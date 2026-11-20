<x-app-layout>
    <x-slot name="title">Detail Kehadiran: {{ $response->receipt_code }}</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Detail Kehadiran</h1>
                    <span class="px-3 py-1 font-mono font-bold text-sm bg-indigo-50 text-indigo-700 rounded-xl border border-indigo-200">
                        {{ $response->receipt_code }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full uppercase
                        {{ $response->status === 'verified' ? 'bg-emerald-100 text-emerald-700' : ($response->status === 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ $response->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Formulir: <strong>{{ $response->form->title }}</strong> &bull;
                    Kegiatan: <strong>{{ $response->event->title }}</strong> &bull;
                    Waktu: {{ $response->submitted_at?->format('d M Y, H:i:s') }}
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('receipt.show', $response->receipt_code) }}" target="_blank" class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Lihat Bukti Publik (Receipt) &rarr;
                </a>
                <a href="{{ route('admin.responses.index', ['form_id' => $response->form_id]) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 rounded-xl transition">
                    &larr; Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Participant Answers & Signature (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Data Answers Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h2 class="text-base font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">
                        Jawaban Isian Formulir
                    </h2>

                    <dl class="space-y-4">
                        @foreach($response->answers as $ans)
                            @continue(is_array($ans->value_json) && (isset($ans->value_json['signature_id']) || ($ans->value_json['type'] ?? '') === 'photo'))
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between py-2 border-b border-slate-50 text-sm">
                                <dt class="text-xs font-bold uppercase text-slate-400 w-1/3">
                                    {{ Str::title(str_replace('_', ' ', $ans->field_key)) }}
                                </dt>
                                <dd class="text-sm font-semibold text-slate-800 w-2/3 mt-0.5 sm:mt-0">
                                    @if(is_bool($ans->value_json))
                                        <span class="text-emerald-600 font-bold">&check; Disetujui</span>
                                    @elseif(is_array($ans->value_json))
                                        {{ implode(', ', $ans->value_json) }}
                                    @else
                                        {{ $ans->value_json ?? '-' }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <!-- Camera / Selfie Photo Cards (if any) -->
                @php
                    $photoAnswers = $response->answers->filter(function($a) {
                        return is_array($a->value_json) && ($a->value_json['type'] ?? '') === 'photo';
                    });
                @endphp

                @foreach($photoAnswers as $photoAns)
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                            <div>
                                <h2 class="text-base font-bold text-slate-900">Foto Selfie / Kamera ({{ Str::title(str_replace('_', ' ', $photoAns->field_key)) }})</h2>
                                <p class="text-xs text-slate-400">Bukti fisik kehadiran yang diambil langsung melalui kamera peserta.</p>
                            </div>
                            <a href="{{ route('admin.responses.photo', [$response, $photoAns->field_key]) }}" target="_blank"
                               class="px-2.5 py-1 text-2xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                Buka Penuh &rarr;
                            </a>
                        </div>

                        <div class="p-4 bg-slate-900 rounded-2xl flex flex-col items-center justify-center">
                            <img src="{{ route('admin.responses.photo', [$response, $photoAns->field_key]) }}"
                                 class="max-h-72 max-w-full rounded-xl object-contain shadow-md" alt="Foto Selfie">
                            <div class="text-2xs text-slate-400 mt-3 font-mono text-center space-x-2">
                                <span>Diambil: {{ !empty($photoAns->value_json['captured_at']) ? \Carbon\Carbon::parse($photoAns->value_json['captured_at'])->format('d M Y H:i:s') : '-' }}</span>
                                <span>&bull;</span>
                                <span>SHA256: {{ substr($photoAns->value_json['sha256'] ?? '', 0, 10) }}...</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Participant Signature Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Tanda Tangan Peserta</h2>
                            <p class="text-xs text-slate-400">Tanda tangan elektronik sederhana yang digambar di layar.</p>
                        </div>
                        @if($response->participantSignature)
                            <span class="text-xs text-slate-500 font-mono">
                                SHA256: {{ substr($response->participantSignature->sha256_hash, 0, 10) }}...
                            </span>
                        @endif
                    </div>

                    @if($response->participantSignature)
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col items-center">
                            <img src="{{ route('admin.signatures.show', $response->participantSignature) }}"
                                class="max-h-40 max-w-full object-contain filter drop-shadow-xs" alt="Tanda Tangan Peserta">
                            <div class="text-2xs text-slate-400 mt-3 font-mono">
                                Disimpan privat &bull; {{ $response->participantSignature->signed_at?->format('d M Y H:i:s') }}
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Peserta tidak menyertakan tanda tangan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Verification Action & History (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Verification Form Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6" x-data="operatorVerifyPad()">
                    <h2 class="text-base font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">
                        Verifikasi Kehadiran Petugas
                    </h2>

                    <form method="POST" action="{{ route('admin.responses.verify', $response) }}" @submit="handleSubmit($event)" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Keputusan Verifikasi <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex flex-col items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-emerald-50/50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 transition">
                                    <input type="radio" name="decision" value="verified" {{ $response->status === 'verified' ? 'checked' : '' }} class="text-emerald-600 mb-1">
                                    <span class="text-xs font-bold text-emerald-800">Setujui</span>
                                </label>

                                <label class="flex flex-col items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-rose-50/50 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 transition">
                                    <input type="radio" name="decision" value="rejected" {{ $response->status === 'rejected' ? 'checked' : '' }} class="text-rose-600 mb-1">
                                    <span class="text-xs font-bold text-rose-800">Tolak</span>
                                </label>

                                <label class="flex flex-col items-center p-3 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50/50 has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100 transition">
                                    <input type="radio" name="decision" value="voided" {{ $response->status === 'voided' ? 'checked' : '' }} class="text-slate-600 mb-1">
                                    <span class="text-xs font-bold text-slate-800">Batalkan</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Catatan / Alasan Penolakan</label>
                            <textarea name="reason" rows="2" placeholder="Tuliskan catatan atau alasan jika menolak..."
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-indigo-500">{{ $response->rejection_reason }}</textarea>
                        </div>

                        <!-- Optional Operator Signature Pad -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Tanda Tangan Petugas (Opsional)</label>
                                <button type="button" @click="clearPad()" class="text-2xs font-semibold text-rose-500 hover:underline">Hapus</button>
                            </div>
                            <div class="border border-slate-300 rounded-xl overflow-hidden bg-slate-50">
                                <canvas x-ref="operatorCanvas" width="400" height="130" class="w-full h-28 touch-none bg-white"></canvas>
                            </div>
                            <input type="hidden" name="operator_signature" x-ref="operatorSigInput">
                            <p class="text-2xs text-slate-400 mt-1">Goreskan tanda tangan petugas sebagai bukti verifikasi sah.</p>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                                Simpan Status Verifikasi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Verification History Timeline -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100">
                        Riwayat Verifikasi
                    </h3>

                    <div class="space-y-4">
                        @forelse($response->verifications as $v)
                            <div class="text-xs border-l-2 {{ $v->decision === 'verified' ? 'border-emerald-500' : 'border-rose-500' }} pl-3 py-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold uppercase {{ $v->decision === 'verified' ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $v->decision }}
                                    </span>
                                    <span class="text-slate-400 text-2xs">{{ $v->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="text-slate-600 mt-0.5">Petugas: <strong>{{ $v->operator->name }}</strong></div>
                                @if($v->reason)
                                    <div class="text-slate-500 text-2xs mt-1 bg-slate-50 p-1.5 rounded">
                                        "{{ $v->reason }}"
                                    </div>
                                @endif
                                @if($v->signature)
                                    <div class="mt-2">
                                        <img src="{{ route('admin.signatures.show', $v->signature) }}" class="h-8 max-w-24 object-contain filter drop-shadow-2xs" alt="Ttd Petugas">
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-400 text-xs">Belum ada riwayat verifikasi sebelumnya.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function operatorVerifyPad() {
            return {
                pad: null,
                init() {
                    const canvas = this.$refs.operatorCanvas;
                    if (window.SignaturePad && canvas) {
                        this.pad = new window.SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(15, 23, 42)'
                        });
                    }
                },
                clearPad() {
                    if (this.pad) {
                        this.pad.clear();
                    }
                },
                handleSubmit(e) {
                    if (this.pad && !this.pad.isEmpty()) {
                        this.$refs.operatorSigInput.value = this.pad.toDataURL('image/png');
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
