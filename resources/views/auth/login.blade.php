<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - HadirForm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <div class="inline-flex p-3 bg-indigo-600 text-white rounded-2xl shadow-md mb-3">
                <span class="font-black text-2xl tracking-wider">HF</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Masuk ke HadirForm</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Manajemen Presensi & Tanda Tangan Digital</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', 'admin@hadirform.test') }}" required autofocus
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi</label>
                <input type="password" id="password" name="password" value="password123" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-xs">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg transition duration-200 text-sm">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
            Kredensial demo: <strong class="text-slate-600">admin@hadirform.test</strong> / <strong class="text-slate-600">password123</strong>
        </div>
    </div>
</body>
</html>
