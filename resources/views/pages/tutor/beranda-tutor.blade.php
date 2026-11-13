<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Beranda Tutor - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    @php
        $fullName = $tutor->user?->full_name ?? 'Tutor';
        $initials = collect(explode(' ', trim($fullName)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
        $hasTeachingData = $nearestSession || $activeStudentCount > 0 || $activeClassCount > 0 || $completedSessionCount > 0;
    @endphp

    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="flex min-h-screen min-w-0 flex-col px-5 sm:px-8 lg:px-10 flex-1">
            <x-tutor.topbar title="Beranda Tutor" />
            <x-tutor.mobile-nav />

            <div class="flex-1 space-y-6 pb-8">
                @if (session('status'))
                    <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-semibold text-green-800">{{ session('status') }}</div>
                @endif

                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6" aria-labelledby="level-request-title">
                    <h2 id="level-request-title" class="text-lg font-extrabold text-slate-900">Ajukan kenaikan jenjang mengajar</h2>
                    <p class="mt-1 text-sm text-slate-600">Pilih jenjang yang ingin diajukan dan lampirkan sertifikat dalam format PDF. Admin akan meninjau pengajuan Anda.</p>
                    <form method="POST" action="{{ route('tutor.level-requests.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                        @csrf
                        <label class="text-sm font-semibold text-slate-700">Jenjang tujuan
                            <select name="requested_level" required class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5">
                                <option value="">Pilih jenjang</option>
                                @foreach (['SD', 'SMP', 'SMA', 'UMUM'] as $level)
                                    <option value="{{ $level }}" @selected(old('requested_level') === $level)>{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('requested_level')<span class="mt-1 block text-xs text-rose-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Sertifikat PDF (maks. 10 MB)
                            <input type="file" name="certificate_file" accept="application/pdf,.pdf" required class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm">
                            @error('certificate_file')<span class="mt-1 block text-xs text-rose-700">{{ $message }}</span>@enderror
                        </label>
                        <button type="submit" class="rounded-xl border-2 border-slate-900 bg-amber-400 px-5 py-2.5 text-sm font-extrabold text-slate-900 hover:bg-amber-300">Kirim pengajuan</button>
                    </form>
                </section>

                <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-8" aria-labelledby="welcome-title">
                    <div class="absolute -right-4 bottom-0 h-28 w-28 rounded-full bg-cyan-50"></div>
                    <div class="relative flex flex-wrap items-center justify-between gap-5">
                        <div class="max-w-3xl">
                            <h1 id="welcome-title" class="text-xl font-extrabold tracking-tight text-slate-900 md:text-2xl">Halo, Semangat Mengajar, {{ $fullName }}! <span aria-hidden="true">&#128075;</span></h1>
                            <p class="mt-2 text-sm text-slate-600">@if ($todaySessions->isNotEmpty()) Ada <a href="{{ route('tutor.class-details') }}" class="font-semibold text-[#102d3f] underline">{{ $todaySessions->count() }} sesi bimbingan terjadwal hari ini</a>. @endif Pastikan persiapan modul dan kehadiran tepat waktu untuk menjaga kepuasan siswa.</p>
                        </div>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-slate-800"><span aria-hidden="true">&#11088;</span> &nbsp;Belum ada ulasan</span>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-4 md:grid-cols-3" aria-label="Akses cepat">
                    @if (Route::has('tutor.availability'))
                        <a href="{{ route('tutor.availability') }}" class="group flex min-h-[82px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xl text-amber-700" aria-hidden="true">&#128197;</span><span><b class="block text-sm font-extrabold text-slate-900">Atur Ketersediaan</b><small class="text-xs text-slate-500">Kelola jadwal agar tidak berbenturan</small></span></a>
                    @else
                        <div aria-disabled="true" class="flex min-h-[82px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xl text-amber-700" aria-hidden="true">&#128197;</span><span><b class="block text-sm font-extrabold text-slate-900">Atur Ketersediaan</b><small class="text-xs text-slate-500">Halaman belum tersedia</small></span></div>
                    @endif
                    <a href="{{ route('tutor.classes.create') }}" class="group flex min-h-[82px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-cyan-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-xl text-cyan-700" aria-hidden="true">&#43;</span><span><b class="block text-sm font-extrabold text-slate-900">Buat Program Kelas</b><small class="text-xs text-slate-500">Mulai menyusun kelas bimbingan</small></span></a>
                    @if (Route::has('tutor.studio'))
                        <a href="{{ route('tutor.studio') }}" class="group flex min-h-[82px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-cyan-50"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-xl text-cyan-700" aria-hidden="true">&#128218;</span><span><b class="block text-sm font-extrabold text-slate-900">Studio Materi &amp; Kuis</b><small class="text-xs text-slate-500">Susun modul dan soal pembelajaran</small></span></a>
                    @else
                        <div aria-disabled="true" class="flex min-h-[82px] items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 shadow-sm"><span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-xl text-cyan-700" aria-hidden="true">&#128218;</span><span><b class="block text-sm font-extrabold text-slate-900">Studio Materi &amp; Kuis</b><small class="text-xs text-slate-500">Halaman belum tersedia</small></span></div>
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
                    <section class="space-y-5 rounded-3xl border-2 border-amber-300 bg-amber-50/40 p-6" aria-labelledby="next-session-title">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-3">
                            <span class="rounded-full border-2 border-slate-900 bg-amber-400 px-4 py-1 text-xs font-black uppercase">Sesi mengajar terdekat · {{ $nearestSession->starts_at->isToday() ? 'Hari ini' : $nearestSession->starts_at->translatedFormat('l, j F') }}</span>
                            <span class="rounded-full border-2 border-slate-900 bg-white px-3 py-2 font-mono text-xs font-bold">Mulai dalam: <span data-session-countdown data-target="{{ $countdownTarget->toIso8601String() }}">{{ $nearestSession->starts_at->isFuture() ? 'Menunggu jadwal' : 'Sesi berlangsung' }}</span></span>
                        </div>
                        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
                            <div class="lg:col-span-2">
                                <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500"><span class="rounded border border-cyan-500 px-2 py-1 text-cyan-700">PERTEMUAN {{ $nearestSession->session_number ?? '—' }}{{ $nearestSession->total_sessions ? ' DARI '.$nearestSession->total_sessions.' SESI' : '' }}</span><span>• ID Sesi: {{ $nearestSession->session_id }}</span></div>
                                <h2 id="next-session-title" class="mt-2 text-xl font-extrabold sm:text-2xl">{{ $nearestSession->title }}{{ $nearestSession->session_number ? ' (Pertemuan '.$nearestSession->session_number.($nearestSession->total_sessions ? '/'.$nearestSession->total_sessions : '').')' : '' }}</h2>
                                <p class="mt-1 text-sm text-slate-600">Metode: <b>{{ $nearestSession->learning_method_label }}</b>@if ($nearestSession->squad_name) • Squad {{ $nearestSession->squad_name }}@elseif ($nearestSession->student_name) • Siswa: {{ $nearestSession->student_name }}@endif @if ($expectedStudents) ({{ $expectedStudents }} Siswa Terdaftar)@endif</p>
                                <div class="mt-4 space-y-2 rounded-2xl border-2 border-[#102d3f] bg-white p-4 text-xs shadow-[2px_3px_0_#102d3f]">
                                    <p>▦ &nbsp;<b>{{ $nearestSession->starts_at->translatedFormat('l, j F') }}, {{ $nearestSession->starts_at->format('H:i') }}–{{ $nearestSession->ends_at->format('H:i') }} WITA</b> ({{ $nearestSession->duration_minutes ?: $nearestSession->starts_at->diffInMinutes($nearestSession->ends_at) }} Menit)</p>
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
                            <div class="space-y-3">
                                @if (! $nearestSession->is_clocked_in)
                                    <button type="button" data-clock-in data-endpoint="{{ route('api.classroom.attendance.clock-in') }}" data-session="{{ $nearestSession->session_id }}" class="w-full rounded-2xl border-2 border-slate-900 bg-emerald-500 px-5 py-3.5 text-xs font-black text-white transition hover:bg-emerald-600">Masuk Teaching Cockpit (Clock-In) →</button>
                                @else
                                    <span class="rounded-2xl border-2 border-[#102d3f] bg-emerald-100 px-4 py-4 text-center text-sm font-extrabold text-emerald-800">Sesi sedang berlangsung</span>
                                @endif
                                @if ($nearestSession->learning_method === 'online_zoom' && $nearestSession->zoom_meeting_url)
                                    <a href="{{ $nearestSession->zoom_meeting_url }}" target="_blank" rel="noopener noreferrer" class="block rounded-2xl border-2 border-slate-900 bg-white px-5 py-3 text-center text-xs font-bold">Buka Ruang Zoom ↗</a>
                                @elseif ($nearestSession->maps_url)
                                    <a href="{{ $nearestSession->maps_url }}" target="_blank" rel="noopener noreferrer" class="block rounded-2xl border-2 border-slate-900 bg-white px-5 py-3 text-center text-xs font-bold">Buka Rute Google Maps ↗</a>
                                @endif
                            </div>
                        </div>
                    </section>
                @else
                    <section class="rounded-3xl border-2 border-amber-300 bg-amber-50/40 p-6"><span class="rounded-full border-2 border-slate-900 bg-amber-400 px-4 py-1 text-xs font-black uppercase">Sesi mengajar terdekat</span><h2 class="mt-4 text-xl font-extrabold text-slate-900">Belum ada sesi terjadwal</h2><p class="mt-1 text-sm text-slate-600">Sesi yang sudah dibayar dan dijadwalkan akan muncul di sini.</p></section>
                @endif

                    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan tutor">
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Saldo Dompet</p><p class="mt-2 font-mono text-2xl font-extrabold text-emerald-700">Rp {{ number_format($walletBalance, 0, ',', '.') }}</p><small class="text-xs text-slate-500">Saldo tercatat di akun tutor</small></article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Total Jam Terbang</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $totalTeachingHours }} Jam</p><small class="text-xs text-slate-500">{{ $completedSessionCount }} sesi tercatat</small></article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Kelas Bimbingan Aktif</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $activeClassCount }} Program</p><small class="text-xs text-slate-500">{{ $activeStudentCount }} pembelajar terdaftar</small></article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Slot Waktu Tersedia</p><p class="mt-2 font-mono text-2xl font-extrabold">{{ $availableSlotCount }} Slot</p><small class="text-xs text-slate-500">Jadwal yang masih dapat dipilih</small></article>
                    </section>

                    <section class="rounded-3xl border-2 border-emerald-300 bg-emerald-50/60 p-6" aria-labelledby="agenda-title">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 pb-3">
                            <div><h2 id="agenda-title" class="text-lg font-extrabold">▦ Agenda Jadwal 7 Hari Mendatang</h2><p class="mt-1 text-xs text-slate-500">Daftar sesi bimbingan terjadwal pada kelas tutor.</p></div>
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
