<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org;
    protected User $admin;
    protected User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::create([
            'name' => 'Instansi Awal',
            'slug' => 'instansi-awal',
            'brand_color' => '#4f46e5',
            'settings_json' => [
                'timezone' => 'Asia/Jakarta',
            ],
        ]);

        $this->admin = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Admin Utama',
            'email' => 'admin@instansi.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->operator = User::create([
            'organization_id' => $this->org->id,
            'name' => 'Operator Lapangan',
            'email' => 'operator@instansi.test',
            'password' => Hash::make('password123'),
            'role' => 'operator',
        ]);
    }

    public function test_admin_can_access_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Aplikasi');
        $response->assertSee('Instansi Awal');
    }

    public function test_admin_can_update_organization_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings/organization', [
            'name' => 'Kementerian Komunikasi dan Digital',
            'slug' => 'komdigi-ri',
            'brand_color' => '#1d4ed8',
            'email' => 'humas@komdigi.go.id',
            'phone' => '021-3456789',
            'address' => 'Jl. Medan Merdeka Barat No. 9, Jakarta Pusat',
            'website' => 'https://komdigi.go.id',
            'timezone' => 'Asia/Jakarta',
            'receipt_prefix' => 'KD-',
            'receipt_footer' => 'Diterbitkan resmi oleh Kementerian Komunikasi dan Digital.',
            'require_operator_signature' => 1,
        ]);

        $response->assertRedirect();
        $this->org->refresh();

        $this->assertEquals('Kementerian Komunikasi dan Digital', $this->org->name);
        $this->assertEquals('komdigi-ri', $this->org->slug);
        $this->assertEquals('#1d4ed8', $this->org->brand_color);
        $this->assertEquals('humas@komdigi.go.id', $this->org->settings_json['email']);
        $this->assertEquals('KD-', $this->org->settings_json['receipt_prefix']);
        $this->assertTrue($this->org->settings_json['require_operator_signature']);
    }

    public function test_operator_cannot_update_organization_settings(): void
    {
        $response = $this->actingAs($this->operator)->put('/admin/settings/organization', [
            'name' => 'Nama Baru Diubah Operator',
            'slug' => 'nama-baru',
            'timezone' => 'Asia/Jakarta',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_upload_and_remove_organization_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 120, 120);

        $response = $this->actingAs($this->admin)->put('/admin/settings/organization', [
            'name' => $this->org->name,
            'slug' => $this->org->slug,
            'timezone' => 'Asia/Jakarta',
            'logo' => $file,
        ]);

        $response->assertRedirect();
        $this->org->refresh();

        $this->assertNotNull($this->org->logo_path);
        Storage::disk('public')->assertExists($this->org->logo_path);

        // Remove logo
        $removeResponse = $this->actingAs($this->admin)->delete('/admin/settings/logo');
        $removeResponse->assertRedirect();

        $this->org->refresh();
        $this->assertNull($this->org->logo_path);
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings/profile', [
            'name' => 'Nama Admin Baru',
            'email' => 'admin_baru@instansi.test',
        ]);

        $response->assertRedirect();
        $this->admin->refresh();

        $this->assertEquals('Nama Admin Baru', $this->admin->name);
        $this->assertEquals('admin_baru@instansi.test', $this->admin->email);
    }

    public function test_user_can_change_password(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/settings/profile', [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'current_password' => 'password123',
            'password' => 'newsecretpassword123',
            'password_confirmation' => 'newsecretpassword123',
        ]);

        $response->assertRedirect();
        $this->admin->refresh();

        $this->assertTrue(Hash::check('newsecretpassword123', $this->admin->password));
    }
}
