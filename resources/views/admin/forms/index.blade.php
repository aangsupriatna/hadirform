<x-app-layout>
    <x-slot name="title">Daftar Formulir Kehadiran</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Formulir Kehadiran</h1>
                <p class="text-sm text-slate-500">Buat dan atur formulir presensi digital dengan tanda tangan di layar.</p>
            </div>
            <a href="{{ route('admin.forms.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Formulir Baru
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                            <th class="p-4">Judul Formulir</th>
                            <th class="p-4">Kegiatan</th>
                            <th class="p-4">Versi</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Respons</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($forms as $form)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-2.5 h-2.5 rounded-full inline-block shrink-0 shadow-2xs" style="background-color: {{ $form->theme['primary'] }}" title="Tema: {{ $form->theme['name'] }}"></span>
                                        <a href="{{ route('admin.forms.show', $form) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                            {{ $form->title }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5 ml-4.5">/f/{{ $form->public_slug }}</div>
                                </td>
                                <td class="p-4 text-xs font-medium text-slate-600">
                                    {{ $form->event->title }}
                                </td>
                                <td class="p-4 text-xs font-semibold text-slate-600">
                                    v{{ $form->currentVersion?->version_number ?? 'Draft' }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full uppercase
                                        {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $form->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-bold text-indigo-600">
                                    {{ $form->responses_count }}
                                </td>
                                <td class="p-4 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('admin.forms.show', $form) }}" class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.forms.builder', $form) }}" class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition">
                                        Builder
                                    </a>
                                    <button type="button"
                                            onclick="window.confirmDialog({
                                                title: 'Hapus Formulir?',
                                                message: 'Apakah Anda yakin ingin menghapus formulir <strong>{{ addslashes($form->title) }}</strong>? Seluruh data kehadiran dan riwayat versi akan terhapus secara permanen.',
                                                type: 'delete',
                                                confirmText: 'Ya, Hapus',
                                                formId: 'delete-form-{{ $form->id }}'
                                            })"
                                            class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 rounded-lg transition">
                                        Hapus
                                    </button>
                                    <form id="delete-form-{{ $form->id }}" method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    Belum ada formulir kehadiran. Klik "Buat Formulir Baru" di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($forms->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $forms->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
