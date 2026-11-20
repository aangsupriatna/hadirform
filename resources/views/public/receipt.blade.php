<x-public-layout :theme="$response->form?->theme_color ?? 'indigo'">
    <x-slot name="title">Bukti Kehadiran: {{ $response->receipt_code }}</x-slot>

    <div class="space-y-6">
        <!-- Success Banner -->
        <div class="text-center">
            <div class="inline-flex p-3 bg-emerald-100 text-emerald-600 rounded-full mb-3 shadow-xs">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Presensi Berhasil Dikirim!</h1>
            <p class="text-xs text-slate-500 mt-1">Simpan atau tangkap layar bukti kehadiran ini sebagai referensi Anda.</p>
        </div>

        <!-- Receipt Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-lg p-6 sm:p-8 relative overflow-hidden" id="receipt-print-area">
            <div class="absolute top-0 left-0 right-0 h-2 bg-theme"></div>

            <div class="flex items-center justify-between pb-6 border-b border-slate-100">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-theme">{{ $response->organization->name }}</div>
                    <h2 class="text-lg font-bold text-slate-900 mt-0.5">{{ $response->event->title }}</h2>
                    <div class="text-xs text-slate-400 font-medium">{{ $response->form->title }}</div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 font-mono font-bold text-xs bg-slate-100 text-slate-800 rounded-lg border border-slate-200">
                        {{ $response->receipt_code }}
                    </span>
                    <div class="text-2xs text-slate-400 mt-1">Kode Bukti</div>
                </div>
            </div>

            <!-- QR Code & Key Information -->
            <div class="py-6 flex flex-col sm:flex-row items-center justify-between gap-6 border-b border-slate-100">
                <div class="space-y-3 flex-1">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Peserta</div>
                        <div class="text-base font-bold text-slate-900">
                            {{ $response->getAnswerValue('full_name') ?? $response->getAnswerValue('name') ?? 'Peserta' }}
                        </div>
                    </div>

                    @if($inst = $response->getAnswerValue('institution'))
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Instansi / Perusahaan</div>
                            <div class="text-sm font-semibold text-slate-700">{{ $inst }}</div>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Waktu Presensi Tercatat</div>
                        <div class="text-sm font-semibold text-slate-700">
                            {{ $response->submitted_at?->format('d F Y, H:i:s') }} WIB
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Kehadiran</div>
                        <div class="mt-0.5">
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full uppercase
                                {{ $response->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $response->status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-center p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <div class="bg-white p-2 rounded-xl shadow-2xs">
                        {!! $qrCodeSvg !!}
                    </div>
                    <span class="text-xs text-slate-500 mt-2 font-mono">Scan verifikasi</span>
                </div>
            </div>

            <!-- Physical Verification & Signature (if any) -->
            @if(!empty($photoDataUrl) || $response->participantSignature)
                <div class="pt-6 grid grid-cols-1 {{ (!empty($photoDataUrl) && $response->participantSignature) ? 'sm:grid-cols-2 gap-4' : '' }}">
                    @if(!empty($photoDataUrl))
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Foto Bukti Kehadiran</div>
                            <div class="p-2 bg-slate-50 rounded-xl border border-slate-200 flex flex-col items-center justify-center">
                                <img src="{{ $photoDataUrl }}" class="h-28 w-full object-cover rounded-lg shadow-2xs" alt="Foto Selfie Kehadiran">
                                <span class="text-xs text-slate-400 mt-1 font-mono">Terekam saat absensi</span>
                            </div>
                        </div>
                    @endif

                    @if($response->participantSignature)
                        <div class="{{ (!empty($photoDataUrl) && $response->participantSignature) ? '' : 'pt-2' }}">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Tanda Tangan Elektronik Peserta</div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex flex-col items-center justify-center h-28">
                                <img src="{{ $signatureDataUrl ?? route('admin.signatures.show', $response->participantSignature) }}" class="max-h-20 max-w-full object-contain" alt="Tanda Tangan">
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-6 pt-4 text-center text-xs text-slate-400">
                Tanda tangan pada bukti ini adalah tanda tangan elektronik sederhana yang dicatat dalam sistem HadirForm.
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-center space-x-3">
            <button type="button" onclick="window.print()" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
            <a href="{{ route('public.form.show', $response->form->public_slug) }}" class="px-4 py-2.5 text-slate-600 hover:text-slate-900 font-semibold text-xs rounded-xl bg-white border border-slate-200 transition">
                Kembali ke Formulir
            </a>
        </div>
    </div>
</x-public-layout>
