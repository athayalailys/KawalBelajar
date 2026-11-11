<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Tutor - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f8fa] font-sans text-[#102d3f] antialiased">
    @php
        $fullName = $tutor->user?->full_name ?? 'Tutor';
        $initials = collect(explode(' ', trim($fullName)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
        $hasTeachingData = $nearestSession || $activeStudentCount > 0 || $activeClassCount > 0 || $completedSessionCount > 0;
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[286px_minmax(0,1fr)]">
        <aside class="flex flex-col border-r border-slate-200 bg-white px-4 py-5 lg:sticky lg:top-0 lg:h-screen">
            <div class="flex items-start justify-between border-b border-slate-200 pb-4">
                <a href="{{ route('tutor.dashboard') }}" class="flex items-center gap-2.5" aria-label="KawalBelajar Portal Tutor">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-[#102d3f] bg-amber-300 text-xl" aria-hidden="true">🎓</span>
                    <span class="leading-tight"><span class="block text-lg font-extrabold tracking-tight">Kawal<span class="text-cyan-600">Belajar</span></span><span class="mt-1 inline-flex rounded bg-cyan-100 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wide">Portal Tutor</span></span>
                </a>
                <button type="button" aria-label="Menu navigasi" class="hidden h-9 w-9 items-center justify-center rounded-xl border border-[#102d3f] lg:flex">◀</button>
            </div>

            <nav aria-label="Menu tutor" class="mt-4 space-y-1.5 text-[13px] font-semibold">
                <a href="{{ route('tutor.dashboard') }}" aria-current="page" class="flex items-center justify-between rounded-2xl bg-yellow-100 px-3.5 py-3 text-[#102d3f]"><span>🏠 &nbsp;Beranda Tutor</span><span class="h-2 w-2 rounded-full bg-emerald-500"></span></a>
                <a href="{{ route('tutor.classes') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">👥 &nbsp;Manajemen Kelas dan Siswa</a>
                <a href="{{ route('tutor.class-details') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">⏱️ &nbsp;Sesi Mengajar / Cockpit</a>
                @if (Route::has('tutor.availability'))
                    <a href="{{ route('tutor.availability') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">🗓️ &nbsp;Master Availability</a>
                @else
                    <span class="block cursor-not-allowed rounded-2xl px-3.5 py-3 text-slate-400" title="Halaman ketersediaan belum tersedia">🗓️ &nbsp;Master Availability</span>
                @endif
                @if (Route::has('tutor.earnings'))
                    <a href="{{ route('tutor.earnings') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">💰 &nbsp;Rekap Gaji &amp; Payroll</a>
                @else
                    <span class="block cursor-not-allowed rounded-2xl px-3.5 py-3 text-slate-400" title="Halaman payroll belum tersedia">💰 &nbsp;Rekap Gaji &amp; Payroll</span>
                @endif
            </nav>

            <div class="mt-auto border-t border-slate-200 pt-3">
                <div class="flex items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-2.5 shadow-[2px_3px_0_#102d3f]">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#102d3f] bg-cyan-100 text-sm font-extrabold">{{ mb_strtoupper($initials) }}</span>
                    <span class="min-w-0 flex-1"><span class="block truncate text-xs font-extrabold">{{ $fullName }}</span><span class="block truncate text-[10px] text-slate-500">{{ $tutor->teacher_level ?? $tutor->focus_subject ?? 'Tutor' }}</span></span>
                    <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" title="Akun aktif"></span>
                </div>
            </div>
        </aside>

        <main class="flex min-h-screen min-w-0 flex-col px-5 sm:px-8 lg:px-10">
            <header class="flex items-center justify-between py-5 text-xs sm:text-sm">
                <div class="text-slate-500">Portal Tutor <span class="px-2">›</span><span class="font-bold text-[#102d3f]">Beranda &amp; Overview</span></div>
                <div class="flex items-center gap-4">
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs font-semibold hover:text-cyan-700">Keluar</button></form>
                    @endif
                    <button type="button" aria-label="Notifikasi" class="relative flex h-9 w-9 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-white text-lg">🔔<span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full border border-white bg-rose-500"></span></button>
                </div>
            </header>

            <div class="flex-1 space-y-6 pb-8">
                @if (session('status'))
                    <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-semibold text-green-800">{{ session('status') }}</div>
                @endif

                <section class="relative overflow-hidden rounded-[28px] border-2 border-sky-300 bg-white px-6 py-7 sm:px-8 sm:py-8" aria-labelledby="welcome-title">
                    <div class="absolute -right-4 bottom-0 h-28 w-28 rounded-full bg-cyan-50"></div>
                    <div class="relative flex flex-wrap items-center justify-between gap-5">
                        <div class="max-w-3xl">
                            <h1 id="welcome-title" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Halo, Semangat Mengajar, {{ $fullName }}! 👋</h1>
                            <p class="mt-2 text-sm text-slate-600">@if ($todaySessions->isNotEmpty()) Ada <a href="{{ route('tutor.class-details') }}" class="font-semibold text-[#102d3f] underline">{{ $todaySessions->count() }} sesi bimbingan terjadwal hari ini</a>. @endif Pastikan persiapan modul dan kehadiran tepat waktu untuk menjaga kepuasan siswa.</p>
                        </div>
                        <span class="rounded-xl border-2 border-[#102d3f] bg-yellow-100 px-4 py-2 text-xs font-semibold shadow-[2px_2px_0_#102d3f]">⭐ &nbsp;Belum Ada Ulasan</span>
                    </div>
                </section>

                <section class="grid gap-3 md:grid-cols-3" aria-label="Akses cepat">
                    @if (Route::has('tutor.availability'))
                        <a href="{{ route('tutor.availability') }}" class="flex min-h-[82px] items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-white px-4 shadow-[3px_4px_0_#102d3f] hover:bg-slate-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-amber-50 text-xl">🗓️</span><span><b class="block text-sm">Atur Master Availability</b><small class="text-[11px] text-slate-500">Kelola slot jam bebas bentrok</small></span></a>
                    @else
                        <div aria-disabled="true" class="flex min-h-[82px] items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-white px-4 shadow-[3px_4px_0_#102d3f]"><span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-amber-50 text-xl">🗓️</span><span><b class="block text-sm">Atur Master Availability</b><small class="text-[11px] text-slate-500">Halaman belum tersedia</small></span></div>
                    @endif
                    <a href="{{ route('tutor.classes.create') }}" class="flex min-h-[82px] items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-white px-4 shadow-[3px_4px_0_#102d3f] hover:bg-slate-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-cyan-50 text-xl">✧</span><span><b class="block text-sm">+ Buat Program Kelas</b><small class="text-[11px] text-slate-500">Mulai wizard program kelas</small></span></a>
                    @if (Route::has('tutor.studio'))
                        <a href="{{ route('tutor.studio') }}" class="flex min-h-[82px] items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-white px-4 shadow-[3px_4px_0_#102d3f] hover:bg-slate-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-cyan-100 text-xl">📚</span><span><b class="block text-sm">Studio Materi &amp; Kuis</b><small class="text-[11px] text-slate-500">Rakit modul dan soal</small></span></a>
                    @else
                        <div aria-disabled="true" class="flex min-h-[82px] items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-white px-4 shadow-[3px_4px_0_#102d3f]"><span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-cyan-100 text-xl">📚</span><span><b class="block text-sm">Studio Materi &amp; Kuis</b><small class="text-[11px] text-slate-500">Halaman belum tersedia</small></span></div>
                    @endif
                </section>

                @if ($nearestSession)
                    @php
                        $nextModule = $nearestSession->material;
                        $sessionEnd = $nearestSession->ends_at;
                        $countdownTarget = $nearestSession->starts_at->isFuture() ? $nearestSession->starts_at : $sessionEnd;
                        $submittedPretests = $nearestSession->evaluationScores()->whereNotNull('pretest_score')->count();
                        $expectedStudents = $nearestSession->squad_size ?: ($nearestSession->order?->student_id ? 1 : 0);
                    @endphp
                    <section class="rounded-[28px] border-l-[9px] border-amber-400 bg-[#fffdf4] p-5 sm:p-7" aria-labelledby="next-session-title">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                            <span class="rounded-full bg-amber-400 px-4 py-1 text-[11px] font-extrabold uppercase">📌 Sesi Mengajar Terdekat • {{ $nearestSession->starts_at->isToday() ? 'Hari Ini' : $nearestSession->starts_at->translatedFormat('l, j F') }}</span>
                            <span class="rounded-xl border-2 border-[#102d3f] bg-white px-3 py-2 font-mono text-xs font-bold">⏱️ <span data-session-countdown data-target="{{ $countdownTarget->toIso8601String() }}">{{ $nearestSession->starts_at->isFuture() ? 'Menunggu jadwal' : 'Sesi berlangsung' }}</span></span>
                        </div>
                        <div class="mt-3 grid gap-5 lg:grid-cols-[minmax(0,1fr)_265px]">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500"><span class="rounded border border-cyan-500 px-2 py-1 text-cyan-700">PERTEMUAN {{ $nearestSession->session_number ?? '—' }}{{ $nearestSession->total_sessions ? ' DARI '.$nearestSession->total_sessions.' SESI' : '' }}</span><span>• ID Sesi: {{ $nearestSession->session_id }}</span></div>
                                <h2 id="next-session-title" class="mt-2 text-xl font-extrabold sm:text-2xl">{{ $nearestSession->title }}{{ $nearestSession->session_number ? ' (Pertemuan '.$nearestSession->session_number.($nearestSession->total_sessions ? '/'.$nearestSession->total_sessions : '').')' : '' }}</h2>
                                <p class="mt-1 text-sm text-slate-600">Metode: <b>{{ $nearestSession->learning_method_label }}</b>@if ($nearestSession->squad_name) • Squad {{ $nearestSession->squad_name }}@elseif ($nearestSession->student_name) • Siswa: {{ $nearestSession->student_name }}@endif @if ($expectedStudents) ({{ $expectedStudents }} Siswa Terdaftar)@endif</p>
                                <div class="mt-4 space-y-2 rounded-2xl border-2 border-[#102d3f] bg-white p-4 text-xs shadow-[2px_3px_0_#102d3f]">
                                    <p>🗓️ &nbsp;<b>{{ $nearestSession->starts_at->translatedFormat('l, j F') }}, {{ $nearestSession->starts_at->format('H:i') }}–{{ $nearestSession->ends_at->format('H:i') }} WITA</b> ({{ $nearestSession->duration_minutes ?: $nearestSession->starts_at->diffInMinutes($nearestSession->ends_at) }} Menit)</p>
                                    @if ($nearestSession->location_address)<p>📍 &nbsp;{{ $nearestSession->location_address }}</p>@endif
                                    @if ($nearestSession->address_landmark)<p class="pl-6 italic text-slate-500">Patokan: {{ $nearestSession->address_landmark }}</p>@endif
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold">
                                    @if ($nextModule)
                                        <span class="rounded-full border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-emerald-800">✓ Modul {{ $nextModule->material_type === 'pdf' ? 'PDF' : 'materi' }} siap: {{ $nextModule->title }}</span>
                                    @else
                                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-slate-500">Modul sesi belum ditautkan</span>
                                    @endif
                                    <span class="rounded-full border border-cyan-300 bg-cyan-50 px-3 py-1.5 text-cyan-800">• Pre-Test: {{ $submittedPretests }}{{ $expectedStudents ? ' dari '.$expectedStudents : '' }} siswa sudah mengerjakan</span>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center gap-2">
                                @if (! $nearestSession->is_clocked_in)
                                    <button type="button" data-clock-in data-endpoint="{{ route('api.classroom.attendance.clock-in') }}" data-session="{{ $nearestSession->session_id }}" class="min-h-[74px] rounded-2xl border-2 border-[#102d3f] bg-emerald-500 px-4 py-3 text-sm font-extrabold text-white shadow-[3px_4px_0_#102d3f] hover:bg-emerald-600">Masuk Teaching Cockpit<br>(Clock-In) →</button>
                                @else
                                    <span class="rounded-2xl border-2 border-[#102d3f] bg-emerald-100 px-4 py-4 text-center text-sm font-extrabold text-emerald-800">Sesi sedang berlangsung</span>
                                @endif
                                @if ($nearestSession->learning_method === 'online_zoom' && $nearestSession->zoom_meeting_url)
                                    <a href="{{ $nearestSession->zoom_meeting_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border-2 border-[#102d3f] bg-cyan-100 px-4 py-3 text-center text-xs font-bold">Buka Ruang Zoom ↗</a>
                                @elseif ($nearestSession->maps_url)
                                    <a href="{{ $nearestSession->maps_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border-2 border-[#102d3f] bg-white px-4 py-3 text-center text-xs font-bold">📍 Buka Rute Google Maps ↗</a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif

                @if ($hasTeachingData)
                    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan tutor">
                        <article class="rounded-2xl border border-sky-300 bg-white p-5"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Saldo Dompet Sementara</p><p class="mt-2 font-mono text-2xl font-extrabold text-emerald-600">Rp {{ number_format($walletBalance, 0, ',', '.') }}</p><small class="text-[10px] text-slate-500">Saldo tercatat di akun tutor</small></article>
                        <article class="rounded-2xl border border-sky-300 bg-white p-5"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Total Jam Terbang</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $totalTeachingHours }} Jam</p><small class="text-[10px] text-cyan-700">{{ $completedSessionCount }} sesi tercatat</small></article>
                        <article class="rounded-2xl border border-sky-300 bg-white p-5"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Kelas Bimbingan Aktif</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $activeClassCount }} Program</p><small class="text-[10px] text-emerald-700">{{ $activeStudentCount }} siswa terdaftar</small></article>
                        <article class="rounded-2xl border border-sky-300 bg-white p-5"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Slot Master Kosong</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $availableSlotCount }} Slot Jam</p><small class="text-[10px] text-slate-500">Slot tersedia untuk kelas baru</small></article>
                    </section>

                    <section class="rounded-[28px] border-2 border-[#102d3f] bg-white p-5 sm:p-7" aria-labelledby="agenda-title">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-3">
                            <div><h2 id="agenda-title" class="text-lg font-extrabold">🗓️ Agenda Jadwal 7 Hari Mendatang</h2><p class="mt-1 text-xs text-slate-500">Daftar sesi bimbingan terjadwal pada kelas tutor.</p></div>
                            <span class="rounded-full border border-cyan-500 bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">{{ $upcomingSessions->count() }} Sesi Terjadwal</span>
                        </div>
                        <div class="divide-y divide-slate-100">
                            @forelse ($upcomingSessions as $session)
                                <article class="grid gap-3 py-4 sm:grid-cols-[52px_minmax(0,1fr)_auto] sm:items-center">
                                    <div class="flex h-11 w-11 flex-col items-center justify-center rounded-xl border-2 border-[#102d3f] bg-cyan-50 text-[9px] font-bold leading-tight"><span>{{ mb_strtoupper($session->starts_at->translatedFormat('D')) }}</span><span class="text-base">{{ $session->starts_at->format('d') }}</span></div>
                                    <div class="min-w-0"><h3 class="truncate text-sm font-extrabold">{{ $session->title }} <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold">{{ $session->order?->package?->grade_level ?? 'Kelas' }}</span></h3><p class="mt-1 text-[11px] font-semibold">{{ $session->starts_at->translatedFormat('l, j M Y') }} • {{ $session->starts_at->format('H:i') }}–{{ $session->ends_at->format('H:i') }} WITA <span class="px-1 text-slate-400">•</span> {{ $session->order?->package?->subject_name ?? $session->material?->title ?? 'Materi belum ditentukan' }}</p></div>
                                    <div class="flex items-center gap-2 text-[10px] font-bold"><span class="rounded-lg border border-[#102d3f] bg-cyan-100 px-2 py-1">{{ $session->learning_method_label }}</span><span class="rounded-lg border border-[#102d3f] px-2 py-1">{{ $session->squad_size ?: ($session->order?->student_id ? 1 : 0) }} Siswa</span></div>
                                </article>
                            @empty
                                <div class="py-7 text-center text-sm text-slate-500">Belum ada sesi mengajar dalam jadwal mendatang.</div>
                            @endforelse
                        </div>
                    </section>
                @endif
            </div>

            <footer class="border-t border-slate-200 py-5 text-[11px] text-slate-500"><b class="text-[#102d3f]">KawalBelajar</b> &nbsp;© {{ now()->year }} Yayasan Kawal Pendidikan Nusantara. Semua hak cipta dilindungi.</footer>
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-session-countdown]').forEach((element) => {
            const target = new Date(element.dataset.target).getTime();
            const render = () => {
                let remaining = Math.max(0, target - Date.now());
                const days = Math.floor(remaining / 86400000); remaining %= 86400000;
                const hours = Math.floor(remaining / 3600000); remaining %= 3600000;
                const minutes = Math.floor(remaining / 60000); remaining %= 60000;
                const seconds = Math.floor(remaining / 1000);
                element.textContent = `${days ? days + ' Hari ' : ''}${String(hours).padStart(2, '0')} Jam ${String(minutes).padStart(2, '0')} Menit ${String(seconds).padStart(2, '0')} Detik`;
            };
            render(); window.setInterval(render, 1000);
        });

        document.querySelector('[data-clock-in]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            button.disabled = true;
            button.textContent = 'Mencatat Clock-In…';
            const payload = { session_id: button.dataset.session };
            const send = async (coords = null) => {
                if (coords) { payload.latitude = coords.latitude; payload.longitude = coords.longitude; }
                const response = await fetch(button.dataset.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Clock-In gagal dicatat.');
                window.location.href = '{{ route('tutor.class-details') }}';
            };
            try {
                if (navigator.geolocation) navigator.geolocation.getCurrentPosition(({ coords }) => send(coords).catch(showError), () => send().catch(showError), { timeout: 5000 });
                else await send();
            } catch (error) { showError(error); }
            function showError(error) { window.alert(error.message); button.disabled = false; button.textContent = 'Masuk Teaching Cockpit (Clock-In) →'; }
        });
    </script>
</body>
</html>
