# BLUEPRINT APLIKASI FORM KEHADIRAN DIGITAL

**Nama proyek:** HadirForm — Digital Attendance & Signature Form Builder  
**Versi dokumen:** 1.0  
**Tanggal:** 19 November 2026  
**Status:** Blueprint implementasi / Software Requirements & Technical Design  
**Bahasa antarmuka:** Bahasa Indonesia (siap internasionalisasi)  

---

## 1. Ringkasan Eksekutif

Bangun aplikasi web responsif berbasis **PHP (Laravel), MySQL, HTML, CSS, dan JavaScript** untuk membuat formulir dinamis seperti Google Forms, dengan spesialisasi **pendaftaran dan kehadiran kegiatan**, **tanda tangan peserta di layar**, **tanda tangan petugas/admin jika diperlukan**, **QR Code**, **rekap data**, serta **ekspor Excel dan PDF**. Formulir dapat diakses peserta tanpa akun melalui tautan publik atau QR Code.

**Prinsip utama:** mudah digunakan di ponsel; data valid; tanda tangan tersimpan dengan aman; form dapat dikustomisasi; rekap siap cetak; setiap perubahan penting dapat diaudit.

### 1.1 Sasaran
1. Admin membuat dan menerbitkan formulir kehadiran tanpa menulis kode.
2. Peserta mengisi form dan menandatangani langsung dari ponsel atau komputer.
3. Admin memonitor respons dan melakukan verifikasi kehadiran.
4. Sistem menghasilkan daftar hadir, laporan, dan bukti tanda tangan.
5. Data dapat diekspor dan dikelola sesuai kebutuhan privasi serta retensi.

### 1.2 Batasan istilah tanda tangan
Tanda tangan yang digambar pada layar adalah **tanda tangan elektronik sederhana** yang dapat menjadi bukti persetujuan/kehadiran sesuai konteks, **bukan otomatis tanda tangan elektronik tersertifikasi**. Jangan mengklaim setara dengan tanda tangan tersertifikasi tanpa integrasi penyelenggara sertifikasi elektronik dan pemeriksaan persyaratan hukum yang relevan.

---

## 2. Aktor dan Hak Akses

| Aktor | Akses |
|---|---|
| Super Admin | Kelola pengguna, organisasi, pengaturan global, keamanan, audit, retensi |
| Admin Organisasi | Kelola kegiatan, form, petugas, respons, ekspor, branding |
| Operator/Petugas | Lihat kegiatan yang ditugaskan, bantu check-in, verifikasi, tanda tangan petugas |
| Client/Peserta | Buka tautan publik, isi form, tanda tangan, kirim, lihat bukti bila diaktifkan |

Gunakan **RBAC** (role-based access control) dan kebijakan berbasis organisasi serta kepemilikan data. Pengguna tidak boleh mengakses data organisasi lain hanya dengan mengganti ID di URL.

---

## 3. Modul dan Fitur

### 3.1 Autentikasi & Pengguna
- Login, logout, reset kata sandi, verifikasi email admin.
- Role dan permission; undangan operator.
- Profil organisasi, logo, warna merek, identitas penyelenggara.
- Opsional: MFA untuk admin, pembatasan sesi, daftar perangkat aktif.

### 3.2 Manajemen Kegiatan
- CRUD kegiatan: judul, deskripsi, penyelenggara, tempat, tanggal/jam, zona waktu, banner/logo, PIC.
- Status: draft, published, closed, archived.
- Satu kegiatan dapat memiliki beberapa form (mis. registrasi, check-in hari pertama, check-in hari kedua, evaluasi).
- Periode buka/tutup formulir dan kuota peserta (opsional).

### 3.3 Form Builder Dinamis
- Builder dengan urutan field drag-and-drop.
- Tipe field: teks pendek, paragraf, email, telepon, angka, tanggal, waktu, dropdown, radio, checkbox, pilihan ganda, judul/separator, informasi statis, persetujuan, unggah file (opsional), dan tanda tangan.
- Field dapat diatur: label, placeholder, bantuan, wajib/tidak, default, pilihan, batas panjang, regex terbatas, visibilitas.
- Field sistem khusus: nama lengkap, instansi/perusahaan, jabatan, email, nomor HP, nomor identitas internal (opsional), waktu check-in, tanda tangan peserta.
- Preview desktop/mobile, duplikasi form, draft, publish, tutup form.
- **Versioning:** saat form diterbitkan, simpan versi skema yang immutable; respons mengacu ke versi yang dipakai agar perubahan field tidak merusak respons lama.
- Fitur lanjutan setelah MVP: logika kondisional, multi-halaman, template siap pakai.

