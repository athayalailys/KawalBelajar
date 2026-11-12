<x-student-layout title="Rapor Belajar - KawalBelajar">
    <section class="space-y-6" x-data="studentReport" data-dashboard-url="{{ route('api.student.dashboard') }}">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Ringkasan perkembangan</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Rapor belajar</h1>
            <p class="mt-2 text-sm text-slate-600">Ringkasan ini memuat perkembangan belajar yang tercatat pada akun Anda.</p>
        </div>
        <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>
        <div x-show="loading" class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500">Memuat ringkasan…</div>
        <div x-show="!loading && data" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Kehadiran</p>
                <p class="mt-3 text-3xl font-bold text-slate-900" x-text="percent(data?.analytics?.attendance_percentage)"></p>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-teal-600" :style="`width: ${percent(data?.analytics?.attendance_percentage)}`"></div></div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Rata-rata tes akhir</p>
                <p class="mt-3 text-3xl font-bold text-slate-900" x-text="data?.analytics?.avg_post_test === null || data?.analytics?.avg_post_test === undefined ? 'Belum ada nilai' : `${data.analytics.avg_post_test}/100`"></p>
                <p class="mt-2 text-xs text-slate-500">Berdasarkan hasil yang sudah tercatat.</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Kuis terselesaikan</p>
                <p class="mt-3 text-3xl font-bold text-slate-900" x-text="data?.analytics?.kuis_selesai ?? 0"></p>
                <p class="mt-2 text-xs text-slate-500">Jumlah evaluasi yang memiliki nilai.</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Modul terunduh</p>
                <p class="mt-3 text-3xl font-bold text-slate-900" x-text="data?.analytics?.modul_terunduh ?? 'Belum tersedia'"></p>
                <p class="mt-2 text-xs text-slate-500">Data unduhan belum tersedia.</p>
            </article>
        </div>
        <div x-show="!loading && data && (!data.weekly_agenda || data.weekly_agenda.length === 0)" class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-600">
            Belum ada jadwal mingguan atau catatan sesi untuk ditampilkan.
        </div>
        <div x-show="data?.weekly_agenda?.length" class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="font-bold text-slate-900">Agenda minggu ini</h2>
            <ul class="mt-4 divide-y divide-slate-100">
                <template x-for="session in data?.weekly_agenda || []" :key="session.session_id">
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div><p class="font-semibold text-slate-900" x-text="session.package_name || 'Sesi belajar'"></p><p class="text-xs text-slate-500" x-text="session.scheduled_at ? new Date(session.scheduled_at).toLocaleString('id-ID') : 'Waktu belum ditentukan'"></p></div>
                        <span class="text-xs font-semibold text-slate-600" x-text="({ scheduled: 'Terjadwal', in_progress: 'Sedang berlangsung', completed: 'Selesai', cancelled: 'Dibatalkan' })[session.status] || 'Status belum tersedia'"></span>
                    </li>
                </template>
            </ul>
        </div>
        <a href="{{ route('student.classroom') }}" class="inline-flex rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white">Buka ruang kelas</a>
    </section>
</x-student-layout>
