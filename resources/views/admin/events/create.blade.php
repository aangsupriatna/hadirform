<x-app-layout>
    <x-slot name="title">Tambah Kegiatan Baru</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Buat Kegiatan Baru</h1>
                <p class="text-sm text-slate-500">Lengkapi informasi dasar kegiatan untuk formulir kehadiran.</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Judul Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Rapat Koordinasi Tahunan 2026"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Singkat</label>
                    <textarea name="description" rows="3" placeholder="Informasi singkat mengenai kegiatan ini..."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tempat / Lokasi</label>
                        <input type="text" name="venue" value="{{ old('venue') }}" placeholder="Contoh: Gedung Serbaguna Lt. 3"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Zona Waktu</label>
                        <select name="timezone" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="Asia/Jakarta">WIB (Asia/Jakarta)</option>
                            <option value="Asia/Makassar">WITA (Asia/Makassar)</option>
                            <option value="Asia/Jayapura">WIT (Asia/Jayapura)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mulai Tanggal / Jam</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Selesai Tanggal / Jam</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Kegiatan</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="published">Published (Aktif)</option>
                        <option value="draft">Draft (Rancangan)</option>
                        <option value="closed">Closed (Ditutup)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.events.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</a>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                        Simpan Kegiatan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
