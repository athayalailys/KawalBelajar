<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ruang Mengajar - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="min-w-0 flex-1">
            <x-tutor.topbar title="Ruang Mengajar" />
            <x-tutor.mobile-nav />

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
