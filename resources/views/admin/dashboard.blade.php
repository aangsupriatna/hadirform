<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="space-y-6">
        <!-- Page Header & Action Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wider">
                        Sistem Aktif
                    </span>
                    <span class="text-xs text-slate-400">&bull; {{ auth()->user()->organization->name ?? 'Organisasi' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mt-1">
                    Dashboard Presensi Digital
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Monitoring kehadiran peserta, verifikasi tanda tangan, dan manajemen kegiatan secara real-time.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition shadow-2xs">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kegiatan
                </a>
                <a href="{{ route('admin.forms.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs hover:shadow-md transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Buat Formulir Baru
                </a>
            </div>
        </div>

        <!-- 4 Wide KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-indigo-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kegiatan</span>
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-2">{{ $eventsCount }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Seminar, Rapat & Event</span>
                    <a href="{{ route('admin.events.index') }}" class="text-indigo-600 font-semibold hover:underline">Kelola &rarr;</a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-blue-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Formulir Terbit</span>
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-2">{{ $formsCount }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Formulir presensi aktif</span>
                    <a href="{{ route('admin.forms.index') }}" class="text-blue-600 font-semibold hover:underline">Lihat &rarr;</a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kehadiran</span>
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-slate-900 mt-2">{{ $responsesCount }}</div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Dari seluruh kegiatan</span>
                    <a href="{{ route('admin.responses.index') }}" class="text-emerald-600 font-semibold hover:underline">Semua &rarr;</a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-300 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tingkat Verifikasi</span>
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    @php
                        $rate = $responsesCount > 0 ? round(($verifiedCount / $responsesCount) * 100) : 0;
                    @endphp
                    <div class="flex items-baseline space-x-2 mt-2">
                        <span class="text-2xl font-bold text-slate-900">{{ $verifiedCount }}</span>
                        <span class="text-xs font-semibold text-emerald-600">({{ $rate }}%)</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $rate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Grid (Wide 12-Column Responsive Layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Side: Recent Responses & Active Events (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Recent Attendance Table Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Kehadiran Masuk Terbaru</h2>
                            <p class="text-xs text-slate-400">Daftar peserta yang baru saja mengirimkan formulir presensi.</p>
                        </div>
                        <a href="{{ route('admin.responses.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center">
                            Lihat Semua Data ({{ $responsesCount }}) &rarr;
                        </a>
                    </div>

                    <div class="overflow-x-auto -mx-6 px-6">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="text-xs font-bold uppercase text-slate-400 border-b border-slate-100">
                                    <th class="pb-3 pr-4">Peserta</th>
                                    <th class="pb-3 px-4">Kode Bukti</th>
                                    <th class="pb-3 px-4">Formulir & Kegiatan</th>
                                    <th class="pb-3 px-4">Waktu</th>
                                    <th class="pb-3 px-4 text-center">Status</th>
                                    <th class="pb-3 px-4 text-center">Tanda Tangan</th>
                                    <th class="pb-3 pl-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($recentResponses as $resp)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 pr-4">
                                            <div class="font-bold text-slate-900">
                                                {{ $resp->getAnswerValue('full_name') ?? $resp->getAnswerValue('name') ?? 'Peserta' }}
                                            </div>
                                            <div class="text-2xs text-slate-400 truncate max-w-xs">
                                                {{ $resp->getAnswerValue('institution') ?? $resp->getAnswerValue('email') ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-xs text-indigo-600">
                                            {{ $resp->receipt_code }}
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            <div class="font-semibold text-slate-800 truncate max-w-xs">{{ $resp->form->title }}</div>
                                            <div class="text-2xs text-slate-400 truncate max-w-xs">{{ $resp->event->title }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-slate-500 whitespace-nowrap">
                                            {{ $resp->submitted_at?->diffForHumans() }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <span class="px-2.5 py-1 text-2xs font-bold rounded-full uppercase
                                                {{ $resp->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($resp->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700') }}">
                                                {{ $resp->status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            @if($resp->participantSignature)
                                                <span class="inline-flex items-center text-2xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                                    &check; Ada
                                                </span>
                                            @else
                                                <span class="text-slate-300 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 pl-4 text-right whitespace-nowrap">
                                            <a href="{{ route('admin.responses.show', $resp) }}" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl transition">
                                                Verifikasi &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-slate-400 text-xs">
                                            Belum ada data kehadiran yang dikirim peserta.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Active Events & Related Forms Overview Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Kegiatan & Formulir Aktif</h2>
                            <p class="text-xs text-slate-400">Ringkasan kegiatan yang sedang berjalan beserta jumlah form dan respons.</p>
                        </div>
                        <a href="{{ route('admin.events.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Kelola Semua Kegiatan &rarr;
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($recentEvents as $ev)
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-indigo-300 transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-bold uppercase
                                            {{ $ev->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                            {{ $ev->status }}
                                        </span>
                                        <span class="text-2xs text-slate-400 font-medium">
                                            {{ $ev->starts_at?->format('d M Y') ?? 'Jadwal fleksibel' }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-sm">
                                        <a href="{{ route('admin.events.show', $ev) }}" class="hover:text-indigo-600 transition">
                                            {{ $ev->title }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $ev->venue ?? 'Online / Hybrid' }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-200/70 flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-600">
                                        <strong>{{ $ev->forms_count }}</strong> Form &bull; <strong>{{ $ev->responses_count }}</strong> Hadir
                                    </span>
                                    <a href="{{ route('admin.events.show', $ev) }}" class="font-bold text-indigo-600 hover:underline">
                                        Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 py-8 text-center text-slate-400 text-xs">
                                Belum ada kegiatan terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Side: Quick Links, Audit Trail & System Card (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Quick Form Public Link Card -->
                <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 rounded-2xl text-white p-6 shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2 py-0.5 bg-indigo-500/40 text-indigo-200 text-2xs font-bold uppercase rounded-md">
                            Tautan Cepat
                        </span>
                        <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <h3 class="text-base font-bold">Akses Formulir Peserta</h3>
                    <p class="text-xs text-indigo-200 mt-1">
                        Bagikan link presensi atau tampilkan QR Code di lokasi kegiatan untuk diisi peserta.
                    </p>

                    <div class="mt-4 pt-4 border-t border-indigo-600/60 space-y-2">
                        <a href="{{ route('admin.forms.index') }}" class="w-full py-2.5 px-4 bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-xs rounded-xl transition text-center block shadow-xs">
                            Pilih Formulir & Tampilkan QR &rarr;
                        </a>
                    </div>
                </div>

                <!-- Recent Audit Activities Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Log Aktivitas & Audit</h2>
                            <p class="text-2xs text-slate-400">Riwayat peristiwa sistem & keamanan.</p>
                        </div>
                        <a href="{{ route('admin.audit.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Semua &rarr;
                        </a>
                    </div>

                    <div class="space-y-3.5">
                        @forelse($recentAudits as $log)
                            <div class="text-xs border-l-2 border-indigo-500 pl-3 py-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold font-mono text-2xs uppercase text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded">
                                        {{ $log->action }}
                                    </span>
                                    <span class="text-slate-400 text-2xs">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-slate-600 text-xs mt-1">
                                    Oleh: <strong class="text-slate-800">{{ $log->actor?->name ?? 'Sistem / Anonim' }}</strong>
                                </div>
                                <div class="text-slate-400 text-2xs mt-0.5">
                                    IP: {{ $log->ip_address ?? '127.0.0.1' }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-400 text-xs">Belum ada catatan audit.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Security & Storage Summary Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3 text-xs text-slate-600">
                    <h3 class="font-bold text-slate-800 uppercase tracking-wider text-2xs text-slate-400">Informasi Keamanan & Penyimpanan</h3>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span>Penyimpanan Ttd:</span>
                        <span class="font-semibold text-emerald-600">&check; Private Storage</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span>Skema Versi:</span>
                        <span class="font-semibold text-indigo-600">Immutable (SHA256)</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span>Idempotensi Submit:</span>
                        <span class="font-semibold text-slate-800">Aktif & Terproteksi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
