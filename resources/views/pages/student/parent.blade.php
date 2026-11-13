<x-student-layout title="Mode Orang Tua - KawalBelajar">
    <section class="mx-auto max-w-3xl space-y-6" x-data="studentParentMode" data-verify-pin-url="{{ route('api.student.parent-mode.verify-pin') }}">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Perlindungan keluarga</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Mode Orang Tua</h1>
            <p class="mt-2 text-sm text-slate-600">
                {{ $parentModeAvailable ? 'Masukkan PIN 6 digit untuk melihat ringkasan perkembangan pembelajar.' : 'Mode Orang Tua tersedia untuk jenjang SD, SMP, dan SMA.' }}
            </p>
        </div>

        @if ($parentModeAvailable)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <strong>Informasi:</strong> PIN akan diperiksa melalui profil pembelajar dan ringkasan ditampilkan setelah verifikasi. Akun orang tua terpisah belum tersedia.
        </div>
        <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>

        <form x-on:submit.prevent="verify()" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <label for="parent-pin" class="block text-sm font-semibold text-slate-800">PIN orang tua</label>
            <input id="parent-pin" x-model="pin" type="password" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" minlength="6" required class="w-full rounded-xl border-slate-300 text-lg tracking-[0.4em]" aria-describedby="pin-help">
            <p id="pin-help" class="text-xs text-slate-500">PIN digunakan hanya untuk permintaan ini dan tidak disimpan di browser.</p>
            <button type="submit" :disabled="loading || pin.length !== 6" class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-bold text-white disabled:opacity-50" x-text="loading ? 'Memverifikasi…' : 'Verifikasi PIN'"></button>
        </form>

        <article x-show="data" x-cloak class="space-y-6 rounded-2xl border-2 border-slate-900 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">PIN terverifikasi</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900" x-text="data?.student_name"></h2>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4"><p class="text-xs text-slate-600">Rata-rata tes awal</p><p class="mt-1 text-2xl font-extrabold" x-text="data?.kpi?.average_pretest ?? '—'"></p></div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"><p class="text-xs text-slate-600">Rata-rata tes akhir</p><p class="mt-1 text-2xl font-extrabold" x-text="data?.kpi?.average_posttest ?? '—'"></p></div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs text-slate-600">Rata-rata peningkatan</p><p class="mt-1 text-2xl font-extrabold" x-text="data?.kpi?.average_improvement ?? '—'"></p></div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs text-slate-600">Kehadiran</p><p class="mt-1 text-2xl font-extrabold" x-text="`${data?.kpi?.attendance_percentage ?? 0}%`"></p></div>
            </div>

            <section class="rounded-xl border border-slate-200 p-4" aria-labelledby="parent-score-chart-title">
                <h3 id="parent-score-chart-title" class="font-bold text-slate-900">Perbandingan nilai per sesi</h3>
                <p class="mt-1 text-xs text-slate-500">Batang biru menunjukkan tes awal dan hijau menunjukkan tes akhir.</p>
                <div class="mt-4 flex gap-4 overflow-x-auto pb-2">
                    <template x-for="(session, index) in data?.session_history || []" :key="session.session_id">
                        <div class="min-w-24 flex-1 text-center">
                            <div class="flex h-36 items-end justify-center gap-2 rounded-lg bg-slate-50 px-3 py-2">
                                <div class="w-6 rounded-t bg-cyan-500" :style="`height:${Math.max(4, Number(session.pretest_score || 0) * 1.2)}px`" :title="`Tes awal ${session.pretest_score ?? 'belum ada'}`"></div>
                                <div class="w-6 rounded-t bg-emerald-500" :style="`height:${Math.max(4, Number(session.posttest_score || 0) * 1.2)}px`" :title="`Tes akhir ${session.posttest_score ?? 'belum ada'}`"></div>
                            </div>
                            <p class="mt-2 text-xs font-semibold" x-text="`Sesi ${index + 1}`"></p>
                        </div>
                    </template>
                    <p x-show="!data?.session_history?.length" class="text-sm text-slate-500">Belum ada nilai sesi untuk dibandingkan.</p>
                </div>
            </section>

            <section class="grid gap-3 sm:grid-cols-3" aria-label="Rekap kehadiran">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"><span class="text-sm text-slate-600">Hadir</span><strong class="float-right text-lg" x-text="data?.attendance_breakdown?.hadir ?? 0"></strong></div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><span class="text-sm text-slate-600">Izin / sakit</span><strong class="float-right text-lg" x-text="data?.attendance_breakdown?.izin ?? 0"></strong></div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4"><span class="text-sm text-slate-600">Alfa</span><strong class="float-right text-lg" x-text="data?.attendance_breakdown?.alfa ?? 0"></strong></div>
            </section>

            <section aria-labelledby="parent-session-history-title">
                <h3 id="parent-session-history-title" class="font-bold text-slate-900">Riwayat sesi dan evaluasi tutor</h3>
                <div class="mt-3 space-y-3">
                    <template x-for="(session, index) in data?.session_history || []" :key="session.session_id">
                        <article class="rounded-xl border border-slate-200 bg-white p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div><h4 class="font-bold text-slate-900" x-text="session.subject"></h4><p class="mt-1 text-xs text-slate-600" x-text="`${session.date || 'Tanggal belum tersedia'} · Tutor: ${session.tutor_name}`"></p></div>
                                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-800" x-text="session.attendance"></span>
                            </div>
                            <p class="mt-2 text-sm text-slate-700" x-text="`Tes awal: ${session.pretest_score ?? '—'} · Tes akhir: ${session.posttest_score ?? '—'}`"></p>
                            <p class="mt-2 text-sm text-slate-600" x-text="session.teacher_notes || 'Belum ada catatan evaluasi tutor.'"></p>
                        </article>
                    </template>
                    <p x-show="!data?.session_history?.length" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada riwayat sesi pembelajaran.</p>
                </div>
            </section>
        </article>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Mode Orang Tua belum tersedia untuk jenjang Umum.
            </div>
        @endif
    </section>
</x-student-layout>
