<x-app-layout>
    <x-slot name="title">Pengaturan Aplikasi & Organisasi</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-wider">
                        Sistem & Organisasi
                    </span>
                    <span class="text-xs text-slate-400">&bull; {{ $organization->name }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">Pengaturan Aplikasi</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Atur identitas instansi/organisasi, logo, warna brand, preferensi presensi, dan profil akun Anda.
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 rounded-xl transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Side: Organization & System Settings (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Organization Profile & Branding Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Profil & Identitas Organisasi</h2>
                            <p class="text-xs text-slate-400">Informasi ini akan ditampilkan pada header formulir publik, tanda terima, dan laporan ekspor.</p>
                        </div>
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.organization') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- Organization Name & Slug -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nama Organisasi / Instansi <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $organization->name) }}" required
                                    placeholder="Contoh: PT Inovasi Digital Nusantara"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-semibold text-slate-900">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Slug / Identitas URL <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="slug" value="{{ old('slug', $organization->slug) }}" required
                                    placeholder="inovasi-digital"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-mono text-slate-700">
                            </div>
                        </div>

                        <!-- Brand Color & Logo -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Warna Utama Brand
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="brand_color" value="{{ old('brand_color', $organization->brand_color ?? '#4f46e5') }}"
                                        class="h-10 w-14 rounded-xl border border-slate-300 cursor-pointer p-0.5 bg-white">
                                    <input type="text" readonly value="{{ old('brand_color', $organization->brand_color ?? '#4f46e5') }}"
                                        class="w-28 px-3 py-2 text-xs font-mono rounded-xl border border-slate-200 bg-slate-50 text-slate-700">
                                </div>
                                <p class="text-2xs text-slate-400 mt-1">Digunakan untuk tema visual header, tombol, dan banner formulir.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Logo Organisasi
                                </label>
                                <div class="flex items-center space-x-3">
                                    @if($organization->logo_path)
                                        <div class="relative group h-12 w-12 rounded-xl border border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center p-1">
                                            <img src="{{ asset('storage/' . $organization->logo_path) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <input type="file" name="logo" accept="image/*"
                                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl p-1">
                                    </div>
                                    @if($organization->logo_path)
                                        <button type="button"
                                                onclick="window.confirmDialog({
                                                    title: 'Hapus Logo Organisasi?',
                                                    message: 'Logo yang telah diunggah akan dihapus dan kembali ke inisial standar.',
                                                    type: 'delete',
                                                    confirmText: 'Ya, Hapus Logo',
                                                    formId: 'remove-logo-form'
                                                })"
                                                class="px-2.5 py-1.5 text-2xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl border border-rose-200 transition">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                                <p class="text-2xs text-slate-400 mt-1">Format: PNG, JPG, SVG, WEBP (Maks 2MB).</p>
                            </div>
                        </div>

                        <!-- Contact & Address -->
                        <div class="pt-2 border-t border-slate-100 space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Kontak & Domisili Resmi</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Email Resmi</label>
                                    <input type="email" name="email" value="{{ old('email', $organization->settings_json['email'] ?? '') }}"
                                        placeholder="kontak@organisasi.go.id"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">No. Telepon / WA</label>
                                    <input type="text" name="phone" value="{{ old('phone', $organization->settings_json['phone'] ?? '') }}"
                                        placeholder="021-1234567 / 0812..."
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Website Resmi</label>
                                    <input type="url" name="website" value="{{ old('website', $organization->settings_json['website'] ?? '') }}"
                                        placeholder="https://instansi.id"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Alamat Kantor / Sekretariat</label>
                                <input type="text" name="address" value="{{ old('address', $organization->settings_json['address'] ?? '') }}"
                                    placeholder="Jl. Jenderal Sudirman No. 1, Jakarta"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- System Defaults -->
                        <div class="pt-3 border-t border-slate-100 space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Preferensi Default Presensi</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Zona Waktu Standar</label>
                                    <select name="timezone" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                        <option value="Asia/Jakarta" {{ ($organization->settings_json['timezone'] ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>WIB - Asia/Jakarta (GMT+7)</option>
                                        <option value="Asia/Makassar" {{ ($organization->settings_json['timezone'] ?? '') === 'Asia/Makassar' ? 'selected' : '' }}>WITA - Asia/Makassar (GMT+8)</option>
                                        <option value="Asia/Jayapura" {{ ($organization->settings_json['timezone'] ?? '') === 'Asia/Jayapura' ? 'selected' : '' }}>WIT - Asia/Jayapura (GMT+9)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Awalan Kode Bukti Hadir (Prefix)</label>
                                    <input type="text" name="receipt_prefix" value="{{ old('receipt_prefix', $organization->settings_json['receipt_prefix'] ?? 'HF-') }}"
                                        placeholder="Contoh: HF-"
                                        class="w-full px-3 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Catatan Kaki Bukti Hadir (Footer Receipt & PDF)</label>
                                <textarea name="receipt_footer" rows="2" placeholder="Tuliskan teks penutup bukti presensi, misal: 'Dokumen ini sah dikeluarkan oleh Sistem HadirForm.'"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">{{ old('receipt_footer', $organization->settings_json['receipt_footer'] ?? '') }}</textarea>
                            </div>

                            <div>
                                <label class="flex items-center text-xs text-slate-800 font-semibold cursor-pointer">
                                    <input type="checkbox" name="require_operator_signature" value="1"
                                        {{ !empty($organization->settings_json['require_operator_signature']) ? 'checked' : '' }}
                                        class="rounded text-indigo-600 mr-2.5">
                                    <span>Wajibkan tanda tangan digital petugas saat memverifikasi kehadiran</span>
                                </label>
                            </div>
                        </div>

                        <!-- Action Submit -->
                        <div class="pt-4 border-t border-slate-100 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Pengaturan Organisasi
                            </button>
                        </div>
                    </form>

                    <!-- Hidden form for removing logo -->
                    @if($organization->logo_path)
                        <form id="remove-logo-form" method="POST" action="{{ route('admin.settings.logo.remove') }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>

            <!-- Right Side: User Profile & System Information (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- User Profile & Password Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Profil Akun Anda</h2>
                            <p class="text-2xs text-slate-400">Pengaturan login dan kata sandi akun admin.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-2xs font-bold uppercase bg-indigo-100 text-indigo-700">
                            {{ $user->role }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.profile') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-2xs font-bold uppercase text-slate-600 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <div class="text-2xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ganti Kata Sandi (Opsional)</div>

                            <div class="space-y-2.5">
                                <div>
                                    <label class="block text-2xs font-medium text-slate-500 mb-1">Password Saat Ini</label>
                                    <input type="password" name="current_password" placeholder="Masukkan jika ingin mengganti"
                                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-2xs font-medium text-slate-500 mb-1">Password Baru</label>
                                    <input type="password" name="password" placeholder="Minimal 8 karakter"
                                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-2xs font-medium text-slate-500 mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100">
                            <button type="submit" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                Perbarui Profil Akun
                            </button>
                        </div>
                    </form>
                </div>

                <!-- System Information Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-3.5 text-xs text-slate-600">
                    <h3 class="font-bold text-slate-900 text-sm pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Informasi Sistem</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </h3>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Aplikasi:</span>
                        <span class="font-bold text-slate-800">HadirForm v1.2</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Lingkungan:</span>
                        <span class="font-semibold text-indigo-600">Lerd Podman (PHP 8.5)</span>
                    </div>

                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400">Storage Tanda Tangan:</span>
                        <span class="font-semibold text-emerald-600">&check; Private Disk</span>
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-400">Keamanan Duplikasi:</span>
                        <span class="font-semibold text-slate-800">Token Idempotensi Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
