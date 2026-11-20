<x-app-layout>
    <x-slot name="title">Form Builder: {{ $form->title }}</x-slot>

    <div x-data="formBuilder(@js($currentSchema), @js($themes), @js($headerPresets))" class="space-y-6">
        <!-- Top Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded">Builder</span>
                    <h1 class="text-xl font-bold text-slate-900" x-text="schema.title || 'Formulir Tanpa Judul'"></h1>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">Versi aktif: v{{ $form->currentVersion?->version_number ?? 1 }} &bull; Atur susunan field presensi</p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.forms.show', $form) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 bg-slate-100 rounded-xl transition">
                    &larr; Kembali
                </a>

                <button type="button" @click="saveAndPublish()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Terbitkan Versi Baru (Publish)
                </button>
            </div>
        </div>

        <!-- 3-Column Builder Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Field Palette (3 cols) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Pilihan Jenis Field</h3>
                    <div class="grid grid-cols-1 gap-2">
                        <button type="button" @click="addField('text', 'Teks Singkat')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-indigo-100 text-indigo-600 rounded mr-2.5 font-mono text-2xs">TXT</span>
                            Teks Pendek
                        </button>
                        <button type="button" @click="addField('paragraph', 'Paragraf / Catatan')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-indigo-100 text-indigo-600 rounded mr-2.5 font-mono text-2xs">PAR</span>
                            Paragraf
                        </button>
                        <button type="button" @click="addField('email', 'Alamat Email')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-blue-100 text-blue-600 rounded mr-2.5 font-mono text-2xs">@</span>
                            Email
                        </button>
                        <button type="button" @click="addField('phone', 'Nomor WhatsApp / HP')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-emerald-100 text-emerald-600 rounded mr-2.5 font-mono text-2xs">TEL</span>
                            Nomor HP / WA
                        </button>
                        <button type="button" @click="addField('dropdown', 'Pilihan Dropdown', ['Pilihan A', 'Pilihan B'])" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-amber-100 text-amber-600 rounded mr-2.5 font-mono text-2xs">SEL</span>
                            Dropdown
                        </button>
                        <button type="button" @click="addField('radio', 'Pilihan Ganda (Radio)', ['Opsi 1', 'Opsi 2'])" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-amber-100 text-amber-600 rounded mr-2.5 font-mono text-2xs">RAD</span>
                            Radio Button
                        </button>
                        <button type="button" @click="addField('signature', 'Tanda Tangan Peserta')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-bold text-indigo-700 bg-indigo-50/30">
                            <span class="p-1 bg-indigo-600 text-white rounded mr-2.5 font-mono text-2xs">SIG</span>
                            Tanda Tangan Canvas
                        </button>
                        <button type="button" @click="addField('camera', 'Foto Selfie Peserta')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-sky-400 hover:bg-sky-50/50 transition text-left text-xs font-bold text-sky-700 bg-sky-50/30">
                            <span class="p-1 bg-sky-600 text-white rounded mr-2.5 font-mono text-2xs">CAM</span>
                            Foto Selfie / Kamera
                        </button>
                        <button type="button" @click="addField('consent', 'Pernyataan / Persetujuan Data')" class="w-full flex items-center p-2.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/50 transition text-left text-xs font-semibold text-slate-700">
                            <span class="p-1 bg-purple-100 text-purple-600 rounded mr-2.5 font-mono text-2xs">CHK</span>
                            Persetujuan / Consent
                        </button>
                    </div>
                </div>

                <!-- Global Settings Box -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengaturan Form</h3>

                    <div>
                        <label class="block text-2xs font-bold uppercase text-slate-500 mb-1.5">Tema Warna Formulir</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="(thm, thmKey) in availableThemes" :key="thmKey">
                                <button type="button"
                                        @click="schema.settings.theme_color = thmKey"
                                        :title="thm.name"
                                        class="group relative flex flex-col items-center justify-center p-2 rounded-xl border transition text-center cursor-pointer"
                                        :class="(schema.settings.theme_color || 'indigo') === thmKey ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-50/40 shadow-2xs font-bold' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                    <span class="w-4 h-4 rounded-full shadow-2xs mb-1" :style="'background-color: ' + thm.primary"></span>
                                    <span class="text-3xs truncate max-w-full text-slate-600" x-text="thm.name.split(' ')[0]"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Header Image Palette -->
                    <div class="pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-2xs font-bold uppercase text-slate-500">Gambar Header</label>
                            <template x-if="schema.settings.header_image">
                                <button type="button" @click="schema.settings.header_image = null" class="text-3xs font-semibold text-rose-500 hover:underline">
                                    Hapus
                                </button>
                            </template>
                        </div>

                        <!-- Presets selector in builder -->
                        <div class="grid grid-cols-3 gap-1.5 mb-2">
                            <template x-for="(hdr, hdrKey) in headerPresets" :key="hdrKey">
                                <button type="button"
                                        @click="schema.settings.header_image = hdr.path"
                                        :title="hdr.name"
                                        class="relative rounded-lg border overflow-hidden transition cursor-pointer"
                                        :class="schema.settings.header_image === hdr.path ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-slate-200 hover:border-slate-300'">
                                    <img :src="hdr.path" class="w-full h-8 object-cover">
                                    <div class="text-3xs text-slate-600 truncate px-1 py-0.5 bg-slate-50 text-left font-medium" x-text="hdr.name.split(' ')[0]"></div>
                                </button>
                            </template>
                        </div>

                        <!-- Upload custom header in builder -->
                        <div>
                            <label class="w-full flex items-center justify-center p-1.5 rounded-lg border border-dashed border-slate-300 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/20 text-3xs font-semibold text-slate-600 cursor-pointer transition">
                                <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Unggah Header Custom</span>
                                <input type="file" accept="image/*" @change="uploadHeaderFile($event)" class="hidden">
                            </label>
                            <span x-show="isUploadingHeader" class="text-3xs text-indigo-600 font-semibold animate-pulse mt-1 block">Mengunggah...</span>
                            <span x-show="headerUploadError" class="text-3xs text-rose-600 font-semibold mt-1 block" x-text="headerUploadError"></span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <label class="block text-2xs font-bold uppercase text-slate-500 mb-1">Kebijakan Anti-Duplikat</label>
                        <select x-model="schema.settings.duplicate_policy" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-xs">
                            <option value="none">Boleh mengisi lebih dari sekali</option>
                            <option value="email">Cegah duplikat email yang sama</option>
                            <option value="phone">Cegah duplikat nomor HP yang sama</option>
                        </select>
                    </div>

                    <div class="pt-2 border-t border-slate-100 space-y-2.5">
                        <label class="flex items-start text-xs text-slate-800 font-bold cursor-pointer">
                            <input type="checkbox" x-model="schema.settings.enable_lookup" class="rounded text-indigo-600 mr-2 mt-0.5">
                            <span>Aktifkan Autocomplete Peserta Terdaftar</span>
                        </label>
                        <div x-show="schema.settings.enable_lookup" class="pl-6 space-y-2 pt-1 text-xs">
                            <p class="text-2xs text-slate-500">Menyuguhkan data nama, instansi, jabatan otomatis dari daftar Excel yang diunggah.</p>
                            <label class="flex items-center text-slate-700 cursor-pointer">
                                <input type="checkbox" x-model="schema.settings.allow_walk_in" class="rounded text-indigo-600 mr-2">
                                <span class="text-2xs">Izinkan tamu manual jika tidak ada di daftar</span>
                            </label>
                            <a href="{{ route('admin.forms.attendees.index', $form) }}" target="_blank" class="inline-flex items-center text-2xs font-bold text-indigo-600 hover:underline">
                                &rarr; Buka Kelola Data Peserta ({{ $form->attendee_lookups_count ?? 0 }})
                            </a>
                        </div>

                        <label class="flex items-center text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="schema.settings.require_signature" class="rounded text-indigo-600 mr-2">
                            <span>Wajibkan Tanda Tangan</span>
                        </label>
                        <label class="flex items-center text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="schema.settings.show_receipt" class="rounded text-indigo-600 mr-2">
                            <span>Tampilkan Bukti Hadir (Receipt)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Middle: Canvas List (6 cols) -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden" :style="getThemeCssVars()">
                    <!-- Top Theme Accent Stripe (shown when no header image) -->
                    <div x-show="!schema.settings.header_image" class="absolute top-0 left-0 right-0 h-1.5 bg-theme"></div>

                    <!-- Header Image Banner in Builder Canvas Preview -->
                    <template x-if="schema.settings.header_image">
                        <div class="relative -mt-6 -mx-6 mb-6 overflow-hidden border-b border-slate-100 group">
                            <img :src="schema.settings.header_image" alt="Header Preview" class="w-full h-auto block">
                            <button type="button"
                                    @click="schema.settings.header_image = null"
                                    title="Hapus Gambar Header"
                                    class="absolute top-3 right-3 px-2.5 py-1 bg-black/60 hover:bg-black/80 text-white rounded-lg text-2xs font-semibold backdrop-blur-xs transition flex items-center space-x-1 shadow-xs">
                                <span>&times; Hapus Header</span>
                            </button>
                        </div>
                    </template>

                    <!-- Form Title & Description Inputs -->
                    <div class="mb-6 pb-6 border-b border-slate-100 space-y-3">
                        <input type="text" x-model="schema.title" placeholder="Judul Formulir Kehadiran"
                            class="w-full text-xl font-black text-slate-900 border-0 border-b border-transparent hover:border-slate-300 focus:border-indigo-500 focus:ring-0 px-0 pb-1">
                        <textarea x-model="schema.description" rows="2" placeholder="Tuliskan petunjuk atau sambutan pengisian form..."
                            class="w-full text-xs text-slate-600 border-0 border-b border-transparent hover:border-slate-300 focus:border-indigo-500 focus:ring-0 px-0 resize-none"></textarea>
                    </div>

                    <!-- Draggable Fields List -->
                    <div id="fields-sortable" class="space-y-3">
                        <template x-for="(field, index) in schema.fields" :key="field.key">
                            <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-indigo-300 transition shadow-2xs cursor-pointer group"
                                :class="selectedField === index ? 'ring-2 ring-indigo-500 border-transparent' : ''"
                                @click="selectedField = index">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="cursor-move text-slate-300 group-hover:text-slate-500">&vellip;&vellip;</span>
                                        <span class="text-xs font-bold text-slate-800" x-text="field.label"></span>
                                        <span x-show="field.required" class="text-rose-500 text-xs font-bold">*</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-2xs uppercase font-mono" x-text="field.type"></span>
                                        <button type="button" @click.stop="removeField(index)" class="text-slate-300 hover:text-rose-500 p-1">
                                            &times;
                                        </button>
                                    </div>
                                </div>

                                <!-- Field mock representation -->
                                <div class="mt-2.5 pt-2 border-t border-slate-100 text-xs text-slate-400">
                                    <template x-if="field.type === 'signature'">
                                        <div class="border border-dashed border-slate-200 rounded-lg p-3 text-center bg-slate-50 text-slate-400 text-2xs">
                                            [Area Canvas Tanda Tangan Peserta]
                                        </div>
                                    </template>
                                    <template x-if="field.type === 'camera' || field.type === 'selfie'">
                                        <div class="border border-dashed border-sky-300 rounded-lg p-3 text-center bg-sky-50/40 text-sky-700 text-2xs flex items-center justify-center space-x-1.5 font-semibold">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>[Area Pengambilan Foto Selfie / Kamera Live Peserta]</span>
                                        </div>
                                    </template>
                                    <template x-if="field.type !== 'signature' && field.type !== 'camera' && field.type !== 'selfie'">
                                        <input type="text" disabled :placeholder="field.placeholder || 'Isian peserta...'"
                                            class="w-full px-3 py-1.5 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-400 cursor-not-allowed">
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="schema.fields.length === 0" class="py-12 text-center text-slate-400 text-xs">
                        Belum ada field pada formulir ini. Klik pilihan jenis field di sebelah kiri untuk menambahkan.
                    </div>
                </div>
            </div>

            <!-- Right: Field Properties Inspector (3 cols) -->
            <div class="lg:col-span-3">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs sticky top-20">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Pengaturan Field Terpilih</h3>

                    <template x-if="selectedField !== null && schema.fields[selectedField]">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-2xs font-bold uppercase text-slate-500 mb-1">Label Field</label>
                                <input type="text" x-model="schema.fields[selectedField].label"
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                            </div>

                            <div>
                                <label class="block text-2xs font-bold uppercase text-slate-500 mb-1">Placeholder</label>
                                <input type="text" x-model="schema.fields[selectedField].placeholder"
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                            </div>

                            <div>
                                <label class="block text-2xs font-bold uppercase text-slate-500 mb-1">Teks Bantuan / Petunjuk</label>
                                <input type="text" x-model="schema.fields[selectedField].help_text"
                                    class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs">
                            </div>

                            <div class="pt-2">
                                <label class="flex items-center text-xs text-slate-800 font-semibold">
                                    <input type="checkbox" x-model="schema.fields[selectedField].required" class="rounded text-indigo-600 mr-2">
                                    <span>Field Wajib Diisi</span>
                                </label>
                            </div>

                            <!-- Options editor for dropdown / radio -->
                            <template x-if="['dropdown', 'radio', 'checkbox'].includes(schema.fields[selectedField].type)">
                                <div class="pt-3 border-t border-slate-100">
                                    <label class="block text-2xs font-bold uppercase text-slate-500 mb-2">Pilihan Opsi</label>
                                    <template x-for="(opt, optIdx) in schema.fields[selectedField].options" :key="optIdx">
                                        <div class="flex items-center space-x-1 mb-1.5">
                                            <input type="text" x-model="schema.fields[selectedField].options[optIdx]"
                                                class="flex-1 px-2.5 py-1 text-xs border border-slate-200 rounded-md">
                                            <button type="button" @click="schema.fields[selectedField].options.splice(optIdx, 1)"
                                                class="text-rose-500 px-1 text-sm font-bold">&times;</button>
                                        </div>
                                    </template>
                                    <button type="button" @click="schema.fields[selectedField].options.push('Opsi Baru')"
                                        class="mt-2 text-2xs font-bold text-indigo-600 hover:underline">
                                        + Tambah Opsi
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedField === null || !schema.fields[selectedField]">
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Pilih salah satu field di kanvas untuk mengubah properti dan pengaturannya.
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Hidden submit form for publishing -->
        <form id="publish-form" method="POST" action="{{ route('admin.forms.publish', $form) }}" class="hidden">
            @csrf
            <input type="hidden" name="schema" id="schema-input">
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('formBuilder', (initialSchema, themes, headerPresets) => ({
                schema: initialSchema,
                availableThemes: themes || {},
                headerPresets: headerPresets || {},
                selectedField: 0,
                isUploadingHeader: false,
                headerUploadError: '',

                async uploadHeaderFile(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    if (file.size > 3 * 1024 * 1024) {
                        this.headerUploadError = 'Ukuran berkas maksimal 3MB.';
                        return;
                    }

                    this.isUploadingHeader = true;
                    this.headerUploadError = '';

                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('{{ route('admin.forms.upload_header') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await res.json();
                        if (res.ok && data.path) {
                            this.schema.settings.header_image = data.path;
                            window.showToast?.('Gambar header berhasil diunggah!', 'success');
                        } else {
                            this.headerUploadError = data.message || 'Gagal mengunggah gambar header.';
                        }
                    } catch (err) {
                        this.headerUploadError = 'Terjadi kesalahan jaringan saat mengunggah.';
                    } finally {
                        this.isUploadingHeader = false;
                        e.target.value = '';
                    }
                },

                getThemeCssVars() {
                    const key = this.schema?.settings?.theme_color || 'indigo';
                    const thm = this.availableThemes[key] || this.availableThemes['indigo'] || {};
                    return `--theme-primary: ${thm.primary || '#4f46e5'}; --theme-primary-hover: ${thm.hover || '#4338ca'}; --theme-primary-light: ${thm.light || '#eef2ff'}; --theme-primary-border: ${thm.border || '#c7d2fe'}; --theme-primary-text: ${thm.text || '#3730a3'}; --theme-primary-ring: ${thm.ring || 'rgba(79, 70, 229, 0.25)'};`;
                },

                init() {
                    this.$nextTick(() => {
                        const el = document.getElementById('fields-sortable');
                        if (el && window.Sortable) {
                            window.Sortable.create(el, {
                                handle: '.cursor-move',
                                animation: 150,
                                onEnd: (evt) => {
                                    const item = this.schema.fields.splice(evt.oldIndex, 1)[0];
                                    this.schema.fields.splice(evt.newIndex, 0, item);
                                    this.selectedField = evt.newIndex;
                                }
                            });
                        }
                    });
                },

                addField(type, defaultLabel, options = null) {
                    const key = 'f_' + Math.random().toString(36).substring(2, 8);
                    this.schema.fields.push({
                        key: key,
                        type: type,
                        label: defaultLabel,
                        placeholder: '',
                        help_text: '',
                        required: type === 'signature',
                        options: options ? [...options] : null
                    });
                    this.selectedField = this.schema.fields.length - 1;
                },

                removeField(index) {
                    const fieldLabel = this.schema.fields[index]?.label || 'Field ini';
                    window.confirmDialog({
                        title: 'Hapus Field Formulir?',
                        message: `Apakah Anda yakin ingin menghapus <strong>"${fieldLabel}"</strong> dari rancangan formulir ini?`,
                        type: 'delete',
                        confirmText: 'Ya, Hapus Field',
                        cancelText: 'Batal',
                        onConfirm: () => {
                            this.schema.fields.splice(index, 1);
                            if (this.selectedField >= this.schema.fields.length) {
                                this.selectedField = Math.max(0, this.schema.fields.length - 1);
                            }
                            window.showToast('Field berhasil dihapus dari kanvas.', 'warning');
                        }
                    });
                },

                saveAndPublish() {
                    if (this.schema.fields.length === 0) {
                        window.alertDialog({
                            title: 'Formulir Masih Kosong',
                            message: 'Silakan tambahkan minimal satu field isian peserta sebelum menerbitkan formulir.',
                            type: 'warning',
                            buttonText: 'Mengerti'
                        });
                        return;
                    }

                    window.confirmDialog({
                        title: 'Terbitkan Versi Baru?',
                        message: `Versi baru dengan <strong>${this.schema.fields.length} field</strong> akan diterbitkan dan langsung aktif bagi peserta kegiatan. Riwayat versi lama tetap tersimpan.`,
                        type: 'primary',
                        confirmText: 'Ya, Terbitkan Versi Baru',
                        cancelText: 'Periksa Kembali',
                        onConfirm: () => {
                            document.getElementById('schema-input').value = JSON.stringify(this.schema);
                            document.getElementById('publish-form').submit();
                        }
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
