<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO & Metadata -->
    <title>{{ $title ?? 'KawalBelajar - Bimbingan Belajar Terintegrasi & Terpercaya' }}</title>
    <meta name="description" content="{{ $description ?? 'Platform bimbingan belajar terintegrasi dengan tutor terverifikasi, metode Home Visit & Online Zoom, serta sistem patungan hemat.' }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="{{ $title ?? 'KawalBelajar' }}">
    <meta property="og:description" content="Bimbingan belajar berkualitas dengan tutor terverifikasi dan laporan digital terintegrasi.">
    <meta property="og:type" content="website">

    <!-- Fonts Link (Jika Menggunakan Google Fonts) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Vite Assets (Tailwind CSS & Alpine.js / App JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Analytics Partial (Disertai Safe Include Check) -->
    @includeWhen(view()->exists('layouts.partials.analytics'), 'layouts.partials.analytics')
    
    @stack('styles')
</head>
<body class="bg-brand-bg text-brand-dark font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-brand-teal selection:text-white">
    
    <!-- Header Navigation Bar -->
    <x-navbar />

    <!-- Main Content Slot -->
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-16 sm:space-y-20">
        {{ $slot }}
    </main>

    <!-- Footer Section -->
    <x-footer />

    @stack('scripts')
</body>
</html>