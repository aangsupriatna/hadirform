<x-app-layout>
    <x-slot name="title">Peserta Terdaftar: {{ $form->title }}</x-slot>

    <div x-data="{
            showAddModal: false,
            showEditModal: false,
            editData: {
                id: null,
                name: '',
                institution: '',
                position: '',
                email: '',
                phone: '',
                identifier: '',
                actionUrl: ''
            },
            openEdit(att, url) {
                this.editData = {
                    id: att.id,
                    name: att.name || '',
                    institution: att.institution || '',
                    position: att.position || '',
                    email: att.email || '',
                    phone: att.phone || '',
                    identifier: att.identifier || '',
                    actionUrl: url
                };
                this.showEditModal = true;
            }
        }" class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold uppercase">
                        Master Data Peserta
                    </span>
                    <span class="text-xs text-slate-400">&bull; {{ $form->title }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Daftar Peserta Undangan (Lookup)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Data peserta yang diunggah atau ditambahkan akan otomatis muncul sebagai saran (*autocomplete*) saat peserta mengetik di formulir publik.
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button" @click="showAddModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Peserta Manual
                </button>

                <a href="{{ route('admin.forms.show', $form) }}" class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-slate-100 rounded-xl transition">
                    &larr; Detail Form
                </a>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Diundang / Terdaftar</div>
                <div class="text-3xl font-black text-slate-900 mt-1">{{ $totalCount }}</div>
                <div class="text-2xs text-slate-400 mt-2">Peserta dalam database master form</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-600">Sudah Hadir / Check-In</div>
                <div class="text-3xl font-black text-emerald-600 mt-1">{{ $attendedCount }}</div>
                <div class="text-2xs text-emerald-700 font-semibold mt-2">
                    {{ $totalCount > 0 ? round(($attendedCount / $totalCount) * 100) : 0 }}% tingkat kehadiran
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-bold uppercase tracking-wider text-amber-600">Belum Hadir</div>
                <div class="text-3xl font-black text-amber-600 mt-1">{{ $totalCount - $attendedCount }}</div>
                <div class="text-2xs text-slate-400 mt-2">Menunggu kedatangan</div>
            </div>
        </div>

        <!-- Import Box & Search Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Upload Excel Card (1 col) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Impor Data Peserta</h2>
                    <a href="{{ route('admin.forms.attendees.template', $form) }}" class="text-xs font-bold text-indigo-600 hover:underline flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Template
                    </a>
                </div>

                <form method="POST" action="{{ route('admin.forms.attendees.import', $form) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">File Excel atau CSV <span class="text-rose-500">*</span></label>
                        <input type="file" name="file" required accept=".xlsx,.xls,.csv"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-slate-200 rounded-xl p-1.5">
                        <p class="text-2xs text-slate-400 mt-1.5">Format didukung: XLSX, XLS, CSV. Kolom otomatis dipetakan (Nama, Instansi, Jabatan, Email, No HP, NIP).</p>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center text-xs text-slate-700">
                            <input type="checkbox" name="replace" value="1" class="rounded text-indigo-600 mr-2">
                            <span>Ganti data lama (hapus peserta sebelumnya)</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Unggah & Impor Peserta
                    </button>
                </form>

                @if($totalCount > 0)
                    <div class="pt-4 border-t border-slate-100">
                        <button type="button"
                                onclick="window.confirmDialog({
                                    title: 'Bersihkan Semua Data Peserta?',
                                    message: 'Apakah Anda yakin ingin menghapus seluruh <strong>{{ $totalCount }} data peserta terdaftar</strong> untuk formulir ini? Peserta yang belum hadir tidak akan muncul lagi di autocomplete.',
                                    type: 'delete',
                                    confirmText: 'Ya, Bersihkan Semua',
                                    formId: 'clear-all-attendees-form'
                                })"
                                class="w-full py-2 text-center text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                            Bersihkan Semua Peserta
                        </button>
                        <form id="clear-all-attendees-form" method="POST" action="{{ route('admin.forms.attendees.destroy', $form) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @endif
            </div>

            <!-- Right: Attendees List (2 cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Daftar Peserta Terdaftar</h2>

                    <form method="GET" class="flex items-center gap-2">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIP, email..."
                            class="px-3 py-1.5 text-xs rounded-xl border border-slate-300 w-48 focus:ring-2 focus:ring-indigo-500">
                        <select name="status" class="px-2.5 py-1.5 text-xs rounded-xl border border-slate-300">
                            <option value="">Semua</option>
                            <option value="attended" {{ request('status') === 'attended' ? 'selected' : '' }}>Sudah Hadir</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Belum Hadir</option>
                        </select>
                        <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white font-bold text-xs rounded-xl">
                            Cari
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                                <th class="pb-2.5">Nama Lengkap</th>
                                <th class="pb-2.5">Instansi / Jabatan</th>
                                <th class="pb-2.5">Kontak</th>
                                <th class="pb-2.5">NIP / Tiket</th>
                                <th class="pb-2.5 text-center">Status</th>
                                <th class="pb-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($attendees as $att)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-2.5 font-bold text-slate-900">
                                        {{ $att->name }}
                                    </td>
                                    <td class="py-2.5">
                                        <div class="font-medium text-slate-800">{{ $att->institution ?? '-' }}</div>
                                        <div class="text-2xs text-slate-400">{{ $att->position }}</div>
                                    </td>
                                    <td class="py-2.5">
                                        <div>{{ $att->phone ?? '-' }}</div>
                                        <div class="text-2xs text-slate-400">{{ $att->email }}</div>
                                    </td>
                                    <td class="py-2.5 font-mono text-slate-600">
                                        {{ $att->identifier ?? '-' }}
                                    </td>
                                    <td class="py-2.5 text-center">
                                        @if($att->is_attended)
                                            <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-2xs bg-emerald-100 text-emerald-700">
                                                &check; Hadir
                                            </span>
                                            <div class="text-2xs text-slate-400 mt-0.5">{{ $att->attended_at?->format('H:i') }}</div>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full font-bold uppercase text-2xs bg-slate-100 text-slate-500">
                                                Belum
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 text-right whitespace-nowrap space-x-1">
                                        <button type="button"
                                                @click="openEdit({{ json_encode($att) }}, '{{ route('admin.forms.attendees.update', [$form, $att]) }}')"
                                                class="px-2 py-1 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-semibold text-2xs rounded-lg transition">
                                            Edit
                                        </button>
                                        <button type="button"
                                                onclick="window.confirmDialog({
                                                    title: 'Hapus Peserta Undangan?',
                                                    message: 'Apakah Anda yakin ingin menghapus peserta <strong>{{ addslashes($att->name) }}</strong> dari daftar undangan?',
                                                    type: 'delete',
                                                    confirmText: 'Ya, Hapus',
                                                    formId: 'delete-att-{{ $att->id }}'
                                                })"
                                                class="px-2 py-1 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-semibold text-2xs rounded-lg transition">
                                            Hapus
                                        </button>
                                        <form id="delete-att-{{ $att->id }}" method="POST" action="{{ route('admin.forms.attendees.destroy_single', [$form, $att]) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        Belum ada data peserta yang diunggah. Silakan unggah file Excel/CSV di panel kiri atau klik &ldquo;Tambah Peserta Manual&rdquo;.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($attendees->hasPages())
                    <div class="pt-3 border-t border-slate-100">
                        {{ $attendees->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Dialog: Tambah Peserta Manual -->
        <div x-show="showAddModal"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="showAddModal = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showAddModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200/80 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Tambah Peserta Undangan</h3>
                            <p class="text-xs text-slate-400">Peserta akan masuk ke daftar autocomplete formulir presensi.</p>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.forms.attendees.store', $form) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Dr. Budi Santoso, M.Kom"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Instansi / Lembaga</label>
                                <input type="text" name="institution" placeholder="Contoh: Kementerian Komdigi"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Jabatan / Posisi</label>
                                <input type="text" name="position" placeholder="Contoh: Koordinator Tim"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Alamat Email</label>
                                <input type="email" name="email" placeholder="budi@example.com"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                                <input type="text" name="phone" placeholder="081234567890"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">NIP / Nomor Peserta / Tiket</label>
                            <input type="text" name="identifier" placeholder="198501012010011001"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md">Simpan Peserta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Dialog: Edit Peserta -->
        <div x-show="showEditModal"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="showEditModal = false">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showEditModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200/80 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Edit Data Peserta</h3>
                            <p class="text-xs text-slate-400">Perbarui informasi peserta dalam master data undangan.</p>
                        </div>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">&times;</button>
                    </div>

                    <form method="POST" :action="editData.actionUrl" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editData.name" required
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Instansi / Lembaga</label>
                                <input type="text" name="institution" x-model="editData.institution"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Jabatan / Posisi</label>
                                <input type="text" name="position" x-model="editData.position"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Alamat Email</label>
                                <input type="email" name="email" x-model="editData.email"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                                <input type="text" name="phone" x-model="editData.phone"
                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">NIP / Nomor Peserta / Tiket</label>
                            <input type="text" name="identifier" x-model="editData.identifier"
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                            <button type="submit" class="px-5 py-2 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
