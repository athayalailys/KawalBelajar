@props(['title' => 'Portal Admin - KawalBelajar', 'heading' => 'Beranda Admin'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100/70 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col justify-between border-r border-slate-200 bg-white p-4 lg:flex">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 border-b border-slate-100 pb-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-slate-900 bg-amber-300 text-xl font-black">K</span>
                    <span><strong class="block text-lg text-slate-900">Kawal<span class="text-cyan-600">Belajar</span></strong><small class="font-bold tracking-wider text-slate-500">PORTAL ADMIN</small></span>
                </a>
                <nav class="mt-5 space-y-1.5" aria-label="Navigasi Admin">
                    @foreach ([['admin.dashboard', 'Beranda'], ['admin.tutors', 'Tutor & Verifikasi'], ['admin.tariffs', 'Tarif Master'], ['admin.subjects', 'Mata Pelajaran'], ['admin.finance', 'Keuangan & Payroll']] as [$routeName, $label])
                        <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName)) aria-current="page" @endif class="block rounded-xl border px-3.5 py-3 text-sm {{ request()->routeIs($routeName) ? 'border-amber-300 bg-amber-50 font-bold text-slate-900' : 'border-transparent text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="border-t border-slate-200 pt-4">
                <p class="mb-2 truncate rounded-xl bg-slate-50 p-3 text-xs font-semibold">{{ auth()->user()?->full_name ?? 'Administrator' }}</p>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-xl px-3.5 py-2.5 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50">Keluar</button></form>
            </div>
        </aside>
        <main class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between px-4 py-5 sm:px-8">
                <div class="text-sm font-medium text-slate-500">Portal Admin <span class="mx-2">›</span><strong class="text-slate-900">{{ $heading }}</strong></div>
                <div class="flex items-center gap-3"><span class="hidden text-sm font-semibold text-slate-600 sm:inline">{{ auth()->user()?->full_name ?? 'Admin' }}</span><button type="button" aria-label="Notifikasi" class="relative rounded-full border border-slate-300 bg-white p-2.5 text-slate-700 shadow-sm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg></button></div>
            </header>
            <nav class="mx-4 mb-5 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 lg:hidden" aria-label="Navigasi Admin mobile">
                @foreach ([['admin.dashboard', 'Beranda'], ['admin.tutors', 'Tutor'], ['admin.tariffs', 'Tarif'], ['admin.subjects', 'Mapel'], ['admin.finance', 'Keuangan']] as [$routeName, $label])
                    <a href="{{ route($routeName) }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs($routeName) ? 'bg-amber-200 text-slate-900' : 'text-slate-600' }}">{{ $label }}</a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold text-rose-700">Keluar</button></form>
            </nav>
            <section class="flex-1 px-4 pb-8 sm:px-8">{{ $slot }}</section>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
