<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected Event $event;
    protected Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Org Test',
            'slug' => 'org-test',
        ]);

        $this->admin = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->event = Event::create([
            'organization_id' => $this->org->id,
            'title' => 'Seminar Nasional 2026',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $schema = [
            'title' => 'Form Presensi',
            'settings' => [
                'require_signature' => true,
                'allow_anonymous_access' => true,
                'duplicate_policy' => 'none',
                'show_receipt' => true,
            ],
            'fields' => [
                [
                    'key' => 'full_name',
                    'type' => 'text',
                    'label' => 'Nama Lengkap',
                    'required' => true,
                ],
                [
                    'key' => 'email',
                    'type' => 'email',
                    'label' => 'Email',
                    'required' => true,
                ],
                [
                    'key' => 'participant_signature',
                    'type' => 'signature',
                    'label' => 'Tanda Tangan',
                    'required' => true,
                ],
                [
                    'key' => 'privacy_consent',
                    'type' => 'consent',
                    'label' => 'Saya menyetujui data kehadiran',
                    'required' => true,
                ],
            ],
        ];

        $this->form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Presensi Seminar',
            'public_slug' => 'presensi-seminar',
            'status' => 'published',
        ]);

        $version = FormVersion::create([
            'form_id' => $this->form->id,
            'version_number' => 1,
            'schema_json' => $schema,
            'published_at' => now(),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', json_encode($schema)),
        ]);

        $this->form->update(['current_version_id' => $version->id]);
    }

    protected function generateTestSignatureDataUrl(): string
    {
        $img = imagecreatetruecolor(200, 80);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, 200, 80, $white);
        // Draw a simulated signature line
        for ($i = 20; $i < 180; $i++) {
            imagesetpixel($img, $i, 40 + (int)(sin($i / 10) * 15), $black);
            imagesetpixel($img, $i, 41 + (int)(sin($i / 10) * 15), $black);
            imagesetpixel($img, $i, 42 + (int)(sin($i / 10) * 15), $black);
        }
        ob_start();
        imagepng($img);
        $binary = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($binary);
    }

    public function test_can_view_public_published_form(): void
    {
        $response = $this->get('/f/presensi-seminar');

        $response->assertStatus(200);
        $response->assertSee('Presensi Seminar');
        $response->assertSee('Seminar Nasional 2026');
    }

    public function test_cannot_submit_if_form_is_closed(): void
    {
        $this->form->update(['status' => 'closed']);

        $response = $this->get('/f/presensi-seminar');
        $response->assertStatus(200);
        $response->assertSee('Formulir Sedang Ditutup');
    }

    public function test_participant_can_submit_attendance_successfully(): void
    {
        $sigData = $this->generateTestSignatureDataUrl();

        $response = $this->post('/f/presensi-seminar/submit', [
            'full_name' => 'Ahmad Dahlan',
            'email' => 'ahmad@example.com',
            'participant_signature' => $sigData,
            'privacy_consent' => '1',
            '_idempotency_token' => 'token-test-123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('responses', [
            'form_id' => $this->form->id,
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('response_answers', [
            'field_key' => 'full_name',
        ]);
        $this->assertDatabaseHas('signatures', [
            'signer_type' => 'participant',
        ]);
    }

    public function test_submission_rejects_blank_signature(): void
    {
        // Blank white image without strokes
        $img = imagecreatetruecolor(200, 80);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 200, 80, $white);
        ob_start();
        imagepng($img);
        $blankBinary = ob_get_clean();
        imagedestroy($img);
        $blankSig = 'data:image/png;base64,' . base64_encode($blankBinary);

        $response = $this->post('/f/presensi-seminar/submit', [
            'full_name' => 'Budi',
            'email' => 'budi@example.com',
            'participant_signature' => $blankSig,
            'privacy_consent' => '1',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('responses', [
            'form_id' => $this->form->id,
        ]);
    }

    public function test_idempotency_prevents_duplicate_submission(): void
    {
        $sigData = $this->generateTestSignatureDataUrl();
        $token = 'unique-idempotency-token-xyz';

        // First submit
        $resp1 = $this->post('/f/presensi-seminar/submit', [
            'full_name' => 'Citra Dewi',
            'email' => 'citra@example.com',
            'participant_signature' => $sigData,
            'privacy_consent' => '1',
            '_idempotency_token' => $token,
        ]);
        $resp1->assertRedirect();

        $countBefore = \App\Models\Response::where('form_id', $this->form->id)->count();
        $this->assertEquals(1, $countBefore);

        // Second submit with same token (network retry)
        $resp2 = $this->post('/f/presensi-seminar/submit', [
            'full_name' => 'Citra Dewi',
            'email' => 'citra@example.com',
            'participant_signature' => $sigData,
            'privacy_consent' => '1',
            '_idempotency_token' => $token,
        ]);
        $resp2->assertRedirect();

        $countAfter = \App\Models\Response::where('form_id', $this->form->id)->count();
        $this->assertEquals(1, $countAfter); // Must NOT duplicate
    }

    public function test_can_submit_attendance_with_camera_selfie_photo(): void
    {
        // Update schema with camera field
        $schema = $this->form->currentVersion->schema_json;
        $schema['fields'][] = [
            'key' => 'selfie_photo',
            'type' => 'camera',
            'label' => 'Foto Selfie di Tempat',
            'required' => true,
        ];

        $v2 = FormVersion::create([
            'form_id' => $this->form->id,
            'version_number' => 2,
            'schema_json' => $schema,
            'published_at' => now(),
            'schema_hash' => hash('sha256', json_encode($schema)),
        ]);
        $this->form->update(['current_version_id' => $v2->id]);

        // Generate simulated selfie image
        $img = imagecreatetruecolor(320, 240);
        $bg = imagecolorallocate($img, 100, 150, 200);
        imagefilledrectangle($img, 0, 0, 320, 240, $bg);
        ob_start();
        imagejpeg($img, null, 80);
        $photoDataUrl = 'data:image/jpeg;base64,' . base64_encode(ob_get_clean());
        imagedestroy($img);

        $sigData = $this->generateTestSignatureDataUrl();

        $response = $this->post('/f/presensi-seminar/submit', [
            'full_name' => 'Dimas Anggara',
            'email' => 'dimas@example.com',
            'participant_signature' => $sigData,
            'selfie_photo' => $photoDataUrl,
            'privacy_consent' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $respModel = \App\Models\Response::where('form_id', $this->form->id)->latest('id')->first();

        $this->assertNotNull($respModel);

        $photoAns = $respModel->answers()->where('field_key', 'selfie_photo')->first();
        $this->assertNotNull($photoAns);
        $this->assertEquals('photo', $photoAns->value_json['type']);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($photoAns->value_json['file_path']));

        // Admin can view the photo
        $adminResp = $this->actingAs($this->admin)->get("/admin/responses/{$respModel->id}/photo/selfie_photo");
        $adminResp->assertStatus(200);
        $this->assertEquals('image/jpeg', $adminResp->headers->get('content-type'));
    }
}
