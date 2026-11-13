<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ruang Mengajar - {{ $package->package_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="min-w-0 flex-1">
            <x-tutor.topbar title="Teaching Cockpit" />
            <x-tutor.mobile-nav />

            <div class="mx-auto max-w-6xl space-y-5 px-5 py-6 sm:px-8 sm:py-7">
                @if (session('status'))
                    <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-semibold text-green-800">{{ session('status') }}</div>
                @endif

                @php
                    $methodLabel = match ($package->learning_method) {
                        'online_zoom' => 'Online (Zoom)',
                        'home_visit' => 'Home Visit',
                        default => $package->learning_method ?? 'Metode bimbingan',
                    };
                    $roomTitle = $selectedStudent
                        ? $selectedStudent->user?->full_name . ' - ' . $package->package_name
                        : $package->package_name;
                    $progressDelta = $selectedEvaluation?->progress_percentage;
                @endphp

                {{-- Section atas: info kelas dan akses ruang pembelajaran --}}
                <section class="grid gap-5 rounded-[24px] bg-[#102d3f] p-5 text-white sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-7" aria-labelledby="room-title">
                    <div>
                        <span class="inline-flex rounded-full bg-amber-400 px-4 py-1 text-xs font-bold text-[#102d3f]">
                            {{ $roomSession?->session_status === 'in_progress' ? 'Live Teaching Workspace' : 'Ruang Mengajar' }}
                        </span>
                        <h2 id="room-title" class="mt-3 text-xl font-extrabold sm:text-2xl">{{ $roomTitle }}</h2>
                        <p class="mt-2 text-xs text-slate-300">
                            @if ($roomSession)
                                Sesi {{ $roomSession->session_number ?? '—' }}: {{ $package->target_learning ?? $package->subject_name ?? $package->package_name }}
                                @if ($roomSession->scheduled_at) · {{ $roomSession->scheduled_at->translatedFormat('l, j F Y H.i') }} WITA @endif
                            @else
                                {{ $package->subject_name ?? 'Sesi belum dijadwalkan' }}
                            @endif
                        </p>
                    </div>
                    @if ($roomSession?->zoom_meeting_url)
                        <a href="{{ $roomSession->zoom_meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-16 items-center justify-center gap-3 rounded-2xl bg-green-500 px-6 py-4 text-center text-sm font-bold text-white transition hover:bg-green-600">
                            <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5A1.5 1.5 0 0 1 4.5 6h10A1.5 1.5 0 0 1 16 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 3 16.5v-9Zm13 3 5-3v9l-5-3"/></svg>
                            <span>Buka Ruang Zoom<br>Pengajar</span>
                        </a>
                    @else
                        <span class="inline-flex min-h-16 cursor-not-allowed items-center justify-center gap-3 rounded-2xl bg-slate-500 px-6 py-4 text-center text-sm font-bold text-white" title="Tautan Zoom belum tersedia untuk sesi ini">
                            <span class="text-2xl" aria-hidden="true">▣</span><span>Tautan Zoom<br>Belum Tersedia</span>
                        </span>
                    @endif
                </section>

                {{-- Section bawah: roster dan absensi, atau evaluasi siswa terpilih --}}
                @if ($selectedStudent)
                    <section class="space-y-5" aria-label="Evaluasi siswa">
                        <a href="{{ route('tutor.classroom.show', $package->package_id) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-cyan-800 hover:text-cyan-950">← Kembali ke daftar siswa</a>

                        <article class="rounded-[24px] border-2 border-[#57a5b4] bg-white/80 p-5 sm:p-7">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h3 class="text-lg font-extrabold">Hasil Diagnostik Pre-Test</h3>
                                <span class="rounded-full bg-cyan-100 px-5 py-2 text-sm font-extrabold text-cyan-700">
                                    @if ($selectedEvaluation?->pretest_score !== null) Skor: {{ $selectedEvaluation->pretest_score }} / 100 @else Pre-Test belum dikerjakan @endif
                                </span>
                            </div>
                            @if ($pretestAnswers->isNotEmpty())
                                <div class="mt-4 space-y-2 text-sm font-semibold">
                                    <div class="flex items-center justify-between rounded-xl bg-green-100 px-4 py-3 text-green-900"><span>Jawaban Benar</span><span>{{ $pretestCorrectCount }}</span></div>
                                    <div class="flex items-center justify-between rounded-xl bg-rose-300 px-4 py-3 text-rose-950"><span>Jawaban Salah</span><span>{{ $pretestWrongCount }}</span></div>
                                </div>
                            @else
                                <p class="mt-4 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-600">Belum ada rincian jawaban Pre-Test untuk siswa ini.</p>
                            @endif
                        </article>

                        <article class="rounded-[24px] border-2 border-[#57a5b4] bg-white/80 p-5 sm:p-7">
                            <h3 class="text-lg font-extrabold">Evaluasi Akhir Sesi &amp; Rapor Digital</h3>
                            <p class="mt-1 text-sm text-slate-500">Nilai dan catatan akan disimpan di rapor digital siswa.</p>
                            <form method="POST" action="{{ route('tutor.classroom.evaluation', [$package->package_id, $selectedStudent->student_id]) }}" class="mt-5 space-y-4">
                                @csrf
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="block text-sm font-semibold">Skor Post-Test Sesi Ini (0–100)
                                        <input name="posttest_score" type="number" min="0" max="100" value="{{ old('posttest_score', $selectedEvaluation?->posttest_score) }}" class="mt-2 w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-base font-bold outline-none focus:border-cyan-500" required>
                                    </label>
                                    <div class="block text-sm font-semibold">Presentase Peningkatan
                                        <div class="mt-2 flex min-h-12 items-center rounded-xl bg-green-100 px-4 py-3 font-extrabold text-green-800">
                                            @if ($progressDelta !== null)
                                                {{ $progressDelta > 0 ? '+' : '' }}{{ $progressDelta }}% dari Nilai Pre-Test
                                            @else
                                                Menunggu nilai Pre-Test dan Post-Test
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <label class="block text-sm font-semibold">Catatan Perkembangan
                                    <textarea name="teacher_notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-normal outline-none focus:border-cyan-500" placeholder="Tuliskan perkembangan siswa selama sesi">{{ old('teacher_notes', $selectedEvaluation?->teacher_notes) }}</textarea>
                                </label>
                                <button type="submit" class="w-full rounded-xl border border-black bg-amber-400 px-5 py-3 text-sm font-extrabold text-[#102d3f] hover:bg-amber-300">Simpan dan Kirim Rapor Digital →</button>
                            </form>
                        </article>
                    </section>
                @else
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="students-title">
                        @if ($errors->any())
                            <div class="border-b border-rose-200 bg-rose-50 px-5 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
                        @endif
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <h3 id="students-title" class="text-lg font-extrabold">Daftar Siswa</h3>
                            <input id="student-search" type="search" placeholder="Cari siswa..." class="w-full max-w-56 rounded-full bg-slate-100 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-cyan-300">
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[760px] text-left text-sm">
                                <thead class="bg-slate-100 text-xs font-semibold text-slate-500">
                                    <tr><th class="px-5 py-3">Nama</th><th class="px-4 py-3">Jenjang</th><th class="px-4 py-3">Progres</th><th class="px-4 py-3">Status</th><th class="px-5 py-3">Aksi / Kehadiran</th></tr>
                                </thead>
                                <tbody id="student-rows" class="divide-y divide-slate-100">
                                    @forelse ($students as $studentId => $row)
                                        @php
                                            $student = $row['student'];
                                            $studentSession = $row['session'];
                                            $evaluation = $studentSession->evaluationScores->firstWhere('student_id', $studentId);
                                            $attendance = $studentSession->studentAttendances->firstWhere('student_id', $studentId);
                                            $progress = $evaluation?->posttest_score ?? $evaluation?->pretest_score ?? 0;
                                            $attendanceLabel = match ($attendance?->status) {
                                                'present' => 'Hadir',
                                                'absent' => 'Tidak Hadir',
                                                'sick' => 'Sakit',
                                                'permission' => 'Izin',
                                                default => 'Belum Dicek',
                                            };
                                        @endphp
                                        <tr data-student-row data-student-name="{{ strtolower($student->user?->full_name ?? '') }}" class="hover:bg-slate-50">
                                            <td class="px-5 py-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-xs font-bold text-white">{{ mb_strtoupper(mb_substr($student->user?->full_name ?? 'S', 0, 2)) }}</span>
                                                    <span class="font-bold">{{ $student->user?->full_name ?? 'Nama siswa belum tersedia' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3"><span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-800">{{ $student->education_level ?? '—' }}</span></td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-cyan-600" style="width: {{ min(100, max(0, (int) $progress)) }}%"></span></span>
                                                    <span class="text-xs font-semibold">{{ $progress }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $attendance?->status === 'present' ? 'bg-green-50 text-green-700' : ($attendance?->status === 'absent' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600') }}">{{ $attendanceLabel }}</span></td>
                                            <td class="px-5 py-3">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="{{ route('tutor.classroom.student', [$package->package_id, $studentId]) }}" title="Lihat evaluasi siswa" aria-label="Lihat evaluasi {{ $student->user?->full_name }}" class="rounded-lg p-2 text-cyan-800 hover:bg-cyan-50">◎</a>
                                                    <form method="POST" action="{{ route('tutor.classroom.attendance', [$package->package_id, $studentId]) }}" class="flex gap-1">
                                                        @csrf
                                                        <button name="status" value="present" class="rounded-full bg-green-100 px-3 py-1.5 text-xs font-bold text-green-800 hover:bg-green-200">Hadir</button>
                                                        <button name="status" value="absent" class="rounded-full bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-800 hover:bg-rose-200">Tidak Hadir</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada siswa terdaftar atau sesi kelas belum dijadwalkan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>

    <script>
        const studentSearch = document.getElementById('student-search');
        studentSearch?.addEventListener('input', () => {
            const query = studentSearch.value.trim().toLowerCase();
            document.querySelectorAll('[data-student-row]').forEach(row => {
                row.classList.toggle('hidden', !row.dataset.studentName.includes(query));
            });
        });
    </script>
</body>
</html>
