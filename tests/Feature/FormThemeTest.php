<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\Response;
use App\Models\User;
use App\Services\FormThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormThemeTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_form_theme_service_returns_all_themes_and_defaults(): void
    {
        $themes = FormThemeService::all();
        $this->assertArrayHasKey('indigo', $themes);
        $this->assertArrayHasKey('emerald', $themes);
        $this->assertArrayHasKey('rose', $themes);
        $this->assertArrayHasKey('teal', $themes);

        $defaultTheme = FormThemeService::get('unknown_theme_key');
        $this->assertEquals('indigo', $defaultTheme['id']);

        $cssVars = FormThemeService::getCssVariables('emerald');
        $this->assertStringContainsString('--theme-primary: #059669;', $cssVars);
    }

    public function test_admin_can_create_form_with_custom_color_theme(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/forms', [
            'event_id' => $this->event->id,
            'title' => 'Form Presensi Emerald',
            'public_slug' => 'presensi-emerald',
            'theme_color' => 'emerald',
        ]);

        $response->assertRedirect();

        $form = Form::where('public_slug', 'presensi-emerald')->firstOrFail();
        $this->assertEquals('emerald', $form->theme_color);
        $this->assertEquals('emerald', $form->settings_json['theme_color']);
        $this->assertEquals('emerald', $form->currentVersion->schema_json['settings']['theme_color']);
        $this->assertEquals('#059669', $form->theme['primary']);
    }

    public function test_admin_can_update_color_theme_via_builder_publish(): void
    {
        $form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Awal',
            'public_slug' => 'form-awal',
            'status' => 'published',
            'settings_json' => ['theme_color' => 'indigo'],
        ]);

        $schemaV1 = [
            'title' => 'Form Awal',
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

        // Publish v2 with rose theme
        $schemaV2 = $schemaV1;
        $schemaV2['settings']['theme_color'] = 'rose';

        $publishResponse = $this->actingAs($this->admin)->post("/admin/forms/{$form->id}/publish", [
            'schema' => json_encode($schemaV2),
        ]);

        $publishResponse->assertRedirect();

        $form->refresh();
        $this->assertEquals(2, $form->currentVersion->version_number);
        $this->assertEquals('rose', $form->theme_color);
        $this->assertEquals('rose', $form->currentVersion->schema_json['settings']['theme_color']);
        $this->assertEquals('#e11d48', $form->theme['primary']);
    }

    public function test_public_form_renders_with_selected_color_theme(): void
    {
        $schema = [
            'title' => 'Form Presensi Teal',
            'settings' => [
                'theme_color' => 'teal',
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
            'title' => 'Form Presensi Teal',
            'public_slug' => 'presensi-teal',
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

        $response = $this->get('/f/presensi-teal');

        $response->assertStatus(200);
        $response->assertSee('--theme-primary: #0d9488;', false);
        $response->assertSee('bg-theme');
        $response->assertSee('btn-theme');
    }

    public function test_receipt_page_renders_with_form_theme(): void
    {
        $form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Presensi Amber',
            'public_slug' => 'presensi-amber',
            'status' => 'published',
            'settings_json' => ['theme_color' => 'amber'],
        ]);

        $schema = [
            'title' => 'Form Presensi Amber',
            'settings' => ['theme_color' => 'amber'],
            'fields' => [],
        ];
        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema_json' => $schema,
            'published_at' => now(),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', json_encode($schema)),
        ]);
        $form->update(['current_version_id' => $version->id]);

        $submission = Response::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'form_id' => $form->id,
            'form_version_id' => $version->id,
            'receipt_code' => 'HF-AMBER123',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->get('/receipt/HF-AMBER123');

        $response->assertStatus(200);
        $response->assertSee('--theme-primary: #d97706;', false);
    }

    public function test_admin_form_show_displays_latest_version_prominently_with_older_versions_collapsible(): void
    {
        $form = Form::create([
            'organization_id' => $this->org->id,
            'event_id' => $this->event->id,
            'title' => 'Form Multi Versi',
            'public_slug' => 'form-multi-versi',
            'status' => 'published',
            'settings_json' => [],
        ]);

        $v1 = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema_json' => ['fields' => []],
            'published_at' => now()->subDays(2),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', 'v1'),
        ]);

        $v2 = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 2,
            'schema_json' => ['fields' => []],
            'published_at' => now(),
            'published_by' => $this->admin->id,
            'schema_hash' => hash('sha256', 'v2'),
        ]);

        $form->update(['current_version_id' => $v2->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.forms.show', $form));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Versi Formulir');
        $response->assertSee('Versi 2');
        $response->assertSee('Aktif');
        $response->assertSee('Lihat versi sebelumnya (1)');
    }
}
