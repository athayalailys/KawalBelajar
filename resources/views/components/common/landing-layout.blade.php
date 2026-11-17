<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-google-analytics />
    <title>{{ $title ?? 'KawalBelajar' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-light font-sans text-brand-dark antialiased">

    <!-- Navbar Component -->
    <x-common.navbar />

    <!-- Tempat Konten Dinamis (welcome.blade.php akan masuk ke $slot) -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer Component -->
    <x-common.footer />

</body>
</html>
