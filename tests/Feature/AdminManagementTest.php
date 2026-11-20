<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\Response;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected User $operator;
    protected Event $event;
    protected Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Organisasi Maju',
            'slug' => 'org-maju',
        ]);

        $this->admin = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Admin Utama',
            'email' => 'admin@maju.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Operator Lapangan',
            'email' => 'petugas@maju.test',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $this->event = Event::create([
            'organization_id' => $this->org->id,
            'title' => 'Rapat Kerja 2026',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $schema = [
            'title' => 'Presensi Rapat Kerja',
            'settings' => ['require_signature' => true],
            'fields' => [
                ['key' => 'full_name', 'type' => 'text', 'label' => 'Nama', 'required' => true],
            ],
        ];

        $this->form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Presensi Raker',
            'public_slug' => 'presensi-raker',
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

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Presensi');
    }

    public function test_admin_can_create_event(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/events', [
            'title' => 'Pelatihan IT Internal',
            'venue' => 'Ruang Training Lt. 2',
            'timezone' => 'Asia/Jakarta',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Pelatihan IT Internal',
            'organization_id' => $this->org->id,
        ]);
    }

    public function test_admin_can_publish_new_immutable_form_version(): void
    {
        $newSchema = [
            'title' => 'Presensi Rapat Kerja v2',
            'settings' => ['require_signature' => true],
            'fields' => [
                ['key' => 'full_name', 'type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
                ['key' => 'department', 'type' => 'text', 'label' => 'Divisi', 'required' => true],
            ],
        ];

        $response = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/publish", [
            'schema' => json_encode($newSchema),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('form_versions', [
            'form_id' => $this->form->id,
            'version_number' => 2,
        ]);

        $this->form->refresh();
        $this->assertEquals(2, $this->form->currentVersion->version_number);
    }

    public function test_operator_can_verify_response_with_signature(): void
    {
        $attendance = Response::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'form_id' => $this->form->id,
            'form_version_id' => $this->form->current_version_id,
            'receipt_code' => 'HF-TESTVERIFY',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Pre-create participant signature
        $pSig = \App\Models\Signature::create([
            'organization_id' => $this->org->id,
            'response_id' => $attendance->id,
            'signer_type' => 'participant',
            'file_path' => 'signatures/test-p.png',
            'mime_type' => 'image/png',
            'file_size' => 1024,
            'sha256_hash' => hash('sha256', 'participant'),
            'signed_at' => now()->subMinute(),
        ]);

        // Draw a simulated operator signature
        $img = imagecreatetruecolor(150, 60);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, 150, 60, $white);
        for ($i = 10; $i < 140; $i++) {
            imagesetpixel($img, $i, 30 + (int)(cos($i / 8) * 10), $black);
        }
        ob_start();
        imagepng($img);
        $operatorSigData = 'data:image/png;base64,' . base64_encode(ob_get_clean());
        imagedestroy($img);

        $response = $this->actingAs($this->operator)->post("/admin/responses/{$attendance->id}/verify", [
            'decision' => 'verified',
            'reason' => 'Identitas cocok dengan data peserta.',
            'operator_signature' => $operatorSigData,
        ]);

        $response->assertRedirect();

        $attendance->refresh();
        $this->assertEquals('verified', $attendance->status);
        $this->assertEquals($this->operator->id, $attendance->verified_by);

        // Verify BOTH participant AND operator signatures are accessible and distinct
        $this->assertNotNull($attendance->participantSignature, 'Participant signature must not be lost when operator signs');
        $this->assertNotNull($attendance->operatorSignature, 'Operator signature must be accessible');
        $this->assertEquals('participant', $attendance->participantSignature->signer_type);
        $this->assertEquals('operator', $attendance->operatorSignature->signer_type);
        $this->assertEquals($pSig->id, $attendance->participantSignature->id);

        $this->assertDatabaseHas('response_verifications', [
            'response_id' => $attendance->id,
            'operator_id' => $this->operator->id,
            'decision' => 'verified',
        ]);

        $this->assertDatabaseHas('signatures', [
            'response_id' => $attendance->id,
            'signer_type' => 'operator',
            'signed_by_user_id' => $this->operator->id,
        ]);
    }

    public function test_export_xlsx_and_pdf(): void
    {
        $xlsxResp = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/exports", [
            'format' => 'xlsx',
        ]);
        $xlsxResp->assertStatus(200);
        $this->assertTrue(str_contains($xlsxResp->headers->get('content-type'), 'spreadsheetml'));

        $pdfResp = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/exports", [
            'format' => 'pdf',
        ]);
        $pdfResp->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResp->headers->get('content-type'));
    }

    public function test_export_pdf_with_camera_selfie_field(): void
    {
        // Add camera field to form schema
        $version = $this->form->currentVersion;
        $schema = $version->schema_json;
        $schema['fields'][] = [
            'key' => 'selfie_photo',
            'type' => 'camera',
            'label' => 'Foto Selfie Kehadiran',
            'required' => true,
        ];
        $version->update(['schema_json' => $schema]);

        // Attach a simulated selfie photo answer to response
        $attendance = \App\Models\Response::where('form_id', $this->form->id)->first();
        if ($attendance) {
            $img = imagecreatetruecolor(100, 100);
            $bg = imagecolorallocate($img, 120, 180, 240);
            imagefilledrectangle($img, 0, 0, 100, 100, $bg);
            ob_start();
            imagejpeg($img, null, 80);
            $jpegData = ob_get_clean();
            imagedestroy($img);

            $filePath = 'photos/test/' . \Illuminate\Support\Str::uuid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('local')->put($filePath, $jpegData);

            \App\Models\ResponseAnswer::create([
                'response_id' => $attendance->id,
                'field_key' => 'selfie_photo',
                'value_json' => [
                    'type' => 'photo',
                    'file_path' => $filePath,
                    'mime_type' => 'image/jpeg',
                ],
            ]);
        }

        $pdfResp = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/exports", [
            'format' => 'pdf',
        ]);
        $pdfResp->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResp->headers->get('content-type'));

        $xlsxResp = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/exports", [
            'format' => 'xlsx',
        ]);
        $xlsxResp->assertStatus(200);
    }
}
