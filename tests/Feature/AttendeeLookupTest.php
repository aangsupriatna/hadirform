<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormAttendeeLookup;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AttendeeLookupTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected Event $event;
    protected Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create(['name' => 'Org Lookup Test', 'slug' => 'org-lookup']);

        $this->admin = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Admin Lookup',
            'email' => 'admin@lookup.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->event = Event::create([
            'organization_id' => $this->org->id,
            'title' => 'Event Lookup Test',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $schema = [
            'title' => 'Form Presensi Lookup',
            'settings' => [
                'require_signature' => false,
                'enable_lookup' => true,
                'allow_walk_in' => true,
            ],
            'fields' => [
                ['key' => 'full_name', 'type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
                ['key' => 'institution', 'type' => 'text', 'label' => 'Instansi', 'required' => false],
                ['key' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => false],
            ],
        ];

        $this->form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Presensi Lookup',
            'public_slug' => 'presensi-lookup',
            'status' => 'published',
            'settings_json' => $schema['settings'],
        ]);

        $version = FormVersion::create([
            'form_id' => $this->form->id,
            'version_number' => 1,
            'schema_json' => $schema,
            'published_at' => now(),
            'schema_hash' => hash('sha256', json_encode($schema)),
        ]);
        $this->form->update(['current_version_id' => $version->id]);
    }

    public function test_can_download_template(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/forms/{$this->form->id}/attendees/template");

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'spreadsheetml'));
    }

    public function test_can_import_attendees_via_csv(): void
    {
        $csvContent = "Nama Lengkap,Instansi,Jabatan,Email,Nomor HP,NIP\n";
        $csvContent .= "Budi Santoso,Kemenkominfo,Staff,budi@test.com,08123456789,19850101\n";
        $csvContent .= "Siti Rahmawati,PT Telkom,Manager,siti@test.com,08567890123,TK-100\n";

        $file = UploadedFile::fake()->createWithContent('peserta.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/attendees/import", [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('form_attendee_lookups', [
            'form_id' => $this->form->id,
            'name' => 'Budi Santoso',
            'institution' => 'Kemenkominfo',
        ]);
        $this->assertDatabaseHas('form_attendee_lookups', [
            'form_id' => $this->form->id,
            'name' => 'Siti Rahmawati',
            'institution' => 'PT Telkom',
        ]);
    }

    public function test_public_lookup_search_endpoint(): void
    {
        FormAttendeeLookup::create([
            'form_id' => $this->form->id,
            'name' => 'Budi Santoso',
            'institution' => 'Kemenkominfo',
            'position' => 'Staff',
            'email' => 'budi@test.com',
            'phone' => '08123456789',
            'identifier' => '19850101',
        ]);

        $response = $this->get('/f/presensi-lookup/lookup?q=Budi');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Budi Santoso',
            'institution' => 'Kemenkominfo',
        ]);
    }

    public function test_submission_marks_lookup_attendee_as_attended(): void
    {
        $lookup = FormAttendeeLookup::create([
            'form_id' => $this->form->id,
            'name' => 'Budi Santoso',
            'institution' => 'Kemenkominfo',
            'email' => 'budi@test.com',
            'is_attended' => false,
        ]);

        $response = $this->post('/f/presensi-lookup/submit', [
            'full_name' => 'Budi Santoso',
            'institution' => 'Kemenkominfo',
            'email' => 'budi@test.com',
        ]);

        $response->assertRedirect();

        $lookup->refresh();
        $this->assertTrue($lookup->is_attended);
        $this->assertNotNull($lookup->attended_at);
        $this->assertNotNull($lookup->response_id);
    }

    public function test_can_manually_add_attendee_via_dialog(): void
    {
        $response = $this->actingAs($this->admin)->post("/admin/forms/{$this->form->id}/attendees", [
            'name' => 'Ahmad Dahlan',
            'institution' => 'Universitas Indonesia',
            'position' => 'Dosen',
            'email' => 'ahmad@ui.ac.id',
            'phone' => '081299999999',
            'identifier' => 'UI-991',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('form_attendee_lookups', [
            'form_id' => $this->form->id,
            'name' => 'Ahmad Dahlan',
            'identifier' => 'UI-991',
        ]);
    }

    public function test_can_update_attendee(): void
    {
        $attendee = FormAttendeeLookup::create([
            'form_id' => $this->form->id,
            'name' => 'Lama Sekali',
            'institution' => 'Lama',
        ]);

        $response = $this->actingAs($this->admin)->put("/admin/forms/{$this->form->id}/attendees/{$attendee->id}", [
            'name' => 'Baru Diperbarui',
            'institution' => 'Instansi Baru',
        ]);

        $response->assertRedirect();
        $attendee->refresh();
        $this->assertEquals('Baru Diperbarui', $attendee->name);
        $this->assertEquals('Instansi Baru', $attendee->institution);
    }

    public function test_can_delete_single_attendee(): void
    {
        $attendee = FormAttendeeLookup::create([
            'form_id' => $this->form->id,
            'name' => 'Mau Dihapus',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/forms/{$this->form->id}/attendees/{$attendee->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('form_attendee_lookups', [
            'id' => $attendee->id,
        ]);
    }
}
