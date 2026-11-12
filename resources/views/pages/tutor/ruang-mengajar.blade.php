<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Mengajar - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#e7f5f9] font-sans text-slate-950 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
        <aside class="flex flex-col bg-white px-5 py-6 lg:min-h-screen lg:px-7">
            <a href="{{ url('/') }}" class="mb-8 flex items-center gap-2 text-xl font-extrabold tracking-tight text-[#102d3f]">
                <span class="flex h-8 w-8 items-center justify-center rounded bg-amber-400 text-lg" aria-hidden="true">✿</span>
                <span>Kawal<span class="text-cyan-600">Belajar</span></span>
            </a>
            <p class="mb-5 rounded-full bg-[#102d3f] px-4 py-2 text-xs font-semibold text-white">○ Tutor</p>
            <nav aria-label="Menu tutor" class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-1">
                <a href="{{ route('tutor.dashboard') }}" class="rounded-lg border border-black bg-white px-4 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Beranda</a>
                <a href="{{ route('tutor.classes') }}" class="rounded-lg border border-black bg-white px-4 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Kelola Kelas</a>
                <a href="{{ route('tutor.classroom') }}" aria-current="page" class="rounded-lg border border-black bg-amber-400 px-4 py-2.5 text-center text-sm font-bold">Ruang Mengajar</a>
                <a href="{{ Route::has('tutor.earnings') ? route('tutor.earnings') : '#' }}" class="rounded-lg border border-black bg-white px-4 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Penghasilan</a>
                <a href="{{ Route::has('tutor.settings') ? route('tutor.settings') : '#' }}" class="rounded-lg border border-black bg-white px-4 py-2.5 text-center text-sm font-semibold hover:bg-slate-50">Pengaturan</a>
            </nav>
        </aside>

        <main class="min-w-0">
            <header class="flex flex-wrap items-center justify-between gap-4 bg-white px-6 py-4 sm:px-9">
                <h1 class="text-lg font-extrabold">Portal Tutor</h1>
                <div class="flex items-center gap-4 text-xs font-semibold">
                    @auth
                        @if (Route::has('logout'))
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="text-slate-700 hover:text-cyan-700">LogOut</button></form>
                        @endif
                        <span class="min-w-20 rounded-full bg-amber-400 px-4 py-1.5 text-center">Hai {{ $teacher->user?->full_name ?? 'Tutor' }}</span>
                    @endauth
                </div>
            </header>

            <section class="mx-auto max-w-6xl px-5 py-6 sm:px-8 sm:py-8">
                <h2 class="mb-5 text-xl font-extrabold">Pilih Kelas untuk Mengajar</h2>
                <div class="space-y-3">
                    @forelse ($classes as $class)
                        @php
                            $methodLabel = match ($class->learning_method) {
                                'online_zoom' => 'Online (Zoom)',
                                'home_visit' => 'HomeVisit',
                                default => 'Metode belum ditentukan',
                            };
                        @endphp
                        <article class="grid gap-4 rounded-2xl border border-[#c7e6ed] bg-white p-5 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                            <div>
                                <div class="mb-2 flex flex-wrap gap-2 text-xs font-semibold">
                                    @if ($class->grade_level)<span class="rounded-full bg-cyan-600 px-4 py-1 text-white">Jenjang {{ $class->grade_level }}</span>@endif
                                    @if ($class->subject_name)<span class="rounded-full bg-cyan-100 px-4 py-1 text-[#102d3f]">{{ $class->subject_name }}</span>@endif
                                    <span class="rounded-full px-4 py-1 text-white {{ $class->learning_method === 'online_zoom' ? 'bg-green-500' : 'bg-[#102d3f]' }}">{{ $methodLabel }}</span>
                                </div>
                                <h3 class="text-lg font-extrabold">{{ $class->package_name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $class->description ?? $class->target_learning ?? 'Kelas bimbingan' }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end">
                                <span class="rounded-full bg-green-100 px-4 py-1.5 text-xs font-bold text-green-800">{{ $class->enrolled_students_count }} Siswa Terdaftar</span>
                                <a href="{{ route('tutor.classroom.show', $class->package_id) }}" class="rounded-full border border-black bg-amber-400 px-5 py-2 text-sm font-bold hover:bg-amber-300">Buka Ruang Mengajar</a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border-2 border-dashed border-[#b8d7de] bg-white/80 px-5 py-12 text-center">
                            <h3 class="font-bold">Belum ada kelas untuk diajar</h3>
                            <p class="mt-1 text-sm text-slate-500">Kelas aktif milik tutor akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</body>
</html>
