<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sesi Mengajar - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f8fa] font-sans text-[#102d3f] antialiased">
    @php
        $tutorName = $tutor->user?->full_name ?? 'Tutor';
        $clockInAt = $session?->absensi_masuk_guru ?? $session?->teacher_clock_in;
        $clockOutAt = $session?->absensi_keluar_guru ?? $session?->teacher_clock_out;
        $sessionIsLive = $session && ($session->session_status === 'in_progress' || $session->status === 'in_progress' || $clockInAt);
        $clockInValue = $clockInAt?->toIso8601String();
        $startsAt = $session?->scheduled_at?->toIso8601String();
        $minutes = (int) ($session?->duration_minutes ?? 90);
        $dayLabels = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        $slotDay = $slot ? ($dayLabels[$slot->day_of_week] ?? $slot->day_of_week) : $session?->scheduled_at?->translatedFormat('l');
        $firstParticipant = $participants->first();
        $student = $firstParticipant['student'] ?? null;
        $guardianName = $student?->parentProfile?->user?->full_name ?? $student?->parent_name;
        $guardianPhone = $student?->parentProfile?->phone_number ?? $student?->parent_phone;
        $priorNotes = $session?->evaluationScores?->first()?->teacher_notes;
    @endphp
    <div class="min-h-screen lg:grid lg:grid-cols-[286px_minmax(0,1fr)]">
        <aside class="flex flex-col border-r border-[#102d3f] bg-white px-4 py-5 lg:sticky lg:top-0 lg:h-screen">
            <div class="flex items-start justify-between border-b border-slate-200 pb-4"><a href="{{ route('tutor.dashboard') }}" class="flex items-center gap-2.5"><span class="flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-[#102d3f] bg-amber-300"></span><span class="leading-tight"><b class="block text-lg">Kawal<span class="text-cyan-600">Belajar</span></b><small class="rounded bg-cyan-100 px-2 py-0.5 text-[9px] font-extrabold uppercase">Portal Tutor</small></span></a><span class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#102d3f]">&#9664;</span></div>
            <nav aria-label="Menu tutor" class="mt-4 space-y-1.5 text-[13px] font-semibold"><a href="{{ route('tutor.dashboard') }}" class="flex items-center justify-between rounded-2xl px-3.5 py-3 hover:bg-slate-50"><span>Beranda Tutor</span><span class="h-2 w-2 rounded-full bg-emerald-500"></span></a><a href="{{ route('tutor.classes') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">Manajemen Kelas</a><a href="{{ route('tutor.class-details') }}" aria-current="page" class="block rounded-2xl bg-yellow-100 px-3.5 py-3">Sesi Mengajar</a><a href="{{ route('tutor.availability') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">Master Availability</a></nav>
            <div class="mt-auto border-t border-slate-200 pt-3"><div class="flex items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-2.5 shadow-[2px_3px_0_#102d3f]"><span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#102d3f] bg-cyan-100 font-extrabold">{{ mb_strtoupper(collect(explode(' ', $tutorName))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('')) }}</span><span><b class="block text-xs">{{ $tutorName }}</b><small class="text-[10px] text-slate-500">{{ $tutor->teacher_level ?? $tutor->focus_subject ?? 'Tutor' }}</small></span></div></div>
        </aside>

        <main class="min-w-0 px-5 py-5 sm:px-8 lg:px-10">
            <header class="mb-6 text-xs text-slate-500 sm:text-sm"><a href="{{ route('tutor.dashboard') }}">Portal Tutor</a><span class="px-2">&rsaquo;</span><b class="text-[#102d3f]">Sesi Mengajar</b></header>
            @if (session('status'))<div role="status" class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div role="alert" class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

            @if (!$session)
                <section class="rounded-2xl border-2 border-slate-300 bg-white px-6 py-16 text-center"><h1 class="text-xl font-extrabold">Belum ada sesi mengajar berikutnya</h1><p class="mt-2 text-sm text-slate-600">Halaman ini akan menampilkan sesi terdekat setelah ada siswa terdaftar dan jadwal sesi terbentuk.</p><a href="{{ route('tutor.classes') }}" class="mt-5 inline-flex rounded-xl bg-teal-700 px-4 py-3 text-sm font-bold text-white">Lihat Manajemen Kelas</a></section>
            @else
                <section class="rounded-[26px] border-2 border-slate-300 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-5"><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2 text-[10px] font-bold"><span class="rounded-full bg-sky-100 px-3 py-1 uppercase">{{ $sessionIsLive ? 'Sesi Aktif' : 'Sesi Terdekat' }} &bull; Pertemuan {{ $session->session_number ?? 1 }} dari {{ $session->order?->session_package_count ?? $session->order?->session_package ?? 1 }}</span><span class="rounded-full border border-slate-300 bg-slate-50 px-3 py-1 font-mono">ID KELAS: {{ strtoupper(substr(str_replace('-', '', $package?->package_id ?? ''), 0, 12)) }}</span></div><h1 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $package?->package_name ?? 'Sesi Mengajar' }}</h1><div class="mt-4 grid gap-2 sm:grid-cols-3"><article class="rounded-xl border border-slate-200 bg-slate-50 p-3"><small class="block text-[10px] font-bold uppercase text-slate-400">Format Sesi</small><b class="mt-1 block text-xs">{{ ($session->learning_method ?? $session->order?->learning_method) === 'online_zoom' ? 'Online Zoom' : 'Home Visit' }}</b></article><article class="rounded-xl border border-slate-200 bg-slate-50 p-3"><small class="block text-[10px] font-bold uppercase text-slate-400">Jadwal Rutin</small><b class="mt-1 block text-xs">{{ $slotDay }}, {{ $slot ? substr($slot->start_time, 0, 5).' - '.substr($slot->end_time, 0, 5) : $session->scheduled_at?->format('H:i') }} WITA</b><small class="text-[10px] text-slate-500">{{ $session->scheduled_at?->translatedFormat('l, j F Y') }}</small></article><article class="rounded-xl border border-slate-200 bg-slate-50 p-3"><small class="block text-[10px] font-bold uppercase text-slate-400">Peserta Sesi</small><b class="mt-1 block text-xs">{{ $registeredCount }} Siswa</b></article></div></div><div class="min-w-[220px] rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-4 text-center"><small class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Status Sesi Saat Ini</small><div class="mt-2 rounded-full border-2 border-[#102d3f] bg-amber-100 px-3 py-2 text-xs font-extrabold text-amber-900"><span class="mr-1 text-amber-500">&#9679;</span>{{ $sessionIsLive ? 'SEDANG BERLANGSUNG' : 'MENUNGGU DIMULAI' }}</div><p class="mt-3 font-mono text-[10px] text-slate-500">Durasi: <span id="session-timer" data-clock-in="{{ $clockInValue }}" data-starts-at="{{ $startsAt }}" data-duration="{{ $minutes }}">{{ $clockInAt ? '00:00:00' : 'Belum dimulai' }}</span> / {{ $minutes }}m</p></div></div>
                </section>

                @if (($session->learning_method ?? $session->order?->learning_method) === 'home_visit')
                    <section class="mt-5 grid gap-4 rounded-2xl border border-amber-300 bg-[#fffdf5] p-5 sm:grid-cols-[minmax(0,1fr)_225px] sm:items-center"><div><div class="flex flex-wrap items-center gap-2"><span class="rounded-lg border border-[#102d3f] bg-amber-400 px-3 py-1 text-[10px] font-extrabold uppercase">Lokasi Siswa</span><b class="text-xs">{{ $student?->user?->full_name ?? 'Siswa' }}@if ($guardianPhone) ({{ $guardianPhone }})@endif</b></div><p class="mt-2 text-sm font-bold">{{ $address ?? 'Alamat siswa belum tersedia' }}</p>@if ($landmark)<p class="mt-2 rounded-lg border border-[#102d3f] bg-white p-3 text-xs"><b class="text-teal-600">Patokan:</b> {{ $landmark }}</p>@endif</div><div class="grid gap-2">@if ($mapsUrl)<a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border-2 border-[#102d3f] bg-cyan-600 px-4 py-3 text-center text-xs font-bold text-white shadow-[2px_3px_0_#102d3f]">Buka Navigasi Google Maps</a>@endif<button type="button" id="copy-address" data-address="{{ trim(($address ?? '').($landmark ? ' | Patokan: '.$landmark : '')) }}" class="rounded-xl border-2 border-[#102d3f] bg-white px-4 py-3 text-xs font-bold shadow-[2px_3px_0_#102d3f]">Salin Alamat &amp; Patokan</button></div></section>
                @else
                    <section class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-cyan-300 bg-cyan-50 p-5"><div><b class="text-sm">Ruang Online</b><p class="mt-1 text-xs text-slate-600">Tautan Zoom sesi akan tersedia setelah disiapkan.</p></div>@if ($session->zoom_meeting_url)<a href="{{ $session->zoom_meeting_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-emerald-600 px-5 py-3 text-xs font-bold text-white">Buka Ruang Zoom</a>@else<button disabled class="cursor-not-allowed rounded-xl bg-slate-200 px-5 py-3 text-xs font-bold text-slate-500">Tautan Zoom belum tersedia</button>@endif</section>
                @endif

                <section class="mt-5 rounded-2xl bg-white p-5 shadow-sm"><div class="border-b border-slate-200 pb-3"><h2 class="font-extrabold">Presensi Mengajar Real-Time (Stempel Waktu Digital)</h2><p class="text-xs text-slate-500">Lokasi perangkat dicatat saat tutor melakukan clock-in dan clock-out.</p></div><div class="mt-4 grid gap-3 md:grid-cols-2"><article class="rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-4"><div class="flex items-center justify-between text-[10px] font-extrabold uppercase text-slate-500"><span>Tahap 1: Kedatangan</span><span class="rounded bg-emerald-100 px-2 py-1 text-emerald-800">{{ $clockInAt ? 'Tercatat' : 'Menunggu' }}</span></div><form data-clock-form action="{{ route('api.classroom.attendance.clock-in') }}" method="POST" class="mt-3">@csrf<input type="hidden" name="session_id" value="{{ $session->session_id }}"><button type="submit" @disabled($clockInAt) class="w-full rounded-xl border-2 border-[#102d3f] bg-emerald-600 px-4 py-3 text-xs font-extrabold text-white shadow-[2px_3px_0_#102d3f] disabled:cursor-not-allowed disabled:bg-slate-300">Mulai Kelas (Clock-In)</button></form><div class="mt-3 flex justify-between rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs"><span>Stempel Masuk</span><b class="font-mono">{{ $clockInAt ? $clockInAt->timezone(config('app.timezone'))->format('H:i:s').' WITA' : '--:--:-- WITA' }}</b></div></article><article class="rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-4"><div class="flex items-center justify-between text-[10px] font-extrabold uppercase text-slate-500"><span>Tahap 2: Kepulangan</span><span class="rounded bg-amber-100 px-2 py-1 text-amber-800">{{ $clockOutAt ? 'Tercatat' : 'Menunggu selesai' }}</span></div><form data-clock-form action="{{ route('api.classroom.attendance.clock-out') }}" method="POST" class="mt-3">@csrf<input type="hidden" name="session_id" value="{{ $session->session_id }}"><button type="submit" @disabled(!$clockInAt || $clockOutAt) class="w-full rounded-xl border-2 border-[#102d3f] bg-rose-600 px-4 py-3 text-xs font-extrabold text-white shadow-[2px_3px_0_#102d3f] disabled:cursor-not-allowed disabled:bg-slate-300">Selesai Kelas (Clock-Out)</button></form><div class="mt-3 flex justify-between rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs"><span>Stempel Keluar</span><b class="font-mono">{{ $clockOutAt ? $clockOutAt->timezone(config('app.timezone'))->format('H:i:s').' WITA' : '--:--:-- WITA' }}</b></div></article></div><p id="clock-message" role="status" class="mt-3 hidden rounded-lg bg-rose-50 p-3 text-xs text-rose-700"></p></section>

                <form method="POST" action="{{ route('tutor.sessions.evaluation.save', $session->session_id) }}" class="mt-5 rounded-[24px] border-l-4 border-amber-400 bg-white p-5 shadow-sm sm:p-7">@csrf<div class="border-b border-slate-200 pb-3"><h2 class="text-lg font-extrabold">Absensi Siswa &amp; Ujian Diagnostik (Pre-Test vs Post-Test)</h2><p class="text-xs text-slate-500">Catat kehadiran dan nilai Post-Test untuk menyusun rapor digital siswa.</p></div><div class="mt-3 overflow-x-auto"><table class="w-full min-w-[680px] text-left text-xs"><thead class="border-b-2 border-[#102d3f] bg-slate-50 text-[10px] font-extrabold uppercase text-slate-500"><tr><th class="px-3 py-3">No</th><th class="px-3 py-3">Nama Siswa</th><th class="px-3 py-3">Status Presensi</th><th class="px-3 py-3 text-center">Skor Pre-Test</th><th class="px-3 py-3 text-center">Skor Post-Test</th><th class="px-3 py-3 text-center">Perkembangan</th></tr></thead><tbody class="divide-y divide-slate-100">
                    @forelse ($participants as $index => $row)
                        @php
                            $studentRow = $row['student'];
                            $pre = $row['pretest'];
                            $post = $row['posttest'];
                            $progress = $row['progress'] ?? ($pre && $post !== null ? round((($post - $pre) / $pre) * 100, 1) : null);
                        @endphp
                        <tr><td class="px-3 py-4 text-slate-400">{{ $index + 1 }}</td><td class="px-3 py-4"><b class="block">{{ $studentRow->user?->full_name ?? 'Siswa' }}</b><small class="text-slate-400">{{ $guardianName ? 'Wali: '.$guardianName : $row['role'] }}</small></td><td class="px-3 py-4"><select name="students[{{ $studentRow->student_id }}][attendance]" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs"><option value="">Pilih status</option><option value="present" @selected($row['attendance'] === 'present')>Hadir</option><option value="permission" @selected($row['attendance'] === 'permission')>Izin</option><option value="absent" @selected($row['attendance'] === 'absent')>Alfa</option></select></td><td class="px-3 py-4 text-center"><span class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono font-bold">{{ $pre ?? '-' }}</span><small class="mt-1 block text-slate-400">/100</small></td><td class="px-3 py-4 text-center"><input type="number" min="0" max="100" name="students[{{ $studentRow->student_id }}][posttest_score]" value="{{ old('students.'.$studentRow->student_id.'.posttest_score', $post) }}" class="w-16 rounded-lg border border-slate-300 px-2 py-2 text-center font-mono font-bold" aria-label="Skor post-test {{ $studentRow->user?->full_name }}"><small class="mt-1 block text-slate-400">/100</small></td><td class="px-3 py-4 text-center">@if ($progress !== null)<span class="rounded-lg bg-emerald-100 px-2 py-2 font-bold text-emerald-800">{{ $progress > 0 ? '+' : '' }}{{ $progress }}% @if ($progress >= 0)(Meningkat)@else(Menurun)@endif</span>@else<span class="text-slate-400">Menunggu nilai</span>@endif</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-10 text-center text-slate-500">Data siswa belum tersedia untuk sesi ini.</td></tr>
                    @endforelse
                </tbody></table></div><label class="mt-4 block text-xs font-bold">Catatan Evaluasi Pembelajaran Sesi Ini (Tampil di Rapor Orang Tua): <span class="text-rose-600">*</span><textarea name="teacher_notes" required maxlength="1000" rows="3" class="mt-2 w-full rounded-xl border-2 border-[#102d3f] bg-slate-50 p-3 text-sm font-normal" placeholder="Tulis perkembangan belajar siswa pada sesi ini...">{{ old('teacher_notes', $priorNotes) }}</textarea></label>
                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border-2 border-[#102d3f] p-4"><p class="max-w-xl text-xs text-slate-600"><b class="text-[#102d3f]">Protokol Validasi:</b> menyimpan evaluasi akan menerbitkan pembaruan rapor digital dan menutup sesi mengajar.</p><button type="submit" @disabled($participants->isEmpty()) class="rounded-xl border-2 border-[#102d3f] bg-amber-400 px-5 py-3 text-sm font-extrabold shadow-[2px_3px_0_#102d3f] hover:bg-amber-300 disabled:cursor-not-allowed disabled:bg-slate-200">Simpan Evaluasi &amp; Tutup Sesi Mengajar</button></div>
                </form>
            @endif
            <footer class="mt-6 border-t border-slate-200 py-5 text-[11px] text-slate-500"><b class="text-[#102d3f]">KawalBelajar</b> &nbsp; {{ now()->year }} Yayasan Kawal Pendidikan Nusantara. Semua hak cipta dilindungi.</footer>
        </main>
    </div>
    <script>
        (() => {
            const timer = document.getElementById('session-timer');
            if (timer) {
                const start = timer.dataset.clockIn ? new Date(timer.dataset.clockIn).getTime() : null;
                const begins = timer.dataset.startsAt ? new Date(timer.dataset.startsAt).getTime() : null;
                const render = () => {
                    const now = Date.now();
                    if (start) {
                        const seconds = Math.max(0, Math.floor((now - start) / 1000));
                        timer.textContent = [Math.floor(seconds / 3600), Math.floor((seconds % 3600) / 60), seconds % 60].map(value => String(value).padStart(2, '0')).join(':');
                    } else if (begins && begins > now) {
                        const seconds = Math.floor((begins - now) / 1000);
                        timer.textContent = `Mulai dalam ${Math.floor(seconds / 3600)}j ${Math.floor((seconds % 3600) / 60)}m`;
                    } else {
                        timer.textContent = 'Siap dimulai';
                    }
                };
                render();
                window.setInterval(render, 1000);
            }

            document.getElementById('copy-address')?.addEventListener('click', async event => {
                const button = event.currentTarget;
                try {
                    await navigator.clipboard.writeText(button.dataset.address || '');
                    button.textContent = 'Alamat Tersalin';
                } catch {
                    button.textContent = button.dataset.address || 'Alamat tidak tersedia';
                }
            });

            document.querySelectorAll('[data-clock-form]').forEach(form => form.addEventListener('submit', event => {
                event.preventDefault();
                const message = document.getElementById('clock-message');
                const submit = form.querySelector('button[type="submit"]');
                const send = position => {
                    const data = new FormData(form);
                    if (position) {
                        data.set('latitude', position.coords.latitude);
                        data.set('longitude', position.coords.longitude);
                    }
                    submit.disabled = true;
                    fetch(form.action, {method: 'POST', body: data, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}})
                        .then(async response => {
                            const body = await response.json();
                            if (!response.ok) throw new Error(body.message || 'Presensi gagal dicatat.');
                            window.location.reload();
                        })
                        .catch(error => {
                            message.textContent = error.message;
                            message.classList.remove('hidden');
                            submit.disabled = false;
                        });
                };
                if (!navigator.geolocation) {
                    const message = document.getElementById('clock-message');
                    message.textContent = 'Browser ini tidak mendukung lokasi perangkat.';
                    message.classList.remove('hidden');
                    return;
                }
                navigator.geolocation.getCurrentPosition(send, error => {
                    const message = document.getElementById('clock-message');
                    message.textContent = error.code === 1 ? 'Izinkan akses lokasi untuk mencatat presensi.' : 'Lokasi tidak dapat dibaca. Coba lagi di area dengan sinyal GPS yang baik.';
                    message.classList.remove('hidden');
                }, {enableHighAccuracy: true, timeout: 15000, maximumAge: 0});
            }));
        })();
    </script>
</body>
</html>