### 3.4 Form Publik (Client)
- URL publik unik, mis. `/f/{public_slug}`.
- QR Code yang mengarah ke URL publik.
- Tampilan mobile-first, indikator wajib, validasi langsung, pesan kesalahan jelas.
- Canvas tanda tangan dengan dukungan mouse, stylus, dan sentuhan; tombol **Hapus** dan **Ulangi**.
- Tampilkan persetujuan penggunaan data pribadi dan pernyataan tanda tangan.
- Submit dengan loading state dan proteksi submit ganda.
- Halaman sukses berisi kode bukti/receipt; unduh bukti jika diaktifkan.
- Tidak wajib login untuk peserta.

### 3.5 Tanda Tangan
- **Peserta:** tanda tangan pada form publik, wajib atau opsional sesuai konfigurasi.
- **Admin/Petugas:** tanda tangan pada alur verifikasi/penutupan daftar hadir; tidak boleh mengganti tanda tangan peserta.
- Gunakan Signature Pad berbasis canvas; hasil dikirim sebagai PNG atau data vektor terstruktur yang divalidasi di server. Pilihan MVP: PNG yang diproses ulang di server.
- Batasi ukuran, dimensi, MIME nyata, dan jumlah titik/stroke; tolak kanvas kosong; sanitasi/decode lalu encode ulang.
- Simpan file di **private storage**; akses lewat endpoint terotorisasi atau tautan bukti sementara.
- Simpan metadata: waktu server UTC, form version, response ID, signer type, consent version, hash SHA-256 file, dan audit event. IP/user-agent hanya bila benar-benar diperlukan, dijelaskan pada pemberitahuan privasi, dan dibatasi retensinya.
- Setelah submit, respons dan tanda tangan tidak boleh diam-diam ditimpa. Koreksi dibuat sebagai revisi tercatat dengan audit trail.
- Tanda tangan admin/petugas hanya membuktikan tindakan petugas sesuai workflow, bukan sertifikasi identitas peserta.

### 3.6 Respons dan Kehadiran
- Daftar respons dengan pencarian, filter tanggal, status, dan kegiatan.
- Status kehadiran: submitted, verified, rejected, voided; alasan penolakan/pembatalan wajib dicatat.
- Check-in timestamp dari server; simpan UTC, tampilkan menurut zona waktu kegiatan.
- Pencegahan duplikasi **dapat dikonfigurasi**: per kode undangan, nomor peserta, atau kombinasi field terpilih. Jangan menganggap nama saja sebagai identitas unik.
- Kode bukti unik dan QR bukti opsional; QR hanya berisi token acak, bukan data pribadi.
- Check-in manual oleh petugas (dengan penanda sumber dan audit log).

### 3.7 Dashboard dan Laporan
- Jumlah respons, terverifikasi, ditolak, per hari, per form/kegiatan.
- Tabel respons yang bisa disaring dan diunduh.
- Ekspor XLSX/CSV dan PDF daftar hadir (kolom dinamis, tanda tangan peserta, ruang tanda tangan petugas, header kegiatan).
- PDF individual bukti kehadiran bila diperlukan.
- Ekspor asinkron untuk data besar, dengan notifikasi ketika siap dan tautan kedaluwarsa.
- Proteksi CSV/Excel formula injection untuk nilai yang diawali `=`, `+`, `-`, `@`.

### 3.8 Branding dan Pengaturan
- Logo organisasi, judul header, tema warna, footer, teks persetujuan, kebijakan privasi.
- Konfigurasi tampilan kolom PDF, orientasi kertas, nomor urut, dan ukuran tanda tangan.
- Template form kehadiran seminar, workshop, rapat, pelatihan, dan kegiatan multi-hari.

---

## 4. Alur Bisnis

### 4.1 Admin membuat form
1. Login dan pilih organisasi.
2. Buat kegiatan.
3. Pilih template atau form kosong.
4. Tambahkan field dan atur validasi; wajibkan tanda tangan jika diperlukan.
5. Preview dan publish versi form.
6. Salin tautan atau cetak QR Code.
7. Pantau respons; verifikasi bila alur membutuhkan verifikasi.
8. Ekspor laporan dan arsipkan kegiatan.

