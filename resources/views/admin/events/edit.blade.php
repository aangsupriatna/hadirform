<x-app-layout>
    <x-slot name="title">Edit Kegiatan: {{ $event->title }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Kegiatan</h1>
                <p class="text-sm text-slate-500">Perbarui detail informasi kegiatan.</p>
            </div>
            <a href="{{ route('admin.events.show', $event) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Kembali</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <form method="POST" action="{{ route('admin.events.update', $event) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Judul Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Deskripsi Singkat</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description', $event->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tempat / Lokasi</label>
                        <input type="text" name="venue" value="{{ old('venue', $event->venue) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Zona Waktu</label>
                        <select name="timezone" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="Asia/Jakarta" {{ $event->timezone === 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                            <option value="Asia/Makassar" {{ $event->timezone === 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                            <option value="Asia/Jayapura" {{ $event->timezone === 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mulai Tanggal / Jam</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Selesai Tanggal / Jam</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Status Kegiatan</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="published" {{ $event->status === 'published' ? 'selected' : '' }}>Published (Aktif)</option>
                        <option value="draft" {{ $event->status === 'draft' ? 'selected' : '' }}>Draft (Rancangan)</option>
                        <option value="closed" {{ $event->status === 'closed' ? 'selected' : '' }}>Closed (Ditutup)</option>
                        <option value="archived" {{ $event->status === 'archived' ? 'selected' : '' }}>Archived (Arsip)</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <button type="button"
                        onclick="window.confirmDialog({
                            title: 'Hapus Kegiatan?',
                            message: 'Apakah Anda yakin ingin menghapus kegiatan <strong>{{ addslashes($event->title) }}</strong>? Seluruh formulir kehadiran dan respons terkait akan terhapus secara permanen.',
                            type: 'delete',
                            confirmText: 'Ya, Hapus Kegiatan',
                            formId: 'delete-event-form'
                        })"
                        class="px-4 py-2 text-rose-600 hover:text-rose-800 text-sm font-semibold">
                        Hapus Kegiatan
                    </button>

                    <div class="space-x-3">
                        <a href="{{ route('admin.events.show', $event) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</a>
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-event-form" method="POST" action="{{ route('admin.events.destroy', $event) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-app-layout>
