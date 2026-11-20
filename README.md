# HadirForm — Digital Attendance & Signature Form Builder

Aplikasi presensi dan formulir kehadiran digital berbasis **PHP (Laravel), MySQL, Tailwind CSS, Alpine.js, dan JavaScript**. Dilengkapi dengan pembuat formulir dinamis (Form Builder), tanda tangan di layar (canvas signature pad), QR Code presensi, verifikasi petugas, audit trail, serta ekspor rekap ke format Excel (XLSX) dan PDF.

---

## Fitur Utama

1. **Multi-Tenant / Organisasi**: Isolasi data ketat berbasis organisasi (`tenant isolation`). Pengguna suatu organisasi tidak dapat melihat atau memanipulasi data organisasi lain.
2. **Manajemen Kegiatan (Events)**: Kelola kegiatan, seminar, workshop, pelatihan, atau rapat dengan jadwal, tempat, dan status.
3. **Form Builder Dinamis**:
   - Susun urutan field dengan drag-and-drop (SortableJS).
   - Tipe field: Teks Pendek, Paragraf, Email, Nomor Telepon/WhatsApp, Dropdown, Radio, Tanda Tangan Canvas, dan Pernyataan Persetujuan (Consent).
   - Skema versi **immutable**: Setiap penerbitan (publish) membuat versi skema baru (`version_number`) dengan checksum hash SHA-256 sehingga data lama tidak rusak ketika skema diedit.
4. **Formulir Publik & Mobile-First**:
   - Peserta dapat mengisi form tanpa perlu login melalui URL publik unik (`/f/{slug}`) atau memindai QR Code.
   - Canvas tanda tangan responsif dengan dukungan sentuhan jari, stylus, dan mouse, dilengkapi tombol hapus dan validasi goresan di server.
   - Idempotency token untuk mencegah submit ganda akibat koneksi jaringan lambat/retry.
   - Bukti tanda tangan disimpan di **Private Storage** (`storage/app/private/signatures`) dan hanya dapat diakses melalui endpoint terotorisasi.
5. **Verifikasi Petugas (Operator)**:
   - Petugas dapat memeriksa kehadiran, menyetujui, menolak dengan alasan, atau membatalkan kehadiran.
   - Petugas dapat membubuhkan tanda tangan verifikasi sendiri tanpa menimpa atau mengubah tanda tangan peserta.
   - Setiap keputusan dicatat sebagai riwayat verifikasi baru (`response_verifications`).
6. **Laporan & Ekspor Data**:
   - **Excel (XLSX)**: Rekap respons lengkap dengan proteksi terhadap CSV/Excel Formula Injection.
   - **PDF**: Daftar hadir resmi siap cetak (A4 Landscape) dengan logo organisasi, detail kegiatan, tanda tangan peserta yang di-embed secara aman, dan ruang tanda tangan petugas.
7. **Audit Trail & Keamanan**:
   - Pencatatan aktivitas sensitif (penerbitan form, verifikasi, penutupan form, ekspor data).
   - Sanitasi file tanda tangan (re-encode via GD library untuk membersihkan EXIF/payload berbahaya).

---

## Kredensial Default (Seeder)

Aplikasi telah disiapkan dengan data awal untuk pengujian lokal:

- **URL Aplikasi**: `https://hadirform.test`
- **Akun Administrator**:
  - Email: `admin@hadirform.test`
  - Password: `password123`
  - Role: `admin`
- **Akun Petugas / Operator**:
  - Email: `petugas@hadirform.test`
  - Password: `password123`
  - Role: `operator`
- **Sample Form Publik**:
  - URL: `https://hadirform.test/f/workshop-digital-2026`

---

## Panduan Instalasi & Pengujian

### 1. Prasyarat Lingkungan
- PHP 8.3+ (dengan ekstensi `gd`, `pdo_mysql`, `mbstring`, `fileinfo`)
- MySQL 8.0+
- Composer & Node.js (npm)
- Lingkungan lokal **lerd** (direkomendasikan) atau web server Nginx/Apache standar.

### 2. Langkah Pengaturan (Setup)
```bash
# Salin konfigurasi environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Jalankan migrasi dan seeding database
php artisan migrate --force
php artisan db:seed

# Install dependensi frontend dan build assets
npm install
npm run build

# Buat symbolic link storage publik
php artisan storage:link
```

### 3. Menjalankan Queue Worker & Server
Jika menggunakan **lerd**:
```bash
lerd start
lerd worker start queue
```
Atau manual:
```bash
php artisan queue:work
php artisan serve
```

### 4. Menjalankan Pengujian Otomatis (Automated Tests)
Semua skenario pengujian unit & fitur (public submission, idempotency, blank signature rejection, immutable versioning, tenant isolation, dan ekspor dokumen) dapat dijalankan dengan:
```bash
php artisan test
```

---

## Lisensi & Batasan Istilah Tanda Tangan
Tanda tangan pada aplikasi ini merupakan **tanda tangan elektronik sederhana** berupa goresan gambar di layar kanvas untuk kebutuhan konfirmasi kehadiran administratif, dan **bukan** tanda tangan elektronik tersertifikasi (PSrE).
