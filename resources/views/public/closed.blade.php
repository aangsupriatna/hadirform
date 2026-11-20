<x-public-layout :theme="$form->theme_color ?? 'indigo'">
    <x-slot name="title">Formulir Ditutup - {{ $form->title }}</x-slot>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-lg p-8 text-center space-y-4">
        <div class="inline-flex p-4 bg-amber-50 text-amber-600 rounded-full mb-2">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Formulir Sedang Ditutup</h1>
        <p class="text-sm text-slate-600 max-w-md mx-auto">
            {{ $customMessage ?? 'Maaf, formulir kehadiran "' . $form->title . '" untuk kegiatan "' . $form->event->title . '" saat ini sedang tidak menerima respons baru atau telah melewati batas waktu pengisian.' }}
        </p>

        <div class="pt-4 border-t border-slate-100 text-xs text-slate-400">
            Jika Anda memiliki pertanyaan, silakan hubungi panitia penyelenggara kegiatan <strong>{{ $form->organization->name }}</strong>.
        </div>
    </div>
</x-public-layout>
