<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal Siswa - KawalBelajar' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigasi Siswa -->
        <x-student.sidebar />

        <!-- Area Konten Utama -->
        <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Top Bar / Breadcrumb & Notifikasi -->
            <header class="px-8 py-5 flex items-center justify-between">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                    <span>Portal Siswa</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-slate-900 font-bold">Beranda & Jadwal Hari Ini</span>
                </div>

                <!-- Lonceng Notifikasi -->
                <button class="relative p-2.5 bg-white border border-slate-300 rounded-full text-slate-700 hover:bg-slate-50 shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <!-- Badge Titik Merah -->
                    <span class="absolute top-0 right-0 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></span>
                </button>
            </header>

            <!-- Slot Konten Halaman -->
            <div class="px-8 pb-8 flex-1">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>