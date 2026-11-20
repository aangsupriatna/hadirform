<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\User;
use App\Services\FormHeaderPresetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormHeaderImageTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->org = Organization::create([
            'name' => 'Organisasi Maju Bersama',
            'slug' => 'org-maju-bersama',
        ]);

        $this->admin = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Admin Utama',
            'email' => 'admin@maju.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->event = Event::create([
            'organization_id' => $this->org->id,
            'title' => 'Workshop Transformasi 2026',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_form_header_preset_service_returns_presets(): void
    {
        $presets = FormHeaderPresetService::all();
        $this->assertArrayHasKey('preset_seminar', $presets);
        $this->assertArrayHasKey('preset_conference', $presets);
        $this->assertArrayHasKey('preset_education', $presets);

        $found = FormHeaderPresetService::find('preset_seminar');
        $this->assertNotNull($found);
        $this->assertEquals('/images/headers/preset_seminar.svg', $found['path']);
    }

    public function test_admin_can_upload_custom_header_image(): void
    {
        $file = UploadedFile::fake()->image('custom_header.png', 1200, 400);

        $response = $this->actingAs($this->admin)->postJson('/admin/forms/upload-header-image', [
            'image' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'path', 'url']);

        $path = str_replace('/storage/', '', $response->json('path'));
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_can_create_form_with_preset_header_image(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/forms', [
            'event_id' => $this->event->id,
            'title' => 'Form dengan Header Preset',
            'public_slug' => 'form-header-preset',
            'theme_color' => 'blue',
            'header_image' => '/images/headers/preset_seminar.svg',
        ]);

        $response->assertRedirect();

        $form = Form::where('public_slug', 'form-header-preset')->firstOrFail();
        $this->assertEquals('/images/headers/preset_seminar.svg', $form->header_image);
        $this->assertEquals('/images/headers/preset_seminar.svg', $form->settings_json['header_image']);
        $this->assertEquals('/images/headers/preset_seminar.svg', $form->currentVersion->schema_json['settings']['header_image']);
    }

    public function test_admin_can_update_header_image_in_builder(): void
    {
        $form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Tanpa Header',
            'public_slug' => 'form-update-header',
            'status' => 'published',
            'settings_json' => ['theme_color' => 'indigo'],
        ]);

        $schemaV1 = [
            'title' => 'Form Tanpa Header',
            'settings' => ['theme_color' => 'indigo', 'require_signature' => true],
            'fields' => [
                ['key' => 'full_name', 'type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
            ],
        ];

        $v1 = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema_json' => $schemaV1,
            'published_at' => now(),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', json_encode($schemaV1)),
        ]);
        $form->update(['current_version_id' => $v1->id]);

        // Publish v2 with header image
        $schemaV2 = $schemaV1;
        $schemaV2['settings']['header_image'] = '/images/headers/preset_conference.svg';

        $publishResponse = $this->actingAs($this->admin)->post("/admin/forms/{$form->id}/publish", [
            'schema' => json_encode($schemaV2),
        ]);

        $publishResponse->assertRedirect();

        $form->refresh();
        $this->assertEquals(2, $form->currentVersion->version_number);
        $this->assertEquals('/images/headers/preset_conference.svg', $form->header_image);
    }

    public function test_public_form_renders_header_image(): void
    {
        $schema = [
            'title' => 'Form Kehadiran Publik',
            'settings' => [
                'theme_color' => 'indigo',
                'header_image' => '/images/headers/preset_education.svg',
                'require_signature' => true,
                'allow_anonymous_access' => true,
                'duplicate_policy' => 'none',
                'show_receipt' => true,
            ],
            'fields' => [
                ['key' => 'full_name', 'type' => 'text', 'label' => 'Nama Lengkap', 'required' => true],
            ],
        ];

        $form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Kehadiran Publik',
            'public_slug' => 'form-publik-banner',
            'status' => 'published',
            'settings_json' => $schema['settings'],
        ]);

        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema_json' => $schema,
            'published_at' => now(),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', json_encode($schema)),
        ]);
        $form->update(['current_version_id' => $version->id]);

        $response = $this->get('/f/form-publik-banner');

        $response->assertStatus(200);
        $response->assertSee('/images/headers/preset_education.svg');
    }
}
