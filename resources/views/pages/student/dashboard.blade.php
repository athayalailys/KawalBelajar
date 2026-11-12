<x-student-layout title="Beranda & Jadwal Hari Ini - KawalBelajar">
    @php
        $sessionStart = $nearestSession?->start_time ?? $nearestSession?->scheduled_at;
        $sessionStart = $sessionStart ? \Illuminate\Support\Carbon::parse($sessionStart)->timezone('Asia/Makassar') : null;
        $sessionEnd = $sessionStart && $nearestSession?->duration_minutes
            ? $sessionStart->copy()->addMinutes($nearestSession->duration_minutes)
            : null;
        $package = $nearestSession?->order?->package;
        $pretestDone = $nearestSession?->evaluation?->pretest_score !== null;
        $sessionAddress = $nearestSession?->student_address ?: $nearestSession?->onsite_address;
        $tutorName = $nearestSession?->teacher?->user?->full_name;
        $sessionNumber = $nearestSession?->session_number ?: 1;
        $sessionCount = $nearestSession?->order?->session_package_count ?: 4;
        $sessionCode = $nearestSession
            ? 'SS-' . strtoupper(substr((string) $nearestSession->session_id, 0, 4))
            : 'SS-8492';
        $countdown = $sessionStart && $sessionStart->isFuture()
            ? sprintf('%02d Jam %02d Menit', (int) now('Asia/Makassar')->diffInHours($sessionStart), (int) now('Asia/Makassar')->diffInMinutes($sessionStart) % 60)
            : '01 Jam 15 Menit';
        $moduleUrl = $nearestSession?->material?->file_or_url;
    @endphp

    <div class="space-y-6">
        <!-- Sapaan pembelajar -->
        <section class="flex flex-col justify-between gap-5 rounded-3xl border-1 border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:p-8">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 md:text-2xl">
                    Halo, Selamat Belajar, {{ $user->full_name ?? 'Alya Rosaan' }}! 🚀
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                    @if($nearestSession)
                        Sesi tatap muka Anda sudah terjadwal. Kerjakan tes awal sebelum sesi dimulai untuk membuka alamat dan bahan belajar.
                    @else
                        Belum ada sesi belajar terjadwal hari ini. Cari kelas yang sesuai dan mulai perjalanan belajarmu bersama KawalBelajar.
                    @endif
                </p>
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full border border-cyan-300 bg-cyan-50 px-4 py-2 text-xs font-bold text-cyan-900">
                🧪 Jenjang {{ $student->education_level ?? 'SMP' }}
            </span>
        </section>

        <!-- Quick Actions -->
        <section aria-label="Aksi cepat" class="grid gap-4 md:grid-cols-3">
            <a href="{{ route('student.booking') }}" class="group flex items-center gap-4 rounded-2xl border-1 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-amber-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-2xl text-amber-700" aria-hidden="true">⌕</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Cari &amp; Pesan Kelas Baru</span><span class="mt-1 block text-xs text-slate-500">Wizard eksplorasi bimbingan</span></span>
            </a>
            <a href="{{ route('student.report') }}" class="group flex items-center gap-4 rounded-2xl border-1 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-cyan-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-2xl text-cyan-700" aria-hidden="true">▥</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Buka Rapor &amp; Evaluasi</span><span class="mt-1 block text-xs text-slate-500">Grafik perkembangan nilai tes awal dan tes akhir</span></span>
            </a>
            <a href="{{ route('student.parent') }}" class="group flex items-center gap-4 rounded-2xl border-1 border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-50">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-2xl text-rose-700" aria-hidden="true">⬟</span>
                <span><span class="block text-sm font-extrabold text-slate-900">Akses Orang Tua</span><span class="mt-1 block text-xs text-slate-500">Pemantauan dengan PIN 6-digit</span></span>
            </a>
        </section>

        <!-- Sesi nyata jika tersedia; data contoh menjaga wireframe tetap terlihat saat sesi belum ada. -->
        <section class="rounded-3xl border-2 border-amber-300 bg-amber-50/40 p-5 shadow-sm md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="rounded-full border border-slate-900 bg-amber-300 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-slate-900">Sesi Belajar Terdekat</span>
                <span class="rounded-full border border-slate-900 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700">⏱️ Mulai dalam: <strong class="text-rose-600">{{ $countdown }}</strong></span>
            </div>

            <div class="mt-5 grid gap-6 xl:grid-cols-[minmax(0,1fr)_280px]">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-wide text-cyan-800">Pertemuan {{ $sessionNumber }} dari {{ $sessionCount }} sesi <span class="px-1 text-slate-400">•</span> ID Sesi: {{ $sessionCode }}</p>
                    <h2 class="mt-2 text-xl font-extrabold leading-snug text-slate-900">{{ $package?->package_name ?? 'Pendalaman Hukum Newton: Konsep Inersia dan Resultan Gaya' }}</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-700">👩‍🏫 Tutor: {{ $tutorName ?? 'Kak Alya Rosaan, S.T. (Tutor Senior Fisika ★4.9)' }}</p>

                    <div class="mt-5 grid gap-3 rounded-2xl border-2 border-slate-900 bg-white p-4 text-sm sm:grid-cols-2">
                        <p class="font-semibold text-slate-800">📅 {{ $sessionStart ? $sessionStart->translatedFormat('l, H:i') . ' – ' . ($sessionEnd?->format('H:i') ?? $sessionStart->copy()->addMinutes(90)->format('H:i')) . ' WITA' : 'Hari ini, 18:00 – 19:30 WITA' }}</p>
                        <p class="font-semibold text-slate-800">🏠 Metode: {{ $nearestSession ? (strtolower((string) $nearestSession->learning_method) === 'home_visit' ? 'Kunjungan ke Rumah' : 'Daring melalui Zoom') : 'Kunjungan ke Rumah' }}{{ !$nearestSession || strtolower((string) $nearestSession->learning_method) === 'home_visit' ? ' (Rumah Rizky)' : '' }}</p>
                        <p class="text-xs leading-relaxed text-slate-600 sm:col-span-2">📍 Alamat belajar: {{ $sessionAddress ?? 'Jl. Brigjen H. Hasan Basry No. 45, Komplek Kayu Tangi II, Banjarmasin...' }}</p>
                    </div>

                    <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-sm font-extrabold {{ $pretestDone ? 'text-emerald-700' : 'text-rose-700' }}">{{ $pretestDone ? '✓ Tes awal: Selesai' : '⚠️ Tes awal: Belum dikerjakan (wajib)' }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">Tes awal digunakan untuk mengetahui pemahaman sebelum materi disampaikan tutor.</p>
                    </div>
                </div>

                <div class="flex flex-col justify-center gap-3">
                    @if($nearestSession)
                        <a href="{{ route('student.classroom', ['session_id' => $nearestSession->session_id]) }}" class="rounded-xl bg-emerald-500 px-4 py-3 text-center text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-600">Buka Ruang Kelas &amp; Kerjakan Tes Awal</a>
                    @else
                        <a href="{{ route('student.booking') }}" class="rounded-xl bg-emerald-500 px-4 py-3 text-center text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-600">Cari Kelas dan Tutor</a>
                    @endif
                    @if($moduleUrl)
                        <a href="{{ $moduleUrl }}" target="_blank" rel="noopener" class="rounded-xl border-2 border-slate-900 bg-white px-4 py-3 text-center text-xs font-bold text-slate-800 transition hover:bg-slate-50">📄 Unduh Modul Sesi {{ $sessionNumber }} (PDF, 4.8 MB)</a>
                    @else
                        <span class="rounded-xl border-2 border-slate-900 bg-white px-4 py-3 text-center text-xs font-bold text-slate-500">📄 Modul sesi belum tersedia</span>
                    @endif
                </div>
            </div>
        </section>

        {{-- Fitur kelompok belajar disimpan untuk pengembangan berikutnya.
        <section class="rounded-3xl border-2 border-emerald-300 bg-emerald-50/60 p-6">
            <span class="rounded-full bg-emerald-800 px-3 py-1 text-xs font-bold text-white">PEMBENTUK KELOMPOK • HEMAT 25%</span>
            <span class="ml-2 rounded-full bg-white px-3 py-1 text-xs font-bold text-emerald-800">🟢 4 dari 5 tempat terisi</span>
            <h2 class="mt-4 text-xl font-extrabold">Kelompok Fisika SMP: Pendalaman Hukum Newton</h2>
            <p class="mt-2 text-sm">Bagikan kode kelompok kepada teman sekelas untuk mengisi satu tempat yang tersisa...</p>
            <p class="mt-3">Rizky (Host) · Siti N. · Budi S. · Fajar P. · +1 Kosong</p>
            <div class="mt-4 rounded-xl bg-slate-900 p-4 text-white">SQD-FISIKA-SMP-094 <button type="button">📋 Salin kode kelompok</button></div>
        </section>
        --}}
    </div>
</x-student-layout>
