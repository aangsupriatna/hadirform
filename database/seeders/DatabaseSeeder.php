<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Form;
use App\Models\FormVersion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::create([
            'name' => 'PT Inovasi Digital Nusantara',
            'slug' => 'inovasi-digital',
            'brand_color' => '#4f46e5',
            'settings_json' => [
                'timezone' => 'Asia/Jakarta',
                'require_operator_signature' => false,
            ],
        ]);

        $admin = User::create([
            'organization_id' => $org->id,
            'name' => 'Administrator HadirForm',
            'email' => 'admin@hadirform.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $operator = User::create([
            'organization_id' => $org->id,
            'name' => 'Petugas Check-In',
            'email' => 'petugas@hadirform.test',
            'password' => Hash::make('password123'),
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $event = Event::create([
            'organization_id' => $org->id,
            'title' => 'Workshop Transformasi Digital 2026',
            'description' => 'Pelatihan dan seminar akselerasi digital bagi institusi dan perusahaan.',
            'venue' => 'Grand Ballroom Lantai 2, Jakarta',
            'starts_at' => now()->startOfDay()->addHours(8),
            'ends_at' => now()->addDays(2)->endOfDay(),
            'timezone' => 'Asia/Jakarta',
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $schema = [
            'title' => 'Daftar Hadir Workshop Transformasi Digital',
            'description' => 'Silakan isi formulir kehadiran dan bubuhkan tanda tangan Anda pada kolom yang tersedia.',
            'settings' => [
                'require_signature' => true,
                'allow_anonymous_access' => true,
                'duplicate_policy' => 'none',
                'show_receipt' => true,
                'enable_lookup' => true,
                'allow_walk_in' => true,
                'consent_text' => 'Saya menyatakan bahwa data yang saya isi adalah benar dan menyetujui pemrosesan data kehadiran.',
            ],
            'fields' => [
                [
                    'key' => 'full_name',
                    'type' => 'text',
                    'label' => 'Nama Lengkap (beserta gelar jika ada)',
                    'placeholder' => 'Contoh: Dr. Budi Santoso, M.Kom',
                    'help_text' => 'Nama akan dicantumkan pada rekap kehadiran resmi.',
                    'required' => true,
                    'max_length' => 150,
                ],
                [
                    'key' => 'institution',
                    'type' => 'text',
                    'label' => 'Instansi / Perusahaan',
                    'placeholder' => 'Contoh: PT Sukses Mandiri',
                    'required' => true,
                    'max_length' => 200,
                ],
                [
                    'key' => 'position',
                    'type' => 'text',
                    'label' => 'Jabatan / Posisi',
                    'placeholder' => 'Contoh: IT Manager',
                    'required' => false,
                    'max_length' => 150,
                ],
                [
                    'key' => 'email',
                    'type' => 'email',
                    'label' => 'Alamat Email',
                    'placeholder' => 'budi@example.com',
                    'required' => true,
                ],
                [
                    'key' => 'phone',
                    'type' => 'phone',
                    'label' => 'Nomor WhatsApp / HP',
                    'placeholder' => '081234567890',
                    'required' => false,
                ],
                [
                    'key' => 'attendance_mode',
                    'type' => 'radio',
                    'label' => 'Kategori Kehadiran',
                    'options' => ['Hadir Luring (Tatap Muka)', 'Hadir Daring (Online Zoom)'],
                    'required' => true,
                ],
                [
                    'key' => 'participant_signature',
                    'type' => 'signature',
                    'label' => 'Tanda Tangan Peserta',
                    'help_text' => 'Goreskan tanda tangan Anda secara jelas pada kotak canvas.',
                    'required' => true,
                ],
                [
                    'key' => 'privacy_consent',
                    'type' => 'consent',
                    'label' => 'Saya menyetujui pengumpulan dan verifikasi data kehadiran ini untuk dokumentasi kegiatan.',
                    'required' => true,
                ],
            ],
        ];

        $form = Form::create([
            'organization_id' => $org->id,
            'event_id' => $event->id,
            'title' => 'Form Kehadiran Peserta Workshop',
            'description' => 'Formulir presensi resmi peserta Workshop Transformasi Digital.',
            'public_slug' => 'workshop-digital-2026',
            'status' => 'published',
            'opens_at' => now()->subDay(),
            'closes_at' => now()->addDays(7),
            'settings_json' => $schema['settings'],
        ]);

        $schemaHash = hash('sha256', json_encode($schema, JSON_UNESCAPED_UNICODE));

        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => 1,
            'schema_json' => $schema,
            'published_at' => now(),
            'published_by' => $admin->id,
            'schema_hash' => $schemaHash,
        ]);

        $form->update([
            'current_version_id' => $version->id,
        ]);

        // Pre-registered attendees for autocomplete lookup
        $sampleAttendees = [
            [
                'name' => 'Dr. Budi Santoso, M.Kom',
                'institution' => 'Kementerian Komunikasi dan Digital',
                'position' => 'Koordinator Tim Transformasi',
                'email' => 'budi.santoso@komdigi.go.id',
                'phone' => '081234567890',
                'identifier' => '198501012010011001',
            ],
            [
                'name' => 'Siti Rahmawati, S.T.',
                'institution' => 'PT Telkom Indonesia',
                'position' => 'Solutions Architect',
                'email' => 'siti.rahma@telkom.co.id',
                'phone' => '085678901234',
                'identifier' => 'TK-99201',
            ],
            [
                'name' => 'Andi Pratama, M.Sc',
                'institution' => 'Universitas Indonesia',
                'position' => 'Dosen Ilmu Komputer',
                'email' => 'andi.pratama@ui.ac.id',
                'phone' => '087812345678',
                'identifier' => 'UI-88392',
            ],
            [
                'name' => 'Dewi Lestari, S.Kom',
                'institution' => 'Bank Mandiri',
                'position' => 'Digital Banking Lead',
                'email' => 'dewi.lestari@mandiri.co.id',
                'phone' => '081198765432',
                'identifier' => 'BM-44510',
            ],
        ];

        foreach ($sampleAttendees as $att) {
            \App\Models\FormAttendeeLookup::create([
                'form_id' => $form->id,
                ...$att,
            ]);
        }
    }
}