### 4.2 Peserta mengisi kehadiran
1. Scan QR Code atau buka URL.
2. Baca informasi kegiatan dan pemberitahuan privasi.
3. Isi field wajib.
4. Gambar tanda tangan dan centang persetujuan yang diwajibkan.
5. Klik **Kirim Kehadiran**.
6. Server memvalidasi, menyimpan respons beserta tanda tangan secara atomik, dan membuat kode bukti.
7. Peserta melihat halaman sukses/receipt.

### 4.3 Petugas memverifikasi
1. Buka respons sesuai hak akses.
2. Cocokkan informasi yang tersedia dan lakukan verifikasi.
3. Jika perlu, bubuhkan tanda tangan petugas pada record verifikasi.
4. Sistem mencatat aktor, waktu, perubahan status, dan alasan.

---

## 5. Stack Teknologi

| Lapisan | Rekomendasi |
|---|---|
| Backend | PHP versi stabil yang didukung Laravel; Laravel versi stabil terbaru yang kompatibel saat implementasi |
| Database | MySQL 8.0+ / versi yang masih didukung |
| Frontend admin | Blade + Livewire/Alpine.js atau Blade + vanilla JavaScript |
| Styling | Tailwind CSS |
| Form builder | Komponen JavaScript drag-and-drop (mis. SortableJS) |
| Signature | signature_pad (canvas), dengan validasi server |
| QR Code | Paket QR Code Laravel yang terpelihara |
| Excel | Laravel Excel / PhpSpreadsheet |
| PDF | Dompdf untuk laporan sederhana; browser/HTML-to-PDF engine bila layout kompleks |
| Authentication | Laravel starter kit / Fortify sesuai kebutuhan |
| Queue | Database queue untuk MVP; Redis + worker untuk skala lebih besar |
| Storage | Private local disk atau S3-compatible private bucket |
| Server | Nginx + PHP-FPM, HTTPS, scheduler, queue worker |
| Testing | PHPUnit/Pest, Laravel feature tests, browser E2E (opsional) |

**Catatan:** kunci versi dependensi setelah pengecekan kompatibilitas pada awal pengembangan; jangan menebak versi framework/paket.

---

## 6. Arsitektur Sistem

```text
[Browser Admin] -----\
                     -> [Nginx / HTTPS] -> [Laravel Web App]
[Browser Peserta] ---/                         |
                                               +--> [MySQL]
                                               +--> [Private File Storage]
                                               +--> [Queue Worker] --> [PDF/XLSX Exports]
                                               +--> [Mail Service (optional)]
```

Gunakan aplikasi monolitik modular untuk MVP. Pisahkan domain ke `Organizations`, `Events`, `Forms`, `Responses`, `Signatures`, `Reports`, `Audit`, dan `Users`. Jangan memperkenalkan microservices sebelum ada kebutuhan terukur.

---

## 7. Rancangan Database MySQL

Gunakan `BIGINT UNSIGNED` atau UUID/ULID secara konsisten; foreign key, indeks, dan unique constraints. Tabel inti:

### 7.1 `organizations`
`id`, `name`, `slug` UNIQUE, `logo_path`, `brand_color`, `settings_json`, `created_at`, `updated_at`.

### 7.2 `users`
`id`, `organization_id` (nullable untuk super admin), `name`, `email` UNIQUE, `password`, `email_verified_at`, `is_active`, timestamps. Untuk user multi-organisasi gunakan tabel pivot `organization_user` dan jangan mengandalkan satu `organization_id`.

### 7.3 `roles`, `permissions`, dan pivot
Gunakan paket RBAC yang matang atau implementasi setara; role scoped ke organisasi bila multi-tenant.

### 7.4 `events`
`id`, `organization_id`, `title`, `description`, `venue`, `starts_at`, `ends_at`, `timezone`, `status`, `banner_path`, `created_by`, timestamps, `deleted_at`.

### 7.5 `forms`
`id`, `organization_id`, `event_id`, `title`, `description`, `public_slug` UNIQUE, `status`, `opens_at`, `closes_at`, `current_version_id` nullable, `settings_json`, timestamps, `deleted_at`.

