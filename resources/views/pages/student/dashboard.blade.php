<x-student-layout title="Beranda & Jadwal Hari Ini - KawalBelajar">
    @php
        $sessionStart = $nearestSession?->start_time ?? $nearestSession?->scheduled_at;
        $sessionStart = $sessionStart
            ? \Illuminate\Support\Carbon::parse($sessionStart)->timezone('Asia/Makassar')
            : null;
        $sessionEnd = $sessionStart && $nearestSession?->duration_minutes
            ? $sessionStart->copy()->addMinutes($nearestSession->duration_minutes)
            : null;
        $package = $nearestSession?->order?->package;
        $material = $nearestSession?->material;
        $pretestDone = $nearestSession?->evaluation?->pretest_score !== null;
        $sessionAddress = $nearestSession?->student_address ?: $nearestSession?->onsite_address;
        $tutorName = $nearestSession?->teacher?->user?->full_name;
        $sessionNumber = $nearestSession?->session_number;
        $sessionCount = $nearestSession?->order?->session_package_count
            ?? $nearestSession?->order?->session_package;
        $sessionCode = $nearestSession
            ? 'SS-' . strtoupper(substr((string) $nearestSession->session_id, 0, 4))
            : null;
        $countdownTarget = $sessionStart?->isFuture() ? $sessionStart->toIso8601String() : null;
        $moduleUrl = $material?->file_or_url;
        $sessionTitle = $package?->package_name ?? $material?->title;
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col justify-between gap-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:p-8">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 md:text-2xl">
                    Halo, Selamat Belajar, {{ $user->full_name ?? 'Pembelajar' }}! <span aria-hidden="true">&#128640;</span>
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                    @if ($nearestSession)
                        Sesi bimbinganmu sudah terjadwal. Selesaikan tes awal sebelum sesi dimulai untuk membuka ruang belajar dan materi.
                    @else
                        Belum ada jadwal bimbingan terjadwal. Temukan kelas yang sesuai dan pesan sesi pertamamu.
                    @endif
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full border border-cyan-300 bg-cyan-50 px-4 py-2 text-xs font-bold text-cyan-900">
                Jenjang {{ $student->education_level ?: 'Belum diatur' }}
            </span>
        </section>

        <section aria-label="Aksi cepat" class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('student.booking') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-2xl text-amber-700" aria-hidden="true">&#128269;</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Cari &amp; Pesan Kelas Baru</span><span class="mt-1 block text-xs text-slate-500">Pilih kelas dan jadwal bimbingan</span></span>
            </a>
            <a href="{{ route('student.report') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-cyan-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-2xl text-cyan-700" aria-hidden="true">&#128200;</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Buka Rapor &amp; Evaluasi</span><span class="mt-1 block text-xs text-slate-500">Lihat hasil tes dan perkembangan belajar</span></span>
            </a>
            <a href="{{ route('student.parent') }}" class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-2xl text-rose-700" aria-hidden="true">&#128737;</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Mode Orang Tua</span><span class="mt-1 block text-xs text-slate-500">Pantau perkembangan dengan PIN</span></span>
            </a>
        </section>

        @if ($nearestSession)
            <section class="rounded-3xl border-2 border-amber-300 bg-amber-50/40 p-5 shadow-sm md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-full border border-slate-900 bg-amber-300 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-900">Sesi Belajar Terdekat</span>
                    @if ($countdownTarget)
                        <span class="rounded-full border border-slate-900 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">Mulai dalam: <strong class="text-rose-600" data-session-countdown data-target="{{ $countdownTarget }}">Menghitung waktu...</strong></span>
                    @endif
                </div>

                <div class="mt-5 grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                    <div>
                        @if ($sessionNumber || $sessionCount || $sessionCode)
                            <p class="text-xs font-extrabold uppercase tracking-wide text-cyan-800">
                                @if ($sessionNumber) Pertemuan {{ $sessionNumber }} @endif
                                @if ($sessionCount) dari {{ $sessionCount }} sesi @endif
                                @if ($sessionCode) <span class="px-1 text-slate-400">&bull;</span> ID Sesi: {{ $sessionCode }} @endif
                            </p>
                        @endif
                        <h2 class="mt-2 text-xl font-extrabold leading-snug text-slate-900">{{ $sessionTitle ?? 'Materi sesi belum ditentukan' }}</h2>
                        @if ($tutorName)
                            <p class="mt-2 text-sm font-semibold text-slate-700">Tutor: {{ $tutorName }}</p>
                        @endif

                        <div class="mt-5 grid gap-3 rounded-2xl border-2 border-slate-900 bg-white p-4 text-sm sm:grid-cols-2">
                            <p class="font-semibold text-slate-800">
                                @if ($sessionStart)
                                    {{ $sessionStart->translatedFormat('l, j F Y') }}, {{ $sessionStart->format('H:i') }}&ndash;{{ $sessionEnd?->format('H:i') ?? 'Waktu selesai belum ditentukan' }} WITA
                                @else
                                    Waktu sesi belum ditentukan
                                @endif
                            </p>
                            <p class="font-semibold text-slate-800">Metode: {{ $nearestSession->learning_method === 'home_visit' ? 'Kunjungan ke rumah' : ($nearestSession->learning_method === 'online_zoom' ? 'Daring melalui Zoom' : ($nearestSession->learning_method ?? 'Belum ditentukan')) }}</p>
                            @if ($nearestSession->learning_method === 'home_visit')
                                <p class="text-xs leading-relaxed text-slate-600 sm:col-span-2">Alamat belajar: {{ $sessionAddress ?: 'Alamat belajar belum tersedia.' }}</p>
                            @endif
                        </div>

                        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                            <p class="text-sm font-extrabold {{ $pretestDone ? 'text-emerald-700' : 'text-rose-700' }}">{{ $pretestDone ? 'Tes awal: Selesai' : 'Tes awal: Belum dikerjakan (wajib)' }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-600">Tes awal membantu tutor mengetahui pemahamanmu sebelum materi disampaikan.</p>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center gap-3">
                        <a href="{{ route('student.classroom', ['session_id' => $nearestSession->session_id]) }}" class="rounded-xl bg-emerald-500 px-4 py-3 text-center text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-600">Buka Ruang Kelas</a>
                        @if ($moduleUrl)
                            <a href="{{ $moduleUrl }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border-2 border-slate-900 bg-white px-4 py-3 text-center text-xs font-bold text-slate-800 transition hover:bg-slate-50">Buka Materi: {{ $material->title }}</a>
                        @else
                            <span class="rounded-xl border-2 border-slate-900 bg-white px-4 py-3 text-center text-xs font-bold text-slate-500">Materi sesi belum tersedia</span>
                        @endif
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-sm md:p-8" aria-labelledby="empty-schedule-title">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-cyan-100 text-2xl text-cyan-800" aria-hidden="true">&#128197;</span>
                    <h2 id="empty-schedule-title" class="mt-4 text-xl font-extrabold text-slate-900">Belum ada jadwal bimbingan terjadwal</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Pilih kelas, tutor, dan waktu belajar yang sesuai untuk mulai membuat jadwal bimbingan.</p>
                    <a href="{{ route('student.booking') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-amber-400 px-5 py-3 text-sm font-extrabold text-slate-900 shadow-sm transition hover:bg-amber-500">Cari &amp; Pesan Kelas Sekarang &rarr;</a>
                </div>
            </section>
        @endif
    </div>

    @if ($nearestSession)
        <script>
            document.querySelectorAll('[data-session-countdown]').forEach((element) => {
                const target = new Date(element.dataset.target).getTime();
                const render = () => {
                    let remaining = Math.max(0, target - Date.now());
                    const days = Math.floor(remaining / 86400000); remaining %= 86400000;
                    const hours = Math.floor(remaining / 3600000); remaining %= 3600000;
                    const minutes = Math.floor(remaining / 60000);
                    element.textContent = target <= Date.now() ? 'Sesi dimulai' : `${days ? `${days} hari ` : ''}${String(hours).padStart(2, '0')} jam ${String(minutes).padStart(2, '0')} menit`;
                };
                render();
                window.setInterval(render, 60000);
            });
        </script>
    @endif
</x-student-layout>
