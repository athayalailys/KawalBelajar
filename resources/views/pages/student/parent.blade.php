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

        <article x-show="data" x-cloak class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">PIN terverifikasi</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900" x-text="data?.student_name"></h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Tes awal terakhir</p><p class="mt-1 text-xl font-bold" x-text="data?.pretest_score ?? 'Belum ada nilai'"></p></div>
                <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Tes akhir terakhir</p><p class="mt-1 text-xl font-bold" x-text="data?.posttest_score ?? 'Belum ada nilai'"></p></div>
                <div class="rounded-xl bg-slate-50 p-4"><p class="text-xs text-slate-500">Catatan presensi</p><p class="mt-1 text-xl font-bold" x-text="data?.attendance_count ?? 0"></p></div>
            </div>
        </article>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
                Mode Orang Tua belum tersedia untuk jenjang Umum.
            </div>
        @endif
    </section>
</x-student-layout>
