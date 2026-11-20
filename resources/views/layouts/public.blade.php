<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100" style="{{ $themeStyles ?? '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Presensi Kehadiran' }} - HadirForm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800 antialiased flex flex-col justify-between" style="{{ $themeStyles ?? '' }}">
    <header class="bg-white border-b border-slate-200 py-3 shadow-xs">
        <div class="max-w-xl mx-auto px-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="p-1 bg-theme text-white rounded font-black text-sm">HF</span>
                <span class="font-bold text-slate-900 tracking-tight">Hadir<span class="text-theme">Form</span></span>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full border border-slate-200">
                Presensi Digital
            </span>
        </div>
    </header>

    <main class="flex-1 py-6 px-4">
        <div class="max-w-xl mx-auto">
            {{ $slot }}
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-400">
        Didukung oleh <a href="#" class="font-semibold text-slate-500 hover:text-theme">HadirForm</a> — Digital Attendance & Signature
    </footer>

    @stack('scripts')
</body>
</html>
