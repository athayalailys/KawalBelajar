<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- KRUSIAL: Wajib ada untuk Keamanan & AJAX/Fetch/Alpine.js Request -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO & Metadata Dinamis -->
    <title>{{ $title ?? 'KawalBelajar - Bimbingan Belajar Terintegrasi & Terpercaya' }}</title>
    <meta name="description" content="{{ $description ?? 'Platform bimbingan belajar dengan tutor terverifikasi, pilihan belajar daring atau tatap muka di rumah, serta kelompok belajar yang lebih hemat.' }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="{{ $title ?? 'KawalBelajar' }}">
    <meta property="og:description" content="Bimbingan belajar berkualitas dengan tutor terverifikasi dan laporan digital terintegrasi.">
    <meta property="og:type" content="website">

    <!-- Fonts Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Vite Assets (Tailwind CSS & Alpine.js / App JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Analytics Partial (Safe Include) -->
    @includeWhen(view()->exists('layouts.partials.analytics'), 'layouts.partials.analytics')
    
    <!-- KRUSIAL: Tempat CSS Tambahan dari Halaman Anak -->
    @stack('styles')
</head>
<body class="bg-slate-50/60 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-amber-400 selection:text-slate-900">
    
    <!-- Header Navigation Bar Modular -->
    <x-common.navbar />

    <!-- Main Content Area -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer Modular -->
    <x-common.footer />

    <!-- KRUSIAL: Tempat JavaScript Tambahan dari Halaman Anak -->
    @stack('scripts')
</body>
</html>