### 7.6 `form_versions`
`id`, `form_id`, `version_number`, `schema_json`, `published_at`, `published_by`, `schema_hash`, timestamps; UNIQUE(`form_id`, `version_number`). Skema versi published bersifat immutable.

### 7.7 `form_fields` (opsional, untuk query builder)
`id`, `form_version_id`, `field_key`, `type`, `label`, `help_text`, `placeholder`, `is_required`, `sort_order`, `options_json`, `validation_json`, timestamps; UNIQUE(`form_version_id`, `field_key`). Jika field hanya ada dalam `schema_json`, tidak perlu tabel ini; pilih satu sumber kebenaran.

### 7.8 `responses`
`id`, `organization_id`, `form_id`, `form_version_id`, `event_id`, `receipt_code` UNIQUE, `status`, `source`, `submitted_at`, `verified_at`, `verified_by`, `rejection_reason`, `dedupe_key` nullable, `consent_version`, `consented_at`, timestamps, `deleted_at`.

### 7.9 `response_answers`
`id`, `response_id`, `field_key`, `value_json`, timestamps; UNIQUE(`response_id`, `field_key`). Field khusus tanda tangan disimpan sebagai referensi ke `signatures`, bukan data base64 dalam jawaban.

### 7.10 `signatures`
`id`, `organization_id`, `response_id`, `signer_type` ENUM('participant','operator','admin'), `signed_by_user_id` nullable, `file_path`, `mime_type`, `file_size`, `sha256_hash`, `signed_at`, `consent_version`, `metadata_json`, timestamps. Untuk tanda tangan petugas yang bersifat revisi/verifikasi, tambahkan `verification_id` atau `purpose` dengan hubungan eksplisit.

### 7.11 `response_verifications`
`id`, `response_id`, `operator_id`, `decision`, `reason`, `signature_id` nullable, `created_at`. Setiap keputusan menghasilkan record baru, bukan menimpa riwayat.

### 7.12 `audit_logs`
`id`, `organization_id`, `actor_user_id` nullable, `action`, `subject_type`, `subject_id`, `before_json` nullable, `after_json` nullable, `request_id`, `created_at`. Hindari menyimpan password, gambar tanda tangan, atau jawaban sensitif lengkap dalam audit log.

### 7.13 `exports`
`id`, `organization_id`, `requested_by`, `form_id`, `format`, `filters_json`, `status`, `file_path`, `expires_at`, `created_at`, `completed_at`.

### 7.14 `invitation_tokens` (opsional)
`id`, `form_id`, `token_hash` UNIQUE, `participant_ref` nullable, `max_uses`, `uses_count`, `expires_at`, timestamps. Simpan hash token, bukan token mentah.

### 7.15 Indeks penting
- `responses(form_id, submitted_at)`
- `responses(event_id, status)`
- `responses(organization_id, submitted_at)`
- `response_answers(response_id, field_key)`
- `signatures(response_id, signer_type)`
- `audit_logs(organization_id, created_at)`
- UNIQUE untuk `public_slug`, `receipt_code`, dan dedupe key **hanya bila** kebijakan deduplikasi mewajibkan unik. Untuk form berbeda, scope dedupe ke `form_id`.

### 7.16 Konsistensi transaksi
Bungkus penyimpanan respons, jawaban, metadata tanda tangan, dan penggunaan token dalam transaksi DB. File dapat disimpan sementara dahulu lalu dipromosikan setelah commit melalui mekanisme yang tahan gagal; lakukan cleanup orphan file jika transaksi gagal. Gunakan idempotency key atau token submit sekali pakai untuk mencegah respons ganda saat jaringan terputus.

---

## 8. Contoh Skema Form JSON

```json
{
  "title": "Daftar Hadir Workshop",
  "version": 1,
  "settings": {
    "require_signature": true,
    "allow_anonymous_access": true,
    "duplicate_policy": "per_invitation_token",
    "show_receipt": true
  },
  "fields": [
    {"key": "full_name", "type": "text", "label": "Nama Lengkap", "required": true, "max_length": 150},
    {"key": "institution", "type": "text", "label": "Instansi/Perusahaan", "required": true, "max_length": 200},
    {"key": "position", "type": "text", "label": "Jabatan", "required": false, "max_length": 150},
    {"key": "phone", "type": "phone", "label": "Nomor HP", "required": false},
    {"key": "participant_signature", "type": "signature", "label": "Tanda Tangan Peserta", "required": true},
    {"key": "privacy_consent", "type": "consent", "label": "Saya menyetujui pemrosesan data untuk administrasi kehadiran", "required": true}
  ]
}
```

