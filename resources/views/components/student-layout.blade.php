<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Portal Pembelajar - KawalBelajar' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    @php($isFocusedBooking = request()->routeIs('student.booking'))
    <div class="flex min-h-screen">
        <!-- Navigasi portal pembelajar -->
        @unless($isFocusedBooking)
            <x-student.sidebar />
        @endunless

        <!-- Area Konten Utama -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Bar / Breadcrumb & Notifikasi -->
            <header class="px-8 py-5 flex items-center justify-between">
                @if($isFocusedBooking)
                    <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-900 bg-white px-5 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Beranda
                    </a>
                @else
                    <!-- Breadcrumb -->
                    <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                        <span>Portal Pembelajar</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-slate-900 font-bold">{{ match(true) {
                            request()->routeIs('student.dashboard') => 'Beranda dan Jadwal Hari Ini',
                            request()->routeIs('student.catalog') => 'Jelajahi Kelas',
                            request()->routeIs('student.booking') => 'Pesan Bimbingan',
                            request()->routeIs('student.schedule') => 'Jadwal Belajar',
                            request()->routeIs('student.classroom') => 'Ruang Kelas',
                            request()->routeIs('student.report') => 'Rapor Belajar',
                            request()->routeIs('student.parent') => 'Mode Orang Tua',
                            default => 'Portal Pembelajar',
                        } }}</span>
                    </div>
                @endif

                <!-- Lonceng Notifikasi -->
                @unless($isFocusedBooking)
                    <button type="button" aria-label="Notifikasi" class="relative p-2.5 bg-white border border-slate-300 rounded-full text-slate-700 hover:bg-slate-50 shadow-sm transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if (($notificationCount ?? 0) > 0)
                            <span class="absolute top-0 right-0 w-3 h-3 bg-rose-500 border-2 border-white rounded-full" aria-label="Ada notifikasi baru"></span>
                        @endif
                    </button>
                @endunless
            </header>

            @unless($isFocusedBooking)
            <nav class="mx-4 mb-5 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 lg:hidden">
                <a href="{{ route('student.dashboard') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('student.dashboard') ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">Beranda</a>
                <a href="{{ route('student.booking') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('student.booking') ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">Jelajahi Kelas</a>
                <a href="{{ route('student.schedule') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('student.schedule') ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">Jadwal</a>
                <a href="{{ route('student.classroom') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('student.classroom') ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">Ruang Kelas</a>
                <a href="{{ route('student.report') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('student.report') ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">Rapor</a>
                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Keluar</button>
                </form>
            </nav>
            @endunless

            <!-- Slot Konten Halaman -->
            <div class="px-4 pb-8 flex-1 sm:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
