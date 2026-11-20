<x-app-layout>
    <x-slot name="title">Data Kehadiran & Verifikasi</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Data Kehadiran Peserta</h1>
                <p class="text-sm text-slate-500">Kelola dan verifikasi daftar kehadiran yang masuk dari formulir publik.</p>
            </div>

            @if(isset($form))
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.forms.show', $form) }}" class="text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl">
                        &larr; Detail Form: {{ $form->title }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-3 flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode bukti, nama, email..."
                    class="px-3.5 py-2 text-xs rounded-xl border border-slate-300 w-full sm:w-64 focus:ring-2 focus:ring-indigo-500">

                <select name="status" class="px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Baru)</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified (Terverifikasi)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided (Dibatalkan)</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition">
                    Saring Data
                </button>

                @if(request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.responses.index', isset($form) ? ['form' => $form->id] : []) }}" class="text-xs text-rose-600 hover:underline">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Responses Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase text-slate-500">
                            <th class="p-4">Kode Bukti</th>
                            <th class="p-4">Peserta / Identitas</th>
                            <th class="p-4">Formulir / Kegiatan</th>
                            <th class="p-4">Waktu Hadir</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Tanda Tangan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($responses as $resp)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="p-4">
                                    <span class="font-mono font-bold text-indigo-600 text-xs">{{ $resp->receipt_code }}</span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $photoAns = $resp->answers->first(function($a) {
                                            return is_array($a->value_json) && ($a->value_json['type'] ?? '') === 'photo';
                                        });
                                    @endphp
                                    <div class="flex items-center space-x-3">
                                        @if($photoAns)
                                            <a href="{{ route('admin.responses.photo', [$resp, $photoAns->field_key]) }}" target="_blank" title="Klik untuk melihat foto selfie penuh">
                                                <img src="{{ route('admin.responses.photo', [$resp, $photoAns->field_key]) }}"
                                                     class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs hover:opacity-90 transition shrink-0" alt="Selfie">
                                            </a>
                                        @endif
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">
                                                {{ $resp->getAnswerValue('full_name') ?? $resp->getAnswerValue('name') ?? 'Peserta' }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                {{ $resp->getAnswerValue('institution') ?? $resp->getAnswerValue('email') ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-xs">
                                    <div class="font-medium text-slate-800">{{ $resp->form->title }}</div>
                                    <div class="text-slate-400 text-2xs">{{ $resp->event->title }}</div>
                                </td>
                                <td class="p-4 text-xs text-slate-500">
                                    {{ $resp->submitted_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full uppercase
                                        {{ $resp->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($resp->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $resp->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($resp->participantSignature)
                                        <a href="{{ route('admin.signatures.show', $resp->participantSignature) }}" target="_blank" class="inline-block p-1 bg-slate-50 hover:bg-slate-100 rounded border border-slate-200">
                                            <img src="{{ route('admin.signatures.show', $resp->participantSignature) }}" class="h-6 max-w-16 object-contain" alt="Ttd">
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('admin.responses.show', $resp) }}" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition">
                                        Periksa &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    Belum ada data respons kehadiran yang cocok dengan saringan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($responses->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $responses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