Validasi skema di backend dengan whitelist jenis field dan aturan yang diizinkan. Jangan mengeksekusi ekspresi atau skrip arbitrer dari JSON builder.

---

## 9. Struktur Folder Laravel (Usulan)

```text
app/
  Http/
    Controllers/Admin/{DashboardController,EventController,FormController,ResponseController,ExportController}.php
    Controllers/Public/{PublicFormController,PublicSubmissionController,ReceiptController}.php
    Requests/{PublishFormRequest,SubmitAttendanceRequest}.php
    Middleware/
  Models/{Organization,Event,Form,FormVersion,Response,ResponseAnswer,Signature,ResponseVerification,AuditLog}.php
  Policies/{EventPolicy,FormPolicy,ResponsePolicy}.php
  Services/{FormSchemaService,FormPublishService,SubmissionService,SignatureService,ReceiptService,ReportService}.php
  Jobs/{GenerateAttendanceExport}.php
  Actions/
  Enums/
resources/
  views/
    layouts/
    admin/{dashboard,events,forms,responses,reports}/
    public/{form,success,receipt}/
  js/{form-builder.js,signature-pad.js,public-form.js}
  css/app.css
routes/{web.php,api.php}
database/{migrations,seeders,factories}
tests/{Feature,Unit}
storage/app/private/{signatures,exports,receipts}
```

---

## 10. Route dan Endpoint

### 10.1 Admin (session auth + CSRF + RBAC)
| Method | Route | Fungsi |
|---|---|---|
| GET | `/admin/dashboard` | Dashboard |
| GET/POST | `/admin/events` | List/buat kegiatan |
| GET/PUT/DELETE | `/admin/events/{event}` | Detail/edit/hapus |
| GET/POST | `/admin/forms` | List/buat form |
| GET/PUT | `/admin/forms/{form}/builder` | Builder dan simpan draft |
| POST | `/admin/forms/{form}/publish` | Publish versi baru |
| POST | `/admin/forms/{form}/close` | Tutup form |
| GET | `/admin/forms/{form}/responses` | Daftar respons |
| GET | `/admin/responses/{response}` | Detail respons |
| POST | `/admin/responses/{response}/verify` | Verifikasi + tanda tangan petugas opsional |
| POST | `/admin/forms/{form}/exports` | Minta ekspor |
| GET | `/admin/exports/{export}/download` | Unduh ekspor terotorisasi |

### 10.2 Publik (rate limited)
| Method | Route | Fungsi |
|---|---|---|
| GET | `/f/{slug}` | Tampilkan form published |
| POST | `/f/{slug}/submit` | Kirim respons dan tanda tangan |
| GET | `/receipt/{opaqueToken}` | Bukti kehadiran jika diaktifkan |

Semua endpoint admin harus memeriksa **authorization policy**, bukan hanya middleware login. Endpoint receipt memakai token acak berentropi tinggi, masa berlaku/akses yang ditentukan, dan tidak membocorkan PII tanpa otorisasi yang sesuai. Jika menggunakan API untuk SPA, tetapkan autentikasi dan CSRF/CORS secara eksplisit.

---

## 11. UI/UX dan Halaman

### Admin
1. Login dan pemulihan akun.
2. Dashboard: ringkasan kegiatan, form aktif, jumlah peserta, aktivitas terbaru.
3. Daftar kegiatan + buat/edit kegiatan.
4. Daftar form + status dan tombol **Buat Form**.
5. Builder tiga area: panel field, kanvas form, panel pengaturan field; preview mobile/desktop.
6. Halaman publikasi: link, QR, periode aktif, preview, pengaturan duplikasi.
7. Respons: tabel, filter, detail, tanda tangan, verifikasi.
8. Laporan: pilih kolom, format, ekspor, riwayat unduhan.
9. Pengguna, role, organisasi, branding, privasi, audit.

### Client/Peserta
1. Halaman form dengan header kegiatan, field dinamis, persetujuan, area tanda tangan.
2. Status submit dan pesan error yang dapat dipahami.
3. Halaman sukses berisi kode bukti dan tombol unduh jika diaktifkan.

