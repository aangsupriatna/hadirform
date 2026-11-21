<x-public-layout :theme="$schema['settings']['theme_color'] ?? ($form->theme_color ?? 'indigo')">
    <x-slot name="title">{{ $form->title }}</x-slot>

    <div x-data="publicFormHandler(@js(!empty($schema['settings']['enable_lookup'])), '{{ route('public.form.lookup', $form->public_slug) }}')" class="space-y-6">
        @php
            $headerImg = $schema['settings']['header_image'] ?? $form->header_image;
        @endphp

        @if(!empty($headerImg))
            <div class="rounded-2xl overflow-hidden border border-slate-200/90 shadow-xs bg-slate-900">
                <img src="{{ $headerImg }}" alt="{{ $form->title }}" class="w-full h-auto block">
            </div>
        @endif

        <!-- Event & Form Header Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 overflow-hidden relative">
            <div class="absolute top-0 left-0 right-0 h-2 bg-theme"></div>

            <div class="text-xs font-bold uppercase tracking-wider text-theme mb-1">
                {{ $form->organization->name }}
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-snug">
                {{ $form->title }}
            </h1>
            <div class="text-xs text-slate-500 mt-1 font-medium">
                Kegiatan: <span class="text-slate-800 font-semibold">{{ $form->event->title }}</span>
                @if($form->event->venue)
                    &bull; {{ $form->event->venue }}
                @endif
            </div>

            @if($form->description)
                <p class="text-xs text-slate-600 mt-3 pt-3 border-t border-slate-100 leading-relaxed">
                    {{ $form->description }}
                </p>
            @endif

            <div class="mt-3 text-xs text-rose-600 font-medium">
                * Menandakan kolom wajib diisi
            </div>
        </div>

        <!-- Pre-registered Attendee Lookup Box (Autocomplete) -->
        @if(!empty($schema['settings']['enable_lookup']))
            <div class="bg-gradient-to-br from-[var(--theme-primary-light)] to-slate-50/70 border border-theme rounded-2xl p-5 shadow-xs relative">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="p-1 bg-theme text-white rounded-lg text-2xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-theme-dark">
                        Cari Data Peserta Terdaftar (Auto-fill)
                    </h2>
                </div>
                <p class="text-xs text-slate-600 mb-3">
                    Ketik nama, instansi, NIP, atau nomor HP Anda. Pilih nama Anda agar formulir terisi otomatis:
                </p>

                <!-- Search Input with Live Suggestions -->
                <div class="relative" @click.away="searchResults = []">
                    <div class="relative">
                        <input type="text"
                               x-model="searchQuery"
                               @input.debounce.300ms="searchAttendees()"
                               placeholder="Ketik minimal 2 huruf nama Anda..."
                               class="w-full pl-4 pr-10 py-3 bg-white rounded-xl border border-theme theme-focus text-sm shadow-xs font-medium placeholder-slate-400">

                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span x-show="isSearching" class="text-theme">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                            <span x-show="!isSearching && searchQuery.length > 0" @click="searchQuery = ''; searchResults = []" class="text-slate-400 hover:text-slate-600 cursor-pointer pointer-events-auto">
                                &times;
                            </span>
                        </div>
                    </div>

                    <!-- Autocomplete Dropdown List -->
                    <div x-show="searchResults.length > 0"
                         x-transition
                         class="absolute z-20 left-0 right-0 mt-2 bg-white rounded-xl border border-slate-200 shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100">
                        <template x-for="item in searchResults" :key="item.id">
                            <div @click="selectAttendee(item)"
                                 class="p-3 hover:bg-[var(--theme-primary-light)] cursor-pointer transition flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-bold text-slate-900" x-text="item.name"></div>
                                    <div class="text-xs text-slate-500">
                                        <span x-text="item.institution || 'Umum'"></span>
                                        <span x-show="item.position" x-text="' &bull; ' + item.position"></span>
                                    </div>
                                    <div class="text-xs text-slate-500" x-show="item.identifier" x-text="'NIP/ID: ' + item.identifier"></div>
                                </div>
                                <div class="text-right">
                                    <template x-if="item.is_attended">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Sudah Hadir</span>
                                    </template>
                                    <template x-if="!item.is_attended">
                                        <span class="px-2.5 py-1 btn-theme rounded-lg text-xs font-semibold shadow-2xs">Pilih &rarr;</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="hasSearched && searchResults.length === 0 && searchQuery.length >= 2 && !isSearching"
                         class="absolute z-20 left-0 right-0 mt-2 bg-white rounded-xl border border-slate-200 shadow-lg p-3 text-center text-xs text-slate-500">
                        Nama tidak ditemukan dalam daftar undangan.
                        @if(!empty($schema['settings']['allow_walk_in']))
                            <div class="mt-1 font-semibold text-theme">Anda dapat langsung mengisi formulir di bawah sebagai tamu baru.</div>
                        @endif
                    </div>
                </div>

                <!-- Selected Attendee Banner -->
                <div x-show="selectedAttendee" x-transition class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-800">
                    <div class="flex items-center space-x-2">
                        <span class="text-emerald-600 font-bold">&check;</span>
                        <span>Data terisi otomatis untuk: <strong x-text="selectedAttendee?.name"></strong></span>
                    </div>
                    <button type="button" @click="resetAttendee()" class="text-xs font-semibold text-rose-600 hover:underline">
                        Bukan saya? Reset
                    </button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1">Mohon lengkapi isian formulir:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Submission -->
        <form method="POST" action="{{ route('public.form.submit', $form->public_slug) }}"
              @submit="submitForm($event)" class="space-y-4">
            @csrf
            <input type="hidden" name="_idempotency_token" value="{{ $idempotencyToken }}">

            @foreach($schema['fields'] as $field)
                @php
                    $key = $field['key'];
                    $type = $field['type'];
                    $label = $field['label'];
                    $req = (bool)($field['required'] ?? false);
                    $placeholder = $field['placeholder'] ?? '';
                    $help = $field['help_text'] ?? '';
                    $options = $field['options'] ?? [];
                @endphp

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                    <!-- Heading / Static info -->
                    @if($type === 'heading')
                        <h2 class="text-base font-bold text-slate-900">{{ $label }}</h2>
                        @if($help)<p class="text-xs text-slate-500 mt-0.5">{{ $help }}</p>@endif

                    @elseif($type === 'static_info')
                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                            {{ $help ?: $label }}
                        </div>

                    <!-- Short text / number / date / time / email / phone -->
                    @elseif(in_array($type, ['text', 'number', 'email', 'phone', 'date', 'time']))
                        <label for="f_{{ $key }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            {{ $label }}
                            @if($req)<span class="text-rose-500">*</span>@endif
                        </label>
                        @if($help)<p class="text-xs text-slate-500 mb-1.5">{{ $help }}</p>@endif

                        <input type="{{ $type === 'number' ? 'number' : ($type === 'email' ? 'email' : ($type === 'date' ? 'date' : ($type === 'time' ? 'time' : ($type === 'phone' ? 'tel' : 'text')))) }}"
                               id="f_{{ $key }}"
                               name="{{ $key }}"
                               data-field-key="{{ $key }}"
                               value="{{ old($key) }}"
                               placeholder="{{ $placeholder }}"
                               {{ $req ? 'required' : '' }}
                               class="auto-fill-field w-full px-4 py-2.5 rounded-xl border border-slate-300 theme-focus text-sm">

                    <!-- Paragraph -->
                    @elseif($type === 'paragraph')
                        <label for="f_{{ $key }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            {{ $label }}
                            @if($req)<span class="text-rose-500">*</span>@endif
                        </label>
                        @if($help)<p class="text-xs text-slate-500 mb-1.5">{{ $help }}</p>@endif

                        <textarea id="f_{{ $key }}" name="{{ $key }}" rows="3"
                                  data-field-key="{{ $key }}"
                                  placeholder="{{ $placeholder }}"
                                  {{ $req ? 'required' : '' }}
                                  class="auto-fill-field w-full px-4 py-2.5 rounded-xl border border-slate-300 theme-focus text-sm">{{ old($key) }}</textarea>

                    <!-- Dropdown -->
                    @elseif($type === 'dropdown')
                        <label for="f_{{ $key }}" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            {{ $label }}
                            @if($req)<span class="text-rose-500">*</span>@endif
                        </label>
                        @if($help)<p class="text-xs text-slate-500 mb-1.5">{{ $help }}</p>@endif

                        <select id="f_{{ $key }}" name="{{ $key }}" {{ $req ? 'required' : '' }}
                                data-field-key="{{ $key }}"
                                class="auto-fill-field w-full px-4 py-2.5 rounded-xl border border-slate-300 theme-focus text-sm">
                            <option value="">-- Pilih salah satu --</option>
                            @foreach($options as $opt)
                                <option value="{{ $opt }}" {{ old($key) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>

                    <!-- Radio -->
                    @elseif($type === 'radio')
                        <div class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            {{ $label }}
                            @if($req)<span class="text-rose-500">*</span>@endif
                        </div>
                        @if($help)<p class="text-xs text-slate-500 mb-2">{{ $help }}</p>@endif

                        <div class="space-y-2">
                            @foreach($options as $optIdx => $opt)
                                <label class="flex items-center p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-sm">
                                    <input type="radio" name="{{ $key }}" value="{{ $opt }}"
                                           {{ old($key) == $opt ? 'checked' : '' }}
                                           {{ $req ? 'required' : '' }}
                                           class="theme-accent mr-3">
                                    <span class="text-slate-800 text-xs font-medium">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    <!-- Checkbox -->
                    @elseif($type === 'checkbox')
                        <div class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                            {{ $label }}
                            @if($req)<span class="text-rose-500">*</span>@endif
                        </div>
                        @if($help)<p class="text-xs text-slate-500 mb-2">{{ $help }}</p>@endif

                        <div class="space-y-2">
                            @foreach($options as $opt)
                                <label class="flex items-center p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer text-sm">
                                    <input type="checkbox" name="{{ $key }}[]" value="{{ $opt }}"
                                           class="rounded theme-accent mr-3">
                                    <span class="text-slate-800 text-xs font-medium">{{ $opt }}</span>
                                </label>
                            @endforeach
                        </div>

                    <!-- Signature Pad Canvas -->
                    @elseif($type === 'signature')
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    {{ $label }}
                                    @if($req)<span class="text-rose-500">*</span>@endif
                                </label>
                                <button type="button" @click="clearSignature('{{ $key }}')"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                    Hapus Tanda Tangan
                                </button>
                            </div>
                            @if($help)<p class="text-2xs text-slate-400">{{ $help }}</p>@endif

                            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-1 bg-slate-50 focus-within:border-theme-primary transition">
                                <canvas id="sig_canvas_{{ $key }}"
                                        data-key="{{ $key }}"
                                        class="sig-pad-canvas w-full h-44 touch-none bg-white rounded-xl shadow-2xs"
                                        style="cursor: crosshair;"></canvas>
                            </div>

                            <input type="hidden" name="{{ $key }}" id="sig_input_{{ $key }}">
                            <div class="flex items-center justify-between text-2xs text-slate-400 pt-1">
                                <span>Gunakan jari, stylus, atau mouse</span>
                                <span class="font-semibold text-slate-500">Tanda Tangan Elektronik</span>
                            </div>
                        </div>

                    <!-- Camera / Selfie Capture Field -->
                    @elseif(in_array($type, ['camera', 'selfie']))
                        <div x-data="cameraCaptureHandler('{{ $key }}', {{ $req ? 'true' : 'false' }})" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    {{ $label }}
                                    @if($req)<span class="text-rose-500">*</span>@endif
                                </label>
                                <template x-if="photoData">
                                    <button type="button" @click="retakePhoto()" class="text-xs font-semibold text-rose-600 hover:text-rose-800 flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Foto Ulang
                                    </button>
                                </template>
                            </div>
                            @if($help)<p class="text-xs text-slate-500">{{ $help }}</p>@endif

                            <div class="relative bg-slate-900 rounded-2xl overflow-hidden min-h-60 flex flex-col items-center justify-center p-2 text-white shadow-inner">
                                <!-- Loading State -->
                                <div x-show="isLoading" class="py-12 text-center space-y-2">
                                    <svg class="animate-spin h-8 w-8 mx-auto text-indigo-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <p class="text-xs text-slate-300 font-medium">Menghubungkan kamera...</p>
                                </div>

                                <!-- 1. Initial State: Camera not open and no photo taken yet -->
                                <div x-show="!isLoading && !isStreaming && !photoData" class="py-8 text-center space-y-3">
                                    <div class="w-14 h-14 mx-auto rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div>
                                        <button type="button" @click="startCamera()"
                                                class="px-5 py-2.5 btn-theme font-semibold text-xs rounded-xl shadow-md transition inline-flex items-center">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Buka Kamera Selfie
                                        </button>
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        Atau <label class="text-theme hover:underline cursor-pointer font-medium">unggah file foto<input type="file" accept="image/*" capture="user" @change="handleFileUpload($event)" class="hidden"></label>
                                    </div>
                                </div>

                                <!-- 2. Video Streaming State (Live Viewfinder) -->
                                <div x-show="!isLoading && isStreaming" class="w-full relative flex flex-col items-center">
                                    <video x-ref="video" playsinline autoplay muted class="w-full max-h-80 rounded-xl object-cover bg-black"></video>
                                    <div class="mt-3 flex items-center space-x-3 pb-2">
                                        <button type="button" @click="capturePhoto()"
                                                class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-full shadow-lg transition flex items-center">
                                            <span class="w-2.5 h-2.5 rounded-full bg-white mr-2"></span>
                                            Ambil Foto (Jepret)
                                        </button>
                                        <button type="button" @click="stopCamera()"
                                                class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl transition">
                                            Batal
                                        </button>
                                    </div>
                                </div>

                                <!-- 3. Photo Captured State (Review) -->
                                <div x-show="!isLoading && photoData" class="w-full relative flex flex-col items-center p-2">
                                    <img :src="photoData" class="w-full max-h-80 rounded-xl object-contain bg-black shadow-md" alt="Hasil Foto Selfie">
                                    <div class="mt-2 text-xs text-emerald-400 font-semibold flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Foto selfie berhasil direkam
                                    </div>
                                </div>

                                <canvas x-ref="canvas" class="hidden"></canvas>
                            </div>

                            <template x-if="errorMsg">
                                <p class="text-xs text-rose-500 font-medium" x-text="errorMsg"></p>
                            </template>

                            <input type="hidden" name="{{ $key }}" id="cam_input_{{ $key }}">
                        </div>

                    <!-- Consent Checkbox -->
                    @elseif($type === 'consent')
                        <div class="pt-1">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="{{ $key }}" value="1"
                                       {{ $req ? 'required' : '' }}
                                       class="mt-0.5 rounded theme-accent border-slate-300">
                                <span class="ml-2.5 text-xs text-slate-700 font-medium leading-relaxed">
                                    {{ $label }}
                                    @if($req)<span class="text-rose-500 font-bold">*</span>@endif
                                </span>
                            </label>
                            @if($help)<p class="text-2xs text-slate-400 mt-1 ml-6">{{ $help }}</p>@endif
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Submit Button Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
                <button type="submit"
                        :disabled="isSubmitting"
                        class="w-full py-3.5 px-6 btn-theme disabled:opacity-60 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2">
                    <span x-show="!isSubmitting">Kirim Presensi Kehadiran &rarr;</span>
                    <span x-show="isSubmitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan Kehadiran...
                    </span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function publicFormHandler(lookupEnabled = false, lookupUrl = '') {
            return {
                isSubmitting: false,
                pads: {},
                searchQuery: '',
                searchResults: [],
                isSearching: false,
                hasSearched: false,
                selectedAttendee: null,

                init() {
                    this.$nextTick(() => {
                        const canvases = document.querySelectorAll('.sig-pad-canvas');
                        canvases.forEach(canvas => {
                            const key = canvas.getAttribute('data-key');
                            
                            // Adjust canvas resolution for retina displays
                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            const width = canvas.offsetWidth || 400;
                            const height = canvas.offsetHeight || 176;

                            canvas.width = width * ratio;
                            canvas.height = height * ratio;
                            canvas.getContext("2d").scale(ratio, ratio);

                            if (window.SignaturePad) {
                                this.pads[key] = new window.SignaturePad(canvas, {
                                    backgroundColor: 'rgb(255, 255, 255)',
                                    penColor: 'rgb(15, 23, 42)',
                                    minWidth: 1.2,
                                    maxWidth: 3.0
                                });
                            }
                        });
                    });
                },

                async searchAttendees() {
                    if (!lookupEnabled || this.searchQuery.trim().length < 2) {
                        this.searchResults = [];
                        this.hasSearched = false;
                        return;
                    }

                    this.isSearching = true;
                    try {
                        const res = await fetch(`${lookupUrl}?q=${encodeURIComponent(this.searchQuery.trim())}`);
                        if (res.ok) {
                            this.searchResults = await res.json();
                        } else {
                            this.searchResults = [];
                        }
                    } catch (e) {
                        this.searchResults = [];
                    } finally {
                        this.isSearching = false;
                        this.hasSearched = true;
                    }
                },

                selectAttendee(item) {
                    this.selectedAttendee = item;
                    this.searchResults = [];
                    this.searchQuery = item.name;

                    // Automatically populate fields in the form
                    this.setFieldValue(['full_name', 'name', 'nama', 'nama_lengkap'], item.name);
                    this.setFieldValue(['institution', 'instansi', 'perusahaan', 'lembaga', 'kantor'], item.institution);
                    this.setFieldValue(['position', 'jabatan', 'posisi'], item.position);
                    this.setFieldValue(['email', 'surel'], item.email);
                    this.setFieldValue(['phone', 'no_hp', 'telepon', 'whatsapp', 'hp'], item.phone);
                    this.setFieldValue(['identifier', 'nip', 'nik', 'no_peserta'], item.identifier);
                },

                setFieldValue(possibleKeys, value) {
                    if (!value) return;
                    for (const k of possibleKeys) {
                        const el = document.querySelector(`[data-field-key="${k}"]`);
                        if (el) {
                            el.value = value;
                            el.dispatchEvent(new Event('input'));
                            el.dispatchEvent(new Event('change'));
                            // visual pulse
                            el.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/30');
                            setTimeout(() => {
                                el.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/30');
                            }, 2000);
                            break;
                        }
                    }
                },

                resetAttendee() {
                    this.selectedAttendee = null;
                    this.searchQuery = '';
                    this.searchResults = [];
                    this.hasSearched = false;
                },

                clearSignature(key) {
                    if (this.pads[key]) {
                        this.pads[key].clear();
                        const input = document.getElementById('sig_input_' + key);
                        if (input) input.value = '';
                    }
                },

                submitForm(e) {
                    // Check signature fields
                    for (const key in this.pads) {
                        const pad = this.pads[key];
                        const input = document.getElementById('sig_input_' + key);
                        if (pad && !pad.isEmpty()) {
                            input.value = pad.toDataURL('image/png');
                        } else {
                            input.value = '';
                        }
                    }

                    this.isSubmitting = true;
                }
            };
        }

        function cameraCaptureHandler(key, required = false) {
            return {
                stream: null,
                isStreaming: false,
                isLoading: false,
                photoData: '',
                errorMsg: '',

                async startCamera() {
                    this.errorMsg = '';
                    this.isLoading = true;
                    this.stopCamera();

                    try {
                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            throw new Error('Akses kamera live tidak didukung di browser ini. Silakan gunakan tombol unggah file foto.');
                        }
                        const stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user',
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            },
                            audio: false
                        });

                        this.stream = stream;
                        this.isStreaming = true;
                        this.isLoading = false;

                        await this.$nextTick();
                        const videoEl = this.$refs.video;
                        if (videoEl) {
                            videoEl.muted = true;
                            videoEl.srcObject = stream;

                            videoEl.onloadedmetadata = () => {
                                videoEl.play().catch(e => console.warn('Play error:', e));
                            };

                            if (videoEl.readyState >= 2) {
                                videoEl.play().catch(e => console.warn('Play error:', e));
                            }
                        }
                    } catch (err) {
                        console.error('Camera access error:', err);
                        this.stopCamera();
                        this.errorMsg = 'Tidak dapat membuka kamera: ' + (err.message || 'Izin kamera ditolak atau kamera sedang digunakan aplikasi lain.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => {
                            try {
                                track.stop();
                            } catch (e) {}
                        });
                        this.stream = null;
                    }
                    const videoEl = this.$refs.video;
                    if (videoEl) {
                        try {
                            videoEl.pause();
                        } catch (e) {}
                        videoEl.srcObject = null;
                    }
                    this.isStreaming = false;
                    this.isLoading = false;
                },

                capturePhoto() {
                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    if (!video || !canvas) return;

                    const w = video.videoWidth || 640;
                    const h = video.videoHeight || 480;
                    canvas.width = w;
                    canvas.height = h;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, w, h);

                    this.photoData = canvas.toDataURL('image/jpeg', 0.85);
                    const input = document.getElementById('cam_input_' + key);
                    if (input) input.value = this.photoData;

                    this.stopCamera();
                },

                async retakePhoto() {
                    this.photoData = '';
                    const input = document.getElementById('cam_input_' + key);
                    if (input) input.value = '';

                    this.stopCamera();
                    await this.$nextTick();
                    await this.startCamera();
                },

                handleFileUpload(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    if (file.size > 5 * 1024 * 1024) {
                        this.errorMsg = 'Ukuran foto maksimal 5MB.';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.photoData = event.target.result;
                        const input = document.getElementById('cam_input_' + key);
                        if (input) input.value = this.photoData;
                        this.errorMsg = '';
                        this.stopCamera();
                    };
                    reader.readAsDataURL(file);
                }
            };
        }
    </script>
    @endpush
</x-public-layout>
