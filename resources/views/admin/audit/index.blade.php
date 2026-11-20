<x-app-layout>
    <x-slot name="title">Audit Trail & Log</x-slot>

    <div class="space-y-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Audit Trail & Log Aktivitas</h1>
            <p class="text-sm text-slate-500">Catatan kronologis perubahan data, penerbitan formulir, verifikasi kehadiran, dan ekspor data.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Aksi (Action)</th>
                            <th class="p-4">Pengguna (Aktor)</th>
                            <th class="p-4">Objek / Entitas</th>
                            <th class="p-4">IP Address</th>
                            <th class="p-4">Detail Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 text-xs">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-4 font-mono text-slate-500">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 font-bold rounded-md uppercase font-mono text-2xs bg-slate-100 text-slate-700">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="p-4 font-semibold text-slate-800">
                                    {{ $log->actor?->name ?? 'Sistem / Anonim' }}
                                </td>
                                <td class="p-4 font-mono text-slate-500">
                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                </td>
                                <td class="p-4 font-mono text-slate-400">
                                    {{ $log->ip_address ?? '-' }}
                                </td>
                                <td class="p-4 max-w-xs">
                                    @if($log->after_json)
                                        <pre class="bg-slate-50 p-2 rounded-lg text-2xs overflow-x-auto border border-slate-200">{{ json_encode($log->after_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">
                                    Belum ada catatan aktivitas audit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