### Prinsip desain
- Responsif mulai lebar 320 px; target sentuh minimal sekitar 44 × 44 px.
- Kontras warna memadai; label field jelas; navigasi keyboard; pesan error terasosiasi dengan field.
- Tanda tangan memiliki instruksi, clear/retry, dan fallback proses petugas bagi pengguna yang tidak dapat menggambar tanda tangan.
- Jangan menyimpan jawaban pribadi di localStorage tanpa kebutuhan dan persetujuan yang jelas.

---

## 12. Validasi dan Keamanan

1. **HTTPS wajib** di produksi; secure cookie, HttpOnly, SameSite, session regeneration saat login.
2. CSRF untuk request berbasis sesi; rate limit untuk submit publik, login, receipt, dan ekspor.
3. Validasi seluruh field di server terhadap `form_version` yang diterbitkan, termasuk required, tipe, panjang, pilihan, ukuran unggahan, dan signature.
4. Escape output untuk mencegah XSS; sanitasi konten rich text; gunakan ORM/query parameterized untuk mencegah SQL injection.
5. Authorization scoped ke organisasi dan resource pada setiap akses, termasuk download file.
6. Signature/file upload di storage privat, nama acak, MIME diperiksa berdasarkan isi, ukuran terbatas, tanpa eksekusi file.
7. Backup terenkripsi, uji restore, least-privilege database account, rotasi secrets, pembaruan dependensi.
8. Audit trail untuk publish, perubahan status, verifikasi, ekspor, penghapusan, dan perubahan hak akses.
9. Kebijakan retensi data dan mekanisme hapus/anonymize sesuai aturan organisasi serta hukum yang berlaku; tetapkan dasar pemrosesan data dan pemberitahuan privasi.
10. CAPTCHA/anti-bot opsional jika terdeteksi penyalahgunaan; jangan mengandalkan CAPTCHA sebagai satu-satunya kontrol.
11. Atur kebijakan Content Security Policy (CSP), security headers, serta pembatasan CORS.
12. Jangan mengirim tanda tangan/data pribadi ke layanan analitik pihak ketiga.
13. Gunakan lock/unique constraint dan idempotency key untuk mengatasi submit bersamaan.
14. Hindari menyimpan IP lengkap jika tidak diperlukan; jika dipakai untuk audit/anti-fraud, tentukan tujuan dan retensi.

---

## 13. Laporan dan Template Cetak

### 13.1 Daftar Hadir PDF
Header: logo, nama kegiatan, tanggal, lokasi, penyelenggara. Kolom default: No., Nama, Instansi, Jabatan, Waktu Kehadiran, Tanda Tangan. Kolom dinamis sesuai konfigurasi form. Footer opsional: total peserta, nama petugas, tanda tangan petugas, tanggal cetak.

### 13.2 Ekspor XLSX
Sheet `Kehadiran` dengan data per respons; sheet `Metadata` untuk judul kegiatan, versi form, waktu ekspor, filter, dan penjelasan status. Gambar tanda tangan dapat di-embed atau diwakili tautan privat yang terotorisasi sesuai pilihan ekspor. Perlakukan file ekspor sebagai data sensitif.

### 13.3 PDF Bukti Individual
Nomor bukti, identitas peserta yang diizinkan, kegiatan, waktu server, gambar tanda tangan, status verifikasi, serta QR verifikasi berbasis token jika fitur diaktifkan. Jangan menyebutnya sertifikat tanda tangan tersertifikasi.

---

## 14. Aturan Bisnis dan Edge Cases

- Form draft tidak dapat diisi publik.
- Form closed/expired tidak menerima submit baru; berikan pesan jelas.
- Form yang sedang diedit tidak mengubah versi published; publish membuat versi baru.
- Peserta mengirim berdasarkan versi yang dimuat; bila versi ditutup/dicabut, server mengembalikan pesan untuk refresh.
- Tanda tangan kosong ditolak jika field wajib.
- Jika jaringan gagal setelah submit, idempotency key memungkinkan retry tanpa duplikasi.
- Nama sama tidak otomatis dianggap duplikat.
- Jika form multi-hari, setiap sesi kehadiran memiliki form/check-in tersendiri atau dimensi sesi yang eksplisit.
- Penghapusan kegiatan mengikuti kebijakan retensi dan soft-delete; file privat dibersihkan sesuai jadwal.
- Verifikasi ulang dan pembatalan dicatat sebagai event baru.
- Bila QR dibagikan publik, siapa pun yang punya tautan dapat membuka form kecuali token/OTP/akses terbatas diaktifkan; jangan menyebutnya bukti kehadiran fisik yang pasti.
- GPS, selfie, OTP, dan geofencing adalah fitur opsional; masing-masing memiliki implikasi privasi dan tidak diperlukan untuk MVP.

