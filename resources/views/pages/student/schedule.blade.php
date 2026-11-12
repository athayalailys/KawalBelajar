<x-student-layout title="Jadwal Belajar - KawalBelajar">
    <section class="space-y-6" x-data="studentSchedule" data-dashboard-url="{{ route('api.student.dashboard') }}">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Rencana belajar</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Jadwal belajar Anda</h1>
            <p class="mt-2 text-sm text-slate-600">Jadwal sesi yang telah dikonfirmasi tutor tampil di halaman ini.</p>
        </div>

        <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>
        <div x-show="loading" class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500">Memuat jadwal…</div>
        <div x-show="!loading && !error && sessions.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="font-bold text-slate-900">Belum ada sesi terjadwal</h2>
            <p class="mt-2 text-sm text-slate-500">Setelah pesanan dikonfirmasi dan sesi dibuat, jadwal akan muncul di sini.</p>
            <a href="{{ route('student.booking') }}" class="mt-5 inline-flex rounded-xl bg-amber-400 px-5 py-3 text-sm font-bold text-slate-900">Cari kelas belajar</a>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <template x-for="session in sessions" :key="session.session_id">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-teal-700" x-text="session.is_started ? (session.status === 'completed' ? 'Selesai' : (session.status === 'in_progress' ? 'Sedang berlangsung' : 'Jadwal aktif')) : 'Menunggu jadwal'"></p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900" x-text="session.package_name || 'Bimbingan belajar'"></h2>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold" :class="!session.is_started ? 'bg-slate-100 text-slate-700' : (session.is_locked ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800')" x-text="!session.is_started ? 'Menunggu jadwal' : (session.is_locked ? 'Tes awal perlu dikerjakan' : 'Sesi dapat diakses')"></span>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Waktu belajar</dt><dd class="font-semibold text-slate-800" x-text="date(session.scheduled_at)"></dd></div>
                        <div><dt class="text-slate-500">Tutor</dt><dd class="font-semibold text-slate-800" x-text="session.teacher_name || 'Belum tersedia' "></dd></div>
                        <div><dt class="text-slate-500">Durasi</dt><dd class="font-semibold text-slate-800" x-text="session.duration_minutes ? `${session.duration_minutes} menit` : 'Belum ditentukan'"></dd></div>
                    </dl>
                    <a :href="`{{ route('student.classroom') }}?session_id=${encodeURIComponent(session.session_id)}`" class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-800">Lihat rincian sesi</a>
                </article>
            </template>
        </div>
    </section>
</x-student-layout>
