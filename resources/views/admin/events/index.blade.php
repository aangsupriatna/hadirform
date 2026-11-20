<x-app-layout>
    <x-slot name="title">Daftar Kegiatan</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Kegiatan (Events)</h1>
                <p class="text-sm text-slate-500">Kelola kegiatan, workshop, seminar, dan rapat organisasi Anda.</p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kegiatan
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                            <th class="p-4">Nama Kegiatan</th>
                            <th class="p-4">Lokasi / Tempat</th>
                            <th class="p-4">Waktu Pelaksanaan</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Formulir</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($events as $event)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-4">
                                    <a href="{{ route('admin.events.show', $event) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                        {{ $event->title }}
                                    </a>
                                    @if($event->description)
                                        <p class="text-xs text-slate-400 truncate max-w-sm">{{ Str::limit($event->description, 70) }}</p>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-slate-600">{{ $event->venue ?? 'Online / Hybrid' }}</td>
                                <td class="p-4 text-xs text-slate-600">
                                    @if($event->starts_at)
                                        {{ $event->starts_at->format('d M Y') }}
                                        @if($event->ends_at && $event->ends_at->ne($event->starts_at))
                                            - {{ $event->ends_at->format('d M Y') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full uppercase
                                        {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-700' : ($event->status === 'closed' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $event->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold text-indigo-600">
                                    {{ $event->forms_count }}
                                </td>
                                <td class="p-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('admin.events.show', $event) }}" class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}" class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition">
                                        Edit
                                    </a>
                                    <button type="button"
                                            onclick="window.confirmDialog({
                                                title: 'Hapus Kegiatan?',
                                                message: 'Apakah Anda yakin ingin menghapus kegiatan <strong>{{ addslashes($event->title) }}</strong>? Seluruh formulir kehadiran dan respons terkait akan terhapus secara permanen.',
                                                type: 'delete',
                                                confirmText: 'Ya, Hapus',
                                                formId: 'delete-event-{{ $event->id }}'
                                            })"
                                            class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 rounded-lg transition">
                                        Hapus
                                    </button>
                                    <form id="delete-event-{{ $event->id }}" method="POST" action="{{ route('admin.events.destroy', $event) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    Belum ada kegiatan yang terdaftar. Klik "Tambah Kegiatan" untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