---

## 15. Tahapan Pengembangan

### Fase 0 — Discovery & keputusan produk
- Konfirmasi single/multi-organization, skala pengguna, hosting, aturan duplikasi, dan kebutuhan hukum tanda tangan.
- Finalisasi wireframe, field template, format laporan, kebijakan privasi/retensi.

### Fase 1 — Fondasi
- Setup Laravel, MySQL, autentikasi, RBAC, organisasi, layout admin, migrasi, seeders, CI.

### Fase 2 — Kegiatan dan Builder
- CRUD kegiatan, builder field, validasi skema, draft/publish/versioning, preview, URL publik, QR Code.

### Fase 3 — Form Publik dan Tanda Tangan
- Render form, validasi server, signature pad, consent, private storage, transaksi, idempotency, receipt.

### Fase 4 — Respons dan Laporan
- Dashboard, filter respons, detail, verifikasi petugas, tanda tangan petugas, ekspor XLSX/PDF, audit log.

### Fase 5 — QA, Security, Deployment
- Automated tests, accessibility, load test, backup/restore, hardening, staging, production, dokumentasi operasional.

### Fase 6 — Pengembangan Lanjutan
- Conditional logic, multi-page, undangan/token unik, OTP, template lanjutan, check-in offline terkontrol, API integrasi, notifikasi WhatsApp/email, multi-tenant billing jika diperlukan.

---

## 16. Acceptance Criteria MVP

- [ ] Admin dapat login dan membuat kegiatan.
- [ ] Admin dapat membuat form dengan minimal 8 tipe field utama, termasuk tanda tangan.
- [ ] Admin dapat menyimpan draft, preview, publish, dan menutup form.
- [ ] Setiap publish membuat versi immutable; respons lama tetap dapat ditampilkan.
- [ ] Form publik berfungsi pada Android, iOS, dan desktop modern.
- [ ] Peserta dapat menggambar, menghapus, mengulang, dan mengirim tanda tangan.
- [ ] Server menolak form yang invalid, tanda tangan kosong yang diwajibkan, serta submit ke form tertutup.
- [ ] Peserta mendapat kode bukti unik setelah submit sukses.
- [ ] Retry dengan idempotency key yang sama tidak menciptakan duplikat.
- [ ] Admin dapat melihat, mencari, memfilter, dan memverifikasi respons.
- [ ] Petugas dapat menambahkan tanda tangan verifikasi tanpa mengubah tanda tangan peserta.
- [ ] Admin dapat mengekspor daftar hadir PDF dan XLSX, termasuk tanda tangan sesuai pengaturan.
- [ ] Pengguna dari organisasi A tidak dapat mengakses data organisasi B.
- [ ] File tanda tangan tidak dapat diakses langsung melalui URL publik storage.
- [ ] Aktivitas sensitif tercatat dalam audit log.
- [ ] Backup dapat dipulihkan pada lingkungan uji.

---

## 17. Rencana Pengujian

**Unit:** validasi schema, validasi field, status transitions, hash signature, deduplication, perhitungan statistik.  
**Feature:** autentikasi, izin akses, publish versi, submit publik, file upload, verifikasi, ekspor, receipt.  
**Security:** IDOR lintas organisasi, CSRF, XSS, SQLi, upload berbahaya, brute-force, token enumeration, CSV injection.  
**Concurrency:** submit simultan, retry request, queue export, pergantian status form saat submit.  
**Usability:** tanda tangan sentuh pada iPhone/Android, mode portrait, koneksi lambat, keyboard-only, pembaca layar.  
**Operational:** backup restore, monitoring worker, expired export cleanup, rotasi key/secrets.

---

## 18. Deployment dan Operasional

### Lingkungan
- `local`, `staging`, `production` dengan konfigurasi terpisah.
- Database production tidak terbuka ke internet publik.
- `APP_DEBUG=false`, secret environment aman, HTTPS aktif.
- Queue worker dan scheduler dijalankan melalui process manager.
- Storage signature/export private; backup dan lifecycle cleanup terjadwal.

