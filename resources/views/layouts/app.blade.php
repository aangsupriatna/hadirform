<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'HadirForm' }} - Digital Attendance & Signature Form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Top Navigation -->
    <header class="bg-indigo-700 text-white shadow-md sticky top-0 z-30">
        <div class="max-w-[1720px] w-full mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                <button @click="sidebarOpen = true" class="md:hidden p-2 rounded-xl hover:bg-indigo-600 focus:outline-none transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5">
                    <span class="p-1.5 bg-white text-indigo-700 rounded-xl font-black text-lg shadow-xs">HF</span>
                    <span class="font-bold text-xl tracking-tight">Hadir<span class="text-indigo-200">Form</span></span>
                </a>
                <span class="hidden sm:inline-flex items-center px-3 py-1 text-xs font-medium bg-indigo-800 text-indigo-200 rounded-full border border-indigo-600/40">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>
                    {{ auth()->user()->organization->name ?? 'Organisasi' }}
                </span>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold leading-tight">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-indigo-200 uppercase tracking-wider font-semibold">{{ auth()->user()->role }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-bold bg-indigo-800 hover:bg-indigo-900 rounded-xl transition shadow-xs border border-indigo-600/40">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer Backdrop & Menu -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden"
         @click="sidebarOpen = false"
         style="display: none;"></div>

    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl p-6 flex flex-col justify-between md:hidden"
         style="display: none;">
        <div>
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <span class="p-1.5 bg-indigo-600 text-white rounded-lg font-black text-sm">HF</span>
                    <span class="font-bold text-lg text-slate-900">Hadir<span class="text-indigo-600">Form</span></span>
                </div>
                <button @click="sidebarOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="space-y-1.5 mt-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.events.index') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.events.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Kegiatan (Events)
                </a>
                <a href="{{ route('admin.forms.index') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.forms.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Formulir Kehadiran
                </a>
                <a href="{{ route('admin.responses.index') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.responses.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Data Kehadiran
                </a>
                <a href="{{ route('admin.audit.index') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.audit.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Audit Trail & Log
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3.5 py-2.5 text-sm font-semibold rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan Aplikasi
                </a>
            </nav>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <div class="text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</div>
            <div class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }} &bull; {{ auth()->user()->organization->name ?? '' }}</div>
        </div>
    </div>

    <!-- Main Container (Wide & Spacious) -->
    <div class="flex-1 flex max-w-[1720px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-64 hidden md:block flex-shrink-0">
            <div class="sticky top-22 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 space-y-4">
                <div class="px-2 pb-2 border-b border-slate-100">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Navigasi Utama</div>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.events.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.events.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.events.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Kegiatan (Events)
                    </a>

                    <a href="{{ route('admin.forms.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.forms.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.forms.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Formulir Kehadiran
                    </a>

                    <a href="{{ route('admin.responses.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.responses.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.responses.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Data Kehadiran
                    </a>

                    <a href="{{ route('admin.audit.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.audit.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.audit.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Audit Trail & Log
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-xl transition {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 mr-3 {{ request()->routeIs('admin.settings.*') ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Pengaturan Aplikasi
                    </a>
                </nav>

                <div class="pt-4 border-t border-slate-100">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/60 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Organisasi</div>
                            <a href="{{ route('admin.settings.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Atur &rarr;</a>
                        </div>
                        <div class="font-bold text-slate-800 truncate mt-0.5">{{ auth()->user()->organization->name ?? 'Default' }}</div>
                        <div class="text-xs text-emerald-600 font-semibold mt-1 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                            Online & Siap Pakai
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area (Wide, No Cramping) -->
        <main class="flex-1 min-w-0">
            <!-- Flash Message Alerts -->
            @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                    <div class="font-bold text-sm mb-1.5 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Periksa kembali data Anda:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 ml-1 text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <!-- Global Modal & Dialog System (Delete, Confirm, Alert, Notice) -->
    <div x-data="globalDialogManager()"
         @open-confirm-dialog.window="showConfirm($event.detail)"
         @open-alert-dialog.window="showAlert($event.detail)"
         @show-toast.window="addToast($event.detail)"
         x-cloak>

        <!-- Confirmation / Delete Modal Dialog -->
        <div x-show="confirmModal.open"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="closeConfirm()">
            <!-- Backdrop -->
            <div x-show="confirmModal.open"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="closeConfirm()"></div>

            <!-- Dialog Modal Box -->
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="confirmModal.open"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200/80 p-6">
                    
                    <div class="flex items-start space-x-4">
                        <!-- Icon Circle -->
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl"
                             :class="{
                                 'bg-rose-100 text-rose-600': confirmModal.type === 'delete' || confirmModal.type === 'danger',
                                 'bg-amber-100 text-amber-600': confirmModal.type === 'warning',
                                 'bg-indigo-100 text-indigo-600': confirmModal.type === 'primary' || confirmModal.type === 'info'
                             }">
                            <template x-if="confirmModal.type === 'delete' || confirmModal.type === 'danger'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </template>
                            <template x-if="confirmModal.type === 'warning'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <template x-if="confirmModal.type === 'primary' || confirmModal.type === 'info'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight" x-text="confirmModal.title"></h3>
                            <div class="mt-2 text-sm text-slate-600 leading-relaxed" x-html="confirmModal.message"></div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button"
                                @click="closeConfirm()"
                                class="inline-flex justify-center items-center px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs">
                            <span x-text="confirmModal.cancelText"></span>
                        </button>
                        <button type="button"
                                @click="executeConfirm()"
                                class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl text-white text-xs font-bold transition shadow-md"
                                :class="{
                                    'bg-rose-600 hover:bg-rose-700': confirmModal.type === 'delete' || confirmModal.type === 'danger',
                                    'bg-amber-600 hover:bg-amber-700': confirmModal.type === 'warning',
                                    'bg-indigo-600 hover:bg-indigo-700': confirmModal.type === 'primary' || confirmModal.type === 'info'
                                }">
                            <span x-text="confirmModal.confirmText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert / Notice Modal Dialog -->
        <div x-show="alertModal.open"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             @keydown.escape.window="alertModal.open = false">
            <div x-show="alertModal.open"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                 @click="alertModal.open = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="alertModal.open"
                     x-transition:enter="ease-out duration-250"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200/80 p-6">
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl"
                             :class="{
                                 'bg-blue-100 text-blue-600': alertModal.type === 'info',
                                 'bg-amber-100 text-amber-600': alertModal.type === 'warning',
                                 'bg-rose-100 text-rose-600': alertModal.type === 'error',
                                 'bg-emerald-100 text-emerald-600': alertModal.type === 'success'
                             }">
                            <template x-if="alertModal.type === 'warning'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </template>
                            <template x-if="alertModal.type === 'error'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="alertModal.type === 'success'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                            <template x-if="alertModal.type === 'info'">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-black text-slate-900 tracking-tight" x-text="alertModal.title"></h3>
                            <div class="mt-2 text-sm text-slate-600 leading-relaxed" x-html="alertModal.message"></div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end pt-4 border-t border-slate-100">
                        <button type="button"
                                @click="alertModal.open = false"
                                class="inline-flex justify-center items-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs">
                            <span x-text="alertModal.buttonText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Toast Notifications -->
        <div class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 pointer-events-none">
            <template x-for="t in toasts" :key="t.id">
                <div x-show="t.visible"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-3"
                     class="pointer-events-auto flex items-center space-x-2.5 px-4 py-3 rounded-2xl shadow-xl border text-xs font-bold"
                     :class="{
                         'bg-slate-900 text-white border-slate-700': t.type === 'success' || !t.type,
                         'bg-rose-600 text-white border-rose-700': t.type === 'error',
                         'bg-amber-500 text-white border-amber-600': t.type === 'warning'
                     }">
                    <span class="text-emerald-400 font-black">&check;</span>
                    <span x-text="t.message"></span>
                </div>
            </template>
        </div>
    </div>

    <script>
        function globalDialogManager() {
            return {
                confirmModal: {
                    open: false,
                    title: 'Konfirmasi Tindakan',
                    message: 'Apakah Anda yakin ingin melanjutkan?',
                    type: 'primary',
                    confirmText: 'Ya, Lanjutkan',
                    cancelText: 'Batal',
                    onConfirm: null,
                    formId: null,
                    formElement: null,
                },
                alertModal: {
                    open: false,
                    title: 'Pemberitahuan',
                    message: '',
                    type: 'info',
                    buttonText: 'Mengerti'
                },
                toasts: [],

                showConfirm(detail) {
                    this.confirmModal = {
                        open: true,
                        title: detail.title || 'Konfirmasi',
                        message: detail.message || 'Apakah Anda yakin?',
                        type: detail.type || 'primary',
                        confirmText: detail.confirmText || 'Ya, Lanjutkan',
                        cancelText: detail.cancelText || 'Batal',
                        onConfirm: detail.onConfirm || null,
                        formId: detail.formId || null,
                        formElement: detail.formElement || null,
                    };
                },

                closeConfirm() {
                    this.confirmModal.open = false;
                },

                executeConfirm() {
                    const action = this.confirmModal.onConfirm;
                    const fId = this.confirmModal.formId;
                    const fEl = this.confirmModal.formElement;
                    this.closeConfirm();

                    if (typeof action === 'function') {
                        action();
                    } else if (fId) {
                        const form = document.getElementById(fId);
                        if (form) form.submit();
                    } else if (fEl) {
                        fEl.submit();
                    }
                },

                showAlert(detail) {
                    this.alertModal = {
                        open: true,
                        title: detail.title || 'Informasi',
                        message: detail.message || '',
                        type: detail.type || 'info',
                        buttonText: detail.buttonText || 'Mengerti'
                    };
                },

                addToast(detail) {
                    const id = Date.now() + Math.random();
                    const toast = {
                        id: id,
                        message: detail.message || '',
                        type: detail.type || 'success',
                        visible: true
                    };
                    this.toasts.push(toast);
                    setTimeout(() => {
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }, detail.duration || 3000);
                }
            };
        }

        // Global Helper Functions
        window.confirmDialog = function(options) {
            window.dispatchEvent(new CustomEvent('open-confirm-dialog', { detail: options }));
        };

        window.alertDialog = function(options) {
            window.dispatchEvent(new CustomEvent('open-alert-dialog', { detail: options }));
        };

        window.showToast = function(message, type = 'success', duration = 3000) {
            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message, type, duration } }));
        };
    </script>

    @stack('scripts')
</body>
</html>
