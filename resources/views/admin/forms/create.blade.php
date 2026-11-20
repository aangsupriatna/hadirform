<x-app-layout>
    <x-slot name="title">Buat Formulir Baru</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Buat Formulir Baru</h1>
                <p class="text-sm text-slate-500">Pilih kegiatan dan tentukan tautan publik untuk form presensi.</p>
            </div>
            <a href="{{ route('admin.forms.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="createFormHandler('{{ old('header_image', '') }}')">
            <form method="POST" action="{{ route('admin.forms.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Pilih Kegiatan <span class="text-rose-500">*</span></label>
                    <select name="event_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">-- Pilih Kegiatan Terdaftar --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}" {{ (old('event_id', $selectedEventId) == $event->id) ? 'selected' : '' }}>
                                {{ $event->title }} ({{ $event->venue ?? 'Online' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Judul Formulir <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Presensi Hari Pertama (Check-in)"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi / Petunjuk Pengisian</label>
                    <textarea name="description" rows="2" placeholder="Petunjuk pengisian bagi peserta..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Custom Public Slug (Opsional)</label>
                    <div class="flex items-center">
                        <span class="px-3 py-2.5 bg-slate-100 border border-r-0 border-slate-300 rounded-l-xl text-slate-500 text-xs font-mono">
                            {{ url('/f/') }}/
                        </span>
                        <input type="text" name="public_slug" value="{{ old('public_slug') }}" placeholder="presensi-hari-1"
                            class="flex-1 px-4 py-2.5 rounded-r-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-mono">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Kosongkan jika ingin dibuatkan slug acak otomatis.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mulai Buka</label>
                        <input type="datetime-local" name="opens_at" value="{{ old('opens_at') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Batas Ditutup</label>
                        <input type="datetime-local" name="closes_at" value="{{ old('closes_at') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tema Warna Formulir</label>
                    <p class="text-xs text-slate-500 mb-3">Pilih tema warna utama untuk tampilan formulir presensi publik.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        @foreach($themes as $tKey => $t)
                            <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition hover:border-slate-300 has-checked:border-indigo-600 has-checked:ring-2 has-checked:ring-indigo-500/20 has-checked:bg-indigo-50/20">
                                <input type="radio" name="theme_color" value="{{ $tKey }}" {{ old('theme_color', 'indigo') === $tKey ? 'checked' : '' }} class="sr-only">
                                <span class="w-4 h-4 rounded-full mr-2.5 shadow-2xs shrink-0 flex items-center justify-center text-white" style="background-color: {{ $t['primary'] }}"></span>
                                <span class="text-xs font-semibold text-slate-800">{{ $t['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Header Image Selection -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Gambar Banner Header (Opsional)</label>
                        <template x-if="headerImage">
                            <button type="button" @click="headerImage = ''" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                &times; Hapus Gambar
                            </button>
                        </template>
                    </div>
                    <p class="text-xs text-slate-500 mb-3">Pilih salah satu banner header yang telah disediakan atau unggah gambar sendiri.</p>

                    <input type="hidden" name="header_image" :value="headerImage">

                    <!-- Preview Selected Header -->
                    <template x-if="headerImage">
                        <div class="mb-3.5 relative rounded-xl overflow-hidden border border-slate-200 shadow-2xs">
                            <img :src="headerImage" alt="Preview Header" class="w-full h-auto block">
                            <div class="absolute bottom-2 left-2 px-2.5 py-1 bg-black/60 backdrop-blur-xs text-white rounded-lg text-2xs font-semibold flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Banner Header Terpilih</span>
                            </div>
                        </div>
                    </template>

                    <!-- Gallery Presets -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mb-3">
                        @foreach($headerPresets as $pKey => $p)
                            <div @click="headerImage = '{{ $p['path'] }}'"
                                 class="relative rounded-xl border overflow-hidden cursor-pointer transition hover:border-indigo-400 group text-left"
                                 :class="headerImage === '{{ $p['path'] }}' ? 'border-indigo-600 ring-2 ring-indigo-500/30' : 'border-slate-200'">
                                <img src="{{ $p['path'] }}" alt="{{ $p['name'] }}" class="w-full h-16 object-cover">
                                <div class="p-2 bg-white">
                                    <div class="text-2xs font-bold text-slate-800 truncate">{{ $p['name'] }}</div>
                                    <div class="text-3xs text-slate-400 truncate">{{ $p['subtitle'] }}</div>
                                </div>
                                <div x-show="headerImage === '{{ $p['path'] }}'" class="absolute top-1.5 right-1.5 w-5 h-5 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold shadow-xs">
                                    &check;
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Upload Custom -->
                    <div class="flex items-center space-x-3 pt-1">
                        <label class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 cursor-pointer transition">
                            <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Unggah Gambar Sendiri (Max 3MB)</span>
                            <input type="file" accept="image/*" @change="uploadCustomHeader($event)" class="hidden">
                        </label>
                        <span x-show="isUploading" class="text-xs text-indigo-600 font-semibold animate-pulse">Mengunggah...</span>
                        <span x-show="uploadError" class="text-xs text-rose-600 font-semibold" x-text="uploadError"></span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.forms.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                        Lanjut ke Form Builder &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function createFormHandler(initialImage = '') {
            return {
                headerImage: initialImage,
                isUploading: false,
                uploadError: '',

                async uploadCustomHeader(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    if (file.size > 3 * 1024 * 1024) {
                        this.uploadError = 'Ukuran berkas maksimal 3MB.';
                        return;
                    }

                    this.isUploading = true;
                    this.uploadError = '';

                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('{{ route('admin.forms.upload_header') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (res.ok && data.path) {
                            this.headerImage = data.path;
                            window.showToast?.('Gambar header berhasil diunggah!', 'success');
                        } else {
                            this.uploadError = data.message || 'Gagal mengunggah gambar.';
                        }
                    } catch (err) {
                        this.uploadError = 'Terjadi kesalahan jaringan saat mengunggah.';
                    } finally {
                        this.isUploading = false;
                        e.target.value = '';
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
