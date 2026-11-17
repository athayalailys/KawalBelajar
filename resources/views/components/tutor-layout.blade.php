@props(['title' => 'Portal Tutor - KawalBelajar', 'heading' => 'Portal Tutor'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-google-analytics />
    <title>{{ $title ?? 'Portal Tutor - KawalBelajar' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <x-tutor.sidebar />
        <main class="flex min-w-0 flex-1 flex-col">
            <x-tutor.topbar :title="$heading ?? 'Portal Tutor'" />
            <x-tutor.mobile-nav />
            <div class="flex-1 px-4 pb-8 sm:px-8">{{ $slot }}</div>
        </main>
    </div>
</body>
</html>
