<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - {{ $package->package_name }} | KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f8fa] font-sans text-[#102d3f] antialiased">
    @php
        $tutorName = $teacher->user?->full_name ?? 'Tutor';
        $dayNames = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        $day = $slot ? ($dayNames[$slot->day_of_week] ?? $slot->day_of_week) : null;
        $method = match ($package->learning_method) { 'online_zoom' => 'Live Zoom', 'home_visit' => 'Home Visit', default => 'Moda belum ditentukan' };
    @endphp
    <div class="min-h-screen lg:grid lg:grid-cols-[286px_minmax(0,1fr)]">
        <aside class="flex flex-col border-r border-[#102d3f] bg-white px-4 py-5 lg:sticky lg:top-0 lg:h-screen">
            <div class="flex items-start justify-between border-b border-slate-200 pb-4">
                <a href="{{ route('tutor.dashboard') }}" class="flex items-center gap-2.5"><span class="flex h-10 w-10 items-center justify-center rounded-2xl border-2 border-[#102d3f] bg-amber-300 text-xl"></span><span class="leading-tight"><b class="block text-lg">Kawal<span class="text-cyan-600">Belajar</span></b><small class="rounded bg-cyan-100 px-2 py-0.5 text-[9px] font-extrabold uppercase">Portal Tutor</small></span></a>
                <a href="{{ route('tutor.classes') }}" aria-label="Kembali ke manajemen kelas" class="flex h-9 w-9 items-center justify-center rounded-xl border border-[#102d3f]">&#9664;</a>
            </div>
            <nav aria-label="Menu tutor" class="mt-4 space-y-1.5 text-[13px] font-semibold">
                <a href="{{ route('tutor.dashboard') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">Beranda Tutor</a>
                <a href="{{ route('tutor.classes') }}" aria-current="page" class="block rounded-2xl bg-yellow-100 px-3.5 py-3">Manajemen Kelas dan Siswa</a>
                <a href="{{ route('tutor.class-details') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">Sesi Mengajar / Cockpit</a>
                <a href="{{ route('tutor.availability') }}" class="block rounded-2xl px-3.5 py-3 hover:bg-slate-50">Master Availability</a>
            </nav>
            <div class="mt-auto border-t border-slate-200 pt-3"><div class="flex items-center gap-3 rounded-2xl border-2 border-[#102d3f] bg-slate-50 p-2.5 shadow-[2px_3px_0_#102d3f]"><span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[#102d3f] bg-cyan-100 font-extrabold">{{ mb_strtoupper(collect(explode(' ', $tutorName))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('')) }}</span><span><b class="block text-xs">{{ $tutorName }}</b><small class="text-[10px] text-slate-500">{{ $teacher->teacher_level ?? $teacher->focus_subject ?? 'Tutor' }}</small></span></div></div>
        </aside>

        <main class="min-w-0 px-5 py-5 sm:px-8 lg:px-10">
            <header class="mb-6 flex items-center justify-between text-xs sm:text-sm"><div class="text-slate-500"><a href="{{ route('tutor.dashboard') }}">Portal Tutor</a><span class="px-2">&rsaquo;</span><a href="{{ route('tutor.classes') }}">Manajemen Kelas</a><span class="px-2">&rsaquo;</span><b class="text-[#102d3f]">Kelola Siswa</b></div><span class="flex h-9 w-9 items-center justify-center rounded-xl border-2 border-[#102d3f] bg-white" aria-label="Notifikasi">&#9833;</span></header>

            @if (session('status'))<div role="status" class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div role="alert" class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex min-w-0 items-start gap-4"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-xl text-teal-700">&#9787;</span><div><p class="font-mono text-[10px] font-bold tracking-[.16em] text-slate-500">{{ strtoupper(substr(str_replace('-', '', $package->package_id), 0, 12)) }}</p><h1 class="mt-1 text-xl font-extrabold tracking-tight sm:text-2xl">{{ $package->package_name }}</h1><p class="mt-1 text-sm text-slate-600"><span class="font-semibold text-teal-700">{{ $method }}</span><span class="px-2">&bull;</span>{{ $day ? $day.' ('.substr($slot->start_time, 0, 5).' - '.substr($slot->end_time, 0, 5).' WITA)' : 'Jadwal belum dipilih' }}</p></div></div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"><p class="font-bold">{{ $occupiedSeats }} Siswa Terdaftar @if ($capacity) <span class="font-normal text-slate-500">/ {{ $capacity }} kursi</span>@endif</p><p class="mt-1 text-xs {{ $package->is_open && !$isFull ? 'text-emerald-700' : 'text-slate-500' }}">{{ $package->is_open && !$isFull ? 'Penerimaan dibuka' : ($isFull ? 'Kapasitas kelas penuh' : 'Penerimaan ditutup') }}</p></div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                    <div><p class="text-sm font-bold">Status Penerimaan &amp; Listing Publik</p><p class="mt-1 text-xs text-slate-600">Saat ditutup, kelas tidak ditampilkan pada katalog siswa.</p></div>
                    <form method="POST" action="{{ route('tutor.classes.listing', $package->package_id) }}" class="flex items-center gap-3">@csrf @method('PATCH')<input type="hidden" name="is_open" value="{{ $package->is_open ? 0 : 1 }}"><span class="text-xs font-bold">{{ $package->is_open ? 'Penerimaan DIBUKA' : 'Penerimaan DITUTUP' }}</span><button type="submit" @if (!$package->is_open && (!$package->is_published || !$package->is_active || !$module || !$package->questionBanks()->exists() || $isFull)) disabled title="Kelas harus aktif, memiliki modul dan soal, serta masih memiliki kapasitas" @endif class="relative h-8 w-14 rounded-full transition {{ $package->is_open ? 'bg-emerald-500' : 'bg-slate-300' }} disabled:cursor-not-allowed disabled:opacity-50" aria-label="{{ $package->is_open ? 'Tutup penerimaan siswa' : 'Buka penerimaan siswa' }}"><span class="absolute top-1 h-6 w-6 rounded-full bg-white shadow transition {{ $package->is_open ? 'left-7' : 'left-1' }}"></span></button></form>
                </div>

                <div class="mt-6 flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-lg font-extrabold">Roster Siswa Aktif <span class="rounded-full bg-slate-100 px-2.5 py-1 align-middle text-[10px] font-bold text-slate-600">{{ $students->count() }} Siswa Terdaftar</span></h2><p class="mt-1 text-sm text-slate-600">Pantau nilai diagnostik dan presensi siswa untuk kelas ini.</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('tutor.classes.roster.export', $package->package_id) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold hover:bg-slate-50">Unduh CSV</a><button type="button" disabled title="Broadcast WhatsApp belum tersedia" class="cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-bold text-slate-400">Broadcast WA</button><a href="{{ route('tutor.classroom.show', $package->package_id) }}" class="rounded-lg bg-teal-700 px-3 py-2 text-xs font-bold text-white hover:bg-teal-800">Kirim / Kelola Rapor</a></div></div>

                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full min-w-[920px] text-left text-xs">
                        <thead class="bg-slate-100 text-[10px] font-extrabold uppercase tracking-wide text-slate-600"><tr><th class="px-3 py-3">No</th><th class="px-3 py-3">Siswa &amp; Kontak Wali</th><th class="px-3 py-3">Peran Belajar</th><th class="px-3 py-3 text-center">Pre-Test</th><th class="px-3 py-3 text-center">Post-Test</th><th class="px-3 py-3 text-center">Progres Nilai</th><th class="px-3 py-3">Presensi Terakhir</th><th class="px-3 py-3 text-center">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($students as $index => $row)
                                <tr class="align-middle hover:bg-slate-50"><td class="px-3 py-4 text-slate-600">{{ $index + 1 }}</td><td class="px-3 py-4"><span class="flex items-start gap-3"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-600 font-bold text-white">{{ mb_strtoupper(mb_substr($row['name'], 0, 1)) }}</span><span><b class="block text-sm">{{ $row['name'] }}</b><span class="mt-1 block text-slate-600">Wali: {{ $row['guardian_name'] }}</span>@if ($row['guardian_phone'])<a class="mt-0.5 block text-slate-500 hover:text-teal-700" href="tel:{{ $row['guardian_phone'] }}">{{ $row['guardian_phone'] }}</a>@else<span class="mt-0.5 block text-slate-400">Kontak wali belum tersedia</span>@endif</span></span></td><td class="px-3 py-4"><span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600">Individu</span></td><td class="px-3 py-4 text-center">{{ $row['pretest'] ?? '-' }}</td><td class="px-3 py-4 text-center font-bold {{ $row['posttest'] !== null ? 'text-emerald-600' : 'text-slate-400' }}">{{ $row['posttest'] ?? '-' }}</td><td class="px-3 py-4 text-center">@if ($row['progress'] !== null)<span class="rounded bg-emerald-50 px-2 py-1 font-bold {{ $row['progress'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $row['progress'] > 0 ? '+' : '' }}{{ $row['progress'] }}%</span>@else<span class="text-slate-400">-</span>@endif</td><td class="px-3 py-4"><span class="font-semibold {{ $row['attendance_label'] === 'Hadir' ? 'text-emerald-700' : ($row['attendance_label'] === 'Belum Dicatat' ? 'text-slate-400' : 'text-amber-700') }}">{{ $row['attendance_label'] }}</span>@if ($row['attendance_time'])<span class="ml-1 text-slate-500">({{ $row['attendance_time']->format('H:i') }})</span>@endif</td><td class="px-3 py-4 text-center"><a href="{{ route('tutor.classroom.show', $package->package_id) }}" class="font-bold text-teal-700 hover:underline">Lihat Rapor</a></td></tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-12 text-center"><p class="font-bold">Belum ada siswa terdaftar di kelas ini</p><p class="mt-1 text-slate-500">Roster akan terisi dari pendaftaran siswa yang pembayarannya berhasil.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <aside class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600"><p><b class="text-[#102d3f]">Pengembangan berikutnya:</b> fitur skuad dan pengajuan guru pengganti belum tersedia. Roster saat ini menampilkan siswa yang terdaftar langsung pada kelas.</p></aside>
            </section>
            <footer class="mt-6 border-t border-slate-200 py-5 text-[11px] text-slate-500"><b class="text-[#102d3f]">KawalBelajar</b> &nbsp; {{ now()->year }} Yayasan Kawal Pendidikan Nusantara. Semua hak cipta dilindungi.</footer>
        </main>
    </div>
</body>
</html>