### Observabilitas
- Log aplikasi terstruktur dengan request ID, error tracking, metrik latensi submit, error rate, queue depth, storage usage.
- Jangan log isi formulir atau file tanda tangan.

### Perawatan
- Prosedur upgrade Laravel/paket, migrasi rollback yang direncanakan, backup harian sesuai RPO/RTO yang disepakati, uji pemulihan berkala.

---

## 19. Keputusan yang Perlu Dikonfirmasi Sebelum Coding

1. Apakah aplikasi untuk **satu perusahaan** atau **multi-organisasi/multi-client**?
2. Apakah tanda tangan admin diperlukan **per respons**, **per kegiatan**, atau keduanya?
3. Apakah peserta harus unik per kegiatan? Jika ya, berdasarkan **kode undangan/nomor peserta/OTP** apa?
4. Apakah bukti hadir dikirim via email/WhatsApp atau cukup halaman sukses?
5. Apakah PDF harus mengikuti template cetak resmi organisasi?
6. Berapa perkiraan peserta per kegiatan dan total respons per bulan?
7. Apakah peserta boleh mengubah jawaban setelah submit? Jika ya, melalui alur revisi apa?
8. Apakah diperlukan pengumpulan data pribadi khusus? Jika ya, tentukan dasar hukum, kontrol akses, dan retensinya.
9. Apakah aplikasi akan di-host pada shared hosting, VPS, atau cloud?
10. Apakah dibutuhkan tanda tangan elektronik tersertifikasi atau tanda tangan gambar biasa sudah memadai?

**Default MVP jika belum diputuskan:** satu organisasi, admin/operator, form publik tanpa login, tanda tangan gambar peserta wajib, tanda tangan operator saat verifikasi opsional, ekspor PDF/XLSX, tanpa OTP/GPS/WhatsApp, dan deployment VPS.

---

## 20. Instruksi Implementasi untuk AI Coding Assistant

> Anda adalah senior Laravel/PHP engineer dan security-focused architect. Bangun aplikasi **HadirForm** berdasarkan `blueprint.md` ini. Gunakan Laravel stabil yang kompatibel dengan lingkungan deployment, MySQL, Blade, Tailwind, dan JavaScript. Mulai dari fondasi, migrasi, model, RBAC, kegiatan, form builder/versioning, form publik dengan signature pad, penyimpanan privat, respons, verifikasi, lalu laporan PDF/XLSX. Terapkan server-side validation, tenant isolation, policy authorization, audit trail, idempotency, dan test otomatis. Jangan menambahkan fitur di luar MVP sebelum alur utama berfungsi. Untuk setiap fase: tampilkan file yang dibuat/diubah, perintah instalasi/migrasi, pengujian, dan kriteria selesai. Jangan gunakan data dummy di produksi. Jangan mengklaim tanda tangan gambar sebagai tanda tangan tersertifikasi. Bila ada keputusan produk yang belum ditentukan, gunakan default MVP di bagian 19 dan dokumentasikan asumsi.

### Urutan pekerjaan yang disarankan
1. `composer create-project` / setup Laravel dan koneksi MySQL.
2. Migrasi + seed role/permission.
3. Auth + organization scoping + policies.
4. CRUD event dan form draft.
5. Form builder, schema validation, immutable publishing.
6. Public form renderer dan signature pad.
7. Transactional submission, private file storage, idempotency, receipt.
8. Dashboard respons, verification workflow, audit.
9. Export PDF/XLSX + queue.
10. Tests, security review, deployment guide.

### Definition of Done
Fitur MVP lulus seluruh acceptance criteria bagian 16; pengujian keamanan utama lulus; tidak ada akses lintas organisasi; tanda tangan privat; laporan sesuai data; dokumentasi setup dan deployment dapat direproduksi.

---

## 21. Deliverable Pengembangan yang Diharapkan

- Repository source code Laravel.
- `.env.example` tanpa secret.
- Migrasi, seeder, model, controller, policy, service, jobs, views, JS/CSS.
- README setup local/staging/production.
- Dokumentasi database dan endpoint.
- Template PDF/XLSX dan sampel form.
- Automated tests dan checklist QA.
- Dokumentasi backup/restore, privasi, dan operasional.

**Akhir blueprint — versi 1.0**
