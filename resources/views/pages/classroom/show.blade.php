<x-student-layout title="Ruang Kelas - KawalBelajar">
    @if (!$session)
        <section class="mx-auto max-w-3xl rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <span class="text-4xl text-teal-700">▣</span>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">Belum ada sesi belajar</h1>
            <p class="mt-2 text-sm text-slate-600">Ruang kelas akan tersedia setelah pesanan dan jadwal sesi terkonfirmasi.</p>
            <a href="{{ route('student.booking') }}" class="mt-6 inline-flex rounded-xl bg-amber-400 px-5 py-3 text-sm font-bold text-slate-900">Cari kelas belajar</a>
        </section>
    @else
        <section class="space-y-6" x-data="studentClassroom"
            data-dashboard-url="{{ route('api.student.dashboard') }}"
            data-classroom-url="{{ route('student.classroom') }}"
            data-session-id="{{ $session->session_id }}"
            data-pretest-score="{{ $evaluation?->pretest_score ?? '' }}"
            data-room-access-url="{{ route('api.classroom.session.room-access', ['sessionId' => '__SESSION_ID__']) }}"
            data-exam-url="{{ route('api.classroom.session.exam', ['sessionId' => '__SESSION_ID__', 'examType' => '__EXAM_TYPE__']) }}"
            data-submit-exam-url="{{ route('api.classroom.session.submit-exam', ['sessionId' => '__SESSION_ID__']) }}">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <a href="{{ route('student.dashboard') }}" class="hover:text-teal-700">Portal Pembelajar</a>
                    <span>›</span><span class="font-semibold text-slate-900">Ruang Kelas & Evaluasi</span>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ $session->learning_method === 'home_visit' ? 'Tutor datang ke rumah' : 'Belajar daring melalui Zoom' }}
                </span>
            </div>

            @if ($sessions->count() > 1)
                <nav aria-label="Pilih sesi" class="flex gap-2 overflow-x-auto pb-1">
                    @foreach ($sessions as $classSession)
                        <a href="{{ route('student.classroom', ['session_id' => $classSession->session_id]) }}" @class([
                            'shrink-0 rounded-xl border px-4 py-3 text-left transition',
                            'border-teal-700 bg-teal-50 ring-2 ring-teal-100' => $classSession->session_id === $session->session_id,
                            'border-slate-200 bg-white hover:border-slate-400' => $classSession->session_id !== $session->session_id,
                        ])>
                            <span class="block text-xs font-bold uppercase text-teal-800">Sesi {{ $classSession->session_number ?? $loop->iteration }}</span>
                            <span class="mt-1 block text-xs text-slate-600">{{ $classSession->scheduled_at ? $classSession->scheduled_at->format('d M Y') : ($classSession->schedule?->start_time ? \Carbon\Carbon::parse($classSession->schedule->start_time)->format('d M Y') : 'Jadwal belum ditentukan') }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>
            <div x-show="notice" x-cloak role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" x-text="notice"></div>

            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span x-show="!loading && room?.is_locked && room?.gate_reason === 'not_started'" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-700">Menunggu Jadwal</span>
                        <span x-show="!loading && room?.is_locked && room?.gate_reason === 'pretest_required'" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-amber-900">Tes awal belum dikerjakan</span>
                        <span x-show="!loading && room && !room.is_locked" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-900">Tes awal selesai</span>
                        <span x-show="!loading && room?.session_status === 'in_progress'" class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-cyan-900">Sesi sedang berlangsung</span>
                        <span x-show="!loading && room?.session_status === 'completed'" class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-800">Sesi selesai</span>
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-800">
                            Sesi {{ $session->session_number ?? 1 }}{{ $scheduledAt ? ' · ' . $scheduledAt->format('H.i') . ' WITA' : '' }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-500" x-show="loading">Sedang memeriksa akses sesi…</span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $session->material?->title ?? $session->order?->package?->package_name ?? 'Ruang belajar KawalBelajar' }}
                </h1>
                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium text-slate-600">
                    <span>Tutor: <strong class="text-slate-900">{{ $session->teacher?->user?->full_name ?? 'Belum tersedia' }}</strong></span>
                    <span>{{ $scheduledAt?->isoFormat('D MMMM Y, HH:mm') ?? 'Jadwal belum ditentukan' }} WITA</span>
                    <span>{{ $session->duration_minutes ? $session->duration_minutes . ' menit' : 'Durasi belum ditentukan' }}</span>
                </div>
            </article>

            <section class="rounded-2xl border p-6 shadow-sm transition-colors" :class="room?.is_locked ? 'border-amber-200 bg-amber-50/70' : 'border-emerald-200 bg-emerald-50/70'">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-2xl" :class="room?.is_locked ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'" x-text="room?.is_locked ? '🔒' : '✓'"></span>
                    <div class="min-w-0 flex-1 space-y-2">
                        <h2 class="text-lg font-bold text-slate-900" x-text="room?.is_locked ? 'Ruang belajar terkunci' : 'Akses sesi terbuka'"></h2>
                        <p x-show="loading" class="text-sm text-slate-600">Sedang memeriksa ketentuan akses…</p>
                        <p x-show="!loading && room?.gate_reason === 'not_started'" class="text-sm leading-relaxed text-slate-700">Jadwal bimbingan belum dimulai. Tes awal dan ruang belajar tersedia saat waktu sesi tiba.</p>
                        <p x-show="!loading && room?.gate_reason === 'pretest_required'" class="text-sm leading-relaxed text-slate-700">Kerjakan tes awal terlebih dahulu. Tautan Zoom atau alamat belajar baru dapat dibuka setelah tes selesai.</p>
                        <p x-show="!loading && room && !room.is_locked" class="text-sm leading-relaxed text-slate-700">
                            Tes awal sudah tercatat<span x-show="preTestScore !== null"> dengan nilai <strong x-text="preTestScore"></strong>/100</span>. Rincian sesi tersedia di bawah.
                        </p>

                        <div x-show="room && !room.is_locked && room.address" x-cloak class="rounded-xl border border-emerald-200 bg-white/80 p-4 text-sm text-slate-800">
                            <p class="font-semibold">Alamat bimbingan</p><p class="mt-1" x-text="room?.address"></p>
                            <p x-show="room?.landmark" class="mt-2 text-xs text-slate-600" x-text="`Patokan: ${room?.landmark}`"></p>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-2">
                            <button type="button" x-show="room?.can_start_exam" x-on:click="openExam('pre_test')" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">Kerjakan tes awal sekarang →</button>
                            <a x-show="room && !room.is_locked && room.zoom_meeting_url" :href="room?.zoom_meeting_url" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-xl bg-emerald-700 px-5 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-800">Masuk Ruang Zoom</a>
                            <span x-show="room && !room.is_locked && !room.zoom_meeting_url && !room.address" class="text-sm text-slate-600">Tautan ruang belum ditambahkan oleh tutor.</span>
                            <button type="button" x-show="room?.can_take_post_test" x-on:click="openExam('post_test')" class="rounded-xl border border-emerald-700 bg-white px-5 py-2.5 text-xs font-bold text-emerald-900 transition hover:bg-emerald-50">Kerjakan tes akhir</button>
                            <span x-show="room && !room.is_locked && !room.can_take_post_test && room.posttest_score !== null" class="text-sm font-semibold text-emerald-800">Tes akhir selesai · Nilai <span x-text="room?.posttest_score"></span>/100</span>
                        </div>
                    </div>
                </div>
            </section>

            @if ($session->material)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Modul pembelajaran</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $session->material->title }}</p>
                    <a x-show="room && !room.is_locked" x-cloak href="{{ route('student.materials.download', ['sessionId' => $session->session_id, 'materialId' => $session->material->material_id]) }}" class="mt-4 inline-flex rounded-xl border-2 border-slate-900 bg-amber-300 px-5 py-3 text-sm font-extrabold text-slate-900 hover:bg-amber-200">Unduh modul</a>
                </section>
            @endif

            <section id="classroom-exam-panel" x-show="exam" x-cloak class="scroll-mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-teal-700" x-text="examType === 'pre_test' ? 'Tes Awal Diagnostik' : 'Tes Akhir' "></p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">Evaluasi sesi {{ $session->session_number ?? 1 }}</h2>
                        <p class="mt-1 text-xs text-slate-500">Jawaban akan diperiksa otomatis setelah dikirim.</p>
                    </div>
                    <span x-show="exam?.already_submitted" class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Sudah dikumpulkan · <span x-text="exam?.score"></span>/100</span>
                    <span x-show="examType === 'pre_test' && remainingSeconds !== null && !exam?.already_submitted" class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold tabular-nums text-amber-900" aria-live="polite">Sisa waktu <span x-text="timerLabel"></span></span>
                </div>

                <div x-show="exam?.questions?.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Soal belum tersedia untuk sesi ini.</div>
                <form x-show="exam?.questions?.length && !exam?.already_submitted" x-on:submit.prevent="submitExam()" class="space-y-5">
                    <template x-for="(question, index) in exam?.questions || []" :key="question.question_id">
                        <fieldset class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs font-bold text-slate-700" x-text="`Pertanyaan ${index + 1}`"></span>
                                <span class="text-xs font-semibold text-slate-500" x-text="`Kategori: ${question.category || 'Diagnostik'}`"></span>
                            </div>
                            <legend class="text-sm font-semibold leading-relaxed text-slate-900" x-text="question.question_text"></legend>
                            <div x-show="question.option_a || question.option_b || question.option_c || question.option_d" class="grid gap-3 pt-1 md:grid-cols-2">
                                <template x-for="letter in ['A', 'B', 'C', 'D']" :key="letter">
                                    <label x-show="question[`option_${letter.toLowerCase()}`]" class="flex cursor-pointer items-start gap-3 rounded-xl border bg-white p-3.5 text-xs transition" :class="answers[question.question_id] === letter ? 'border-amber-500 bg-amber-50 text-amber-950' : 'border-slate-200 hover:border-slate-300'">
                                        <input type="radio" :name="`answer_${question.question_id}`" :value="letter" x-model="answers[question.question_id]" class="mt-0.5 accent-amber-600">
                                        <span class="font-bold text-slate-500" x-text="`${letter}.`"></span>
                                        <span x-text="question[`option_${letter.toLowerCase()}`]"></span>
                                    </label>
                                </template>
                            </div>
                            <label x-show="!question.option_a && !question.option_b && !question.option_c && !question.option_d" class="block text-sm font-semibold text-slate-700">
                                Jawaban
                                <input type="text" x-model="answers[question.question_id]" autocomplete="off" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            </label>
                        </fieldset>
                    </template>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                        <button type="button" x-on:click="stopTimer(); remainingSeconds = null; exam = null; examType = null; window.history.replaceState({}, '', sessionUrl(selectedSession.session_id))" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800">Tutup ujian</button>
                        <button type="submit" :disabled="submitting || loading" class="rounded-xl bg-amber-500 px-5 py-3 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 disabled:opacity-50" x-text="submitting ? 'Sedang memeriksa jawaban…' : 'Kirim jawaban' "></button>
                    </div>
                </form>
                <div x-show="result" x-cloak class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    Jawaban tersimpan. Nilai: <strong x-text="result?.score"></strong>/100.
                    <span x-show="result?.exam_type === 'post_test' && result?.progress_percentage !== null">
                        Perubahan nilai dari tes awal: <strong x-text="`${Number(result?.progress_percentage) > 0 ? '+' : ''}${result?.progress_percentage}`"></strong>%.
                    </span>
                </div>
            </section>
        </section>
    @endif
</x-student-layout>
