<x-app-layout>
    <x-slot name="title">{{ $event->title }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $event->title }}</h1>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full uppercase
                        {{ $event->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $event->status }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">{{ $event->venue ?? 'Tempat belum ditentukan' }} &bull; {{ $event->starts_at?->format('d M Y H:i') ?? '-' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.forms.create', ['event_id' => $event->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Form di Kegiatan Ini
                </a>
                <a href="{{ route('admin.events.edit', $event) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition">
                    Edit Kegiatan
                </a>
            </div>
        </div>

        @if($event->description)
            <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 text-sm text-slate-700">
                {{ $event->description }}
            </div>
        @endif

        <!-- Forms within this event -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Formulir Kehadiran Terkait</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($event->forms as $form)
                    <div class="border border-slate-200 rounded-xl p-5 hover:border-indigo-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full uppercase
                                    {{ $form->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $form->status }}
                                </span>
                                <span class="text-xs text-slate-400 font-mono">/f/{{ $form->public_slug }}</span>
                            </div>
                            <h3 class="font-bold text-slate-900 text-base">{{ $form->title }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ Str::limit($form->description, 90) }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-600">
                                <strong>{{ $form->responses_count }}</strong> respons kehadiran
                            </span>
                            <div class="space-x-2">
                                <a href="{{ route('admin.forms.show', $form) }}" class="text-xs font-bold text-indigo-600 hover:underline">
                                    Kelola Form &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-8 text-center text-slate-400 text-sm">
                        Belum ada formulir kehadiran untuk kegiatan ini. Klik "Tambah Form di Kegiatan Ini" di atas.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
