<x-app-layout>
    <x-slot name="title">{{ $form->title }}</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $form->title }}</h1>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full uppercase
                        {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $form->status }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-full">
                        Versi {{ $form->currentVersion?->version_number ?? 1 }}
                    </span>
                    @php
                        $themeInfo = $form->theme;
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full border border-slate-200/80 bg-white text-slate-700 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full mr-1.5 shadow-2xs" style="background-color: {{ $themeInfo['primary'] }}"></span>
                        {{ $themeInfo['name'] }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    Kegiatan: <a href="{{ route('admin.events.show', $form->event) }}" class="font-semibold text-indigo-600 hover:underline">{{ $form->event->title }}</a>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.forms.attendees.index', $form) }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-sm rounded-xl transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Peserta Terdaftar ({{ $form->attendee_lookups_count ?? 0 }})
                </a>

                <a href="{{ route('admin.forms.builder', $form) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                    Buka Form Builder
                </a>

                @if($form->status === 'published')
                    <form id="close-form" method="POST" action="{{ route('admin.forms.close', $form) }}">
                        @csrf
                        <button type="button"
                                onclick="window.confirmDialog({
                                    title: 'Tutup Formulir Kehadiran?',
                                    message: 'Formulir <strong>{{ addslashes($form->title) }}</strong> akan ditutup sehingga peserta publik tidak dapat mengisi kehadiran lagi.',
                                    type: 'warning',
                                    confirmText: 'Ya, Tutup Formulir',
                                    formId: 'close-form'
                                })"
                                class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-sm rounded-xl transition">
                            Tutup Form
                        </button>
                    </form>
                @else
                    <form id="reopen-form" method="POST" action="{{ route('admin.forms.reopen', $form) }}">
                        @csrf
                        <button type="button"
                                onclick="window.confirmDialog({
                                    title: 'Buka Kembali Formulir?',
                                    message: 'Formulir <strong>{{ addslashes($form->title) }}</strong> akan dibuka kembali dan siap menerima respons peserta.',
                                    type: 'primary',
                                    confirmText: 'Ya, Buka Formulir',
                                    formId: 'reopen-form'
                                })"
                                class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-sm rounded-xl transition">
                            Buka Form
                        </button>
                    </form>
                @endif

                <button type="button"
                        onclick="window.confirmDialog({
                            title: 'Hapus Formulir Kehadiran?',
                            message: 'Apakah Anda yakin ingin menghapus formulir <strong>{{ addslashes($form->title) }}</strong>? Seluruh versi skema, respons kehadiran, dan data peserta akan dihapus secara permanen.',
                            type: 'delete',
                            confirmText: 'Ya, Hapus Formulir',
                            formId: 'delete-form-{{ $form->id }}'
                        })"
                        class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-sm rounded-xl transition">
                    Hapus Form
                </button>
                <form id="delete-form-{{ $form->id }}" method="POST" action="{{ route('admin.forms.destroy', $form) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>

        @if($headerImg = $form->header_image)
            <div class="rounded-2xl overflow-hidden border border-slate-200/90 shadow-2xs">
                <img src="{{ $headerImg }}" alt="{{ $form->title }}" class="w-full h-auto block">
            </div>
        @endif

        <!-- Metrics Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-slate-400 uppercase">Total Respons</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $responsesStats['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-emerald-600 uppercase">Terverifikasi</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $responsesStats['verified'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-rose-600 uppercase">Ditolak</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $responsesStats['rejected'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-blue-600 uppercase">Menunggu</div>
                <div class="text-2xl font-black text-blue-600 mt-1">{{ $responsesStats['submitted'] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- QR Code & Public Link Card (1 col) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-900">Akses Publik & QR Code</h2>
                <p class="text-xs text-slate-500">Tampilkan QR Code ini di layar proyektor atau bagikan tautan kepada peserta.</p>

                <div class="flex justify-center p-4 bg-slate-50 rounded-xl border border-slate-200/70">
                    <div class="bg-white p-3 rounded-lg shadow-xs">
                        {!! $qrCodeSvg !!}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Tautan Publik</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" readonly value="{{ $publicUrl }}" id="publicUrlInput"
                            class="w-full px-3 py-1.5 bg-slate-50 text-xs font-mono rounded-lg border border-slate-200 text-slate-700">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('publicUrlInput').value); window.showToast('Tautan berhasil disalin ke papan klip!', 'success');"
                            class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition">
                            Salin
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ $publicUrl }}" target="_blank" class="w-full inline-flex justify-center items-center py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition">
                        Buka Form Peserta &rarr;
                    </a>
                </div>
            </div>

            <!-- Response Management & Export (2 cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ekspor Laporan Kehadiran</h2>
                        <p class="text-xs text-slate-500">Unduh data rekap kehadiran beserta bukti tanda tangan.</p>
                    </div>

                    <!-- Export Buttons -->
                    <div class="flex items-center space-x-2">
                        <form method="POST" action="{{ route('admin.forms.export', $form) }}" class="inline">
                            @csrf
                            <input type="hidden" name="format" value="xlsx">
                            <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Ekspor Excel (XLSX)
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.forms.export', $form) }}" class="inline">
                            @csrf
                            <input type="hidden" name="format" value="pdf">
                            <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Cetak PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-800">Daftar Kehadiran Masuk</h3>
                        <a href="{{ route('admin.responses.index', ['form_id' => $form->id]) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                            Lihat Semua Data ({{ $form->responses()->count() }}) &rarr;
                        </a>
                    </div>

                    @php
                        $recentList = $form->responses()->with('participantSignature')->latest('submitted_at')->take(5)->get();
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 font-bold text-slate-400 uppercase">
                                    <th class="pb-2">Kode Bukti</th>
                                    <th class="pb-2">Waktu</th>
                                    <th class="pb-2">Status</th>
                                    <th class="pb-2">Tanda Tangan</th>
                                    <th class="pb-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($recentList as $r)
                                    <tr>
                                        <td class="py-2.5 font-mono font-bold text-indigo-600">{{ $r->receipt_code }}</td>
                                        <td class="py-2.5 text-slate-500">{{ $r->submitted_at?->format('d/m H:i') }}</td>
                                        <td class="py-2.5">
                                            <span class="px-2 py-0.5 font-bold rounded-full uppercase text-2xs
                                                {{ $r->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ $r->status }}
                                            </span>
                                        </td>
                                        <td class="py-2.5">
                                            @if($r->participantSignature)
                                                <span class="text-emerald-600 font-bold text-2xs">&check; Tersimpan</span>
                                            @else
                                                <span class="text-slate-400 text-2xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right">
                                            <a href="{{ route('admin.responses.show', $r) }}" class="font-bold text-indigo-600 hover:underline">
                                                Verifikasi &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada respons kehadiran.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Version History -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-bold text-slate-800">Riwayat Versi Formulir</h3>
                        @if($form->versions->count() > 1)
                            <span class="text-2xs text-slate-400 font-medium">Menampilkan versi terbaru (total {{ $form->versions->count() }} versi)</span>
                        @endif
                    </div>

                    @php
                        $latestVersion = $form->currentVersion ?? $form->versions->first();
                        $olderVersions = $form->versions->where('id', '!=', $latestVersion?->id);
                    @endphp

                    @if($latestVersion)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-indigo-50 border border-indigo-200">
                                <div>
                                    <span class="font-bold text-slate-800">Versi {{ $latestVersion->version_number }}</span>
                                    <span class="text-slate-400 ml-2 font-mono text-2xs">{{ substr($latestVersion->schema_hash, 0, 12) }}...</span>
                                    @if($latestVersion->id === $form->current_version_id)
                                        <span class="ml-2 px-1.5 py-0.5 bg-indigo-600 text-white rounded text-2xs font-bold">Aktif</span>
                                    @endif
                                </div>
                                <div class="text-slate-400">
                                    {{ $latestVersion->published_at?->format('d M Y H:i') }}
                                </div>
                            </div>

                            @if($olderVersions->isNotEmpty())
                                <details class="group text-xs">
                                    <summary class="cursor-pointer text-2xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors list-none flex items-center gap-1 pt-1 select-none">
                                        <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                        <span>Lihat versi sebelumnya ({{ $olderVersions->count() }})</span>
                                    </summary>
                                    <div class="mt-2 space-y-1.5 pl-3 border-l-2 border-slate-100">
                                        @foreach($olderVersions as $v)
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 text-2xs">
                                                <div>
                                                    <span class="font-medium text-slate-700">Versi {{ $v->version_number }}</span>
                                                    <span class="text-slate-400 ml-2 font-mono">{{ substr($v->schema_hash, 0, 12) }}...</span>
                                                </div>
                                                <div class="text-slate-400">
                                                    {{ $v->published_at?->format('d M Y H:i') }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-slate-400">Belum ada versi formulir.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
