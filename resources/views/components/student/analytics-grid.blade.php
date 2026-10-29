<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Kehadiran -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Kehadiran Kelas</div>
        <div class="my-2">
            <span class="text-2xl font-bold text-slate-900" x-text="(upcomingSession?.attendance_rate || '100') + '%'"></span>
        </div>
        <span class="inline-flex items-center text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md self-start border border-emerald-200">
            ✓ 3 Sesi Tuntas
        </span>
    </div>

    <!-- Rata-Rata Post-Test -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Rata-Rata Post-Test</div>
        <div class="my-2">
            <span class="text-2xl font-bold text-slate-900" x-text="upcomingSession?.avg_post_test || '92'"></span>
            <span class="text-xs text-slate-400">/100</span>
        </div>
        <span class="inline-flex items-center text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md self-start border border-emerald-200">
            📈 +20.4% Peningkatan
        </span>
    </div>

    <!-- Modul Terunduh -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Modul Terunduh</div>
        <div class="my-2">
            <span class="text-2xl font-bold text-slate-900" x-text="upcomingSession?.downloaded_modules || '3'"></span>
            <span class="text-xs text-slate-400">/4 Modul</span>
        </div>
        <span class="inline-flex items-center text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md self-start">
            Siap / Modul Sesi 4
        </span>
    </div>

    <!-- Kuis Selesai -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Kuis Selesai</div>
        <div class="my-2">
            <span class="text-2xl font-bold text-slate-900" x-text="upcomingSession?.completed_quizzes || '6'"></span>
            <span class="text-xs text-slate-400">Kuis</span>
        </div>
        <span class="inline-flex items-center text-[11px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md self-start border border-amber-200">
            📝 Pre + Post Test
        </span>
    </div>
</div>