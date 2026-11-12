<div id="student-agenda" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Agenda Jadwal Bimbingan Sepekan ke Depan</h3>
            <p class="text-xs text-slate-500 mt-0.5">Sesi belajar terdaftar yang telah terkonfirmasi oleh tutor.</p>
        </div>
        <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full border border-slate-200" x-text="(weeklyAgenda?.length || 3) + ' Sesi Terkonfirmasi'"></span>
    </div>

    <div class="divide-y divide-slate-100">
        <template x-for="(item, index) in weeklyAgenda" :key="index">
            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex flex-col items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="item.day">JUM</span>
                        <span class="text-sm font-extrabold text-slate-800" x-text="item.date_num">02</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-semibold text-slate-800 text-sm" x-text="item.title">Hukum Newton Sesi 1: Inersia & Gaya Gesek</h4>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-md border border-amber-200" x-text="item.badge">Sesi Terdekat</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            ⏰ <span x-text="item.time">18:00 - 19:30 WITA</span> • 🏠 <span x-text="item.method">Tutor datang ke rumah</span> • 👨‍🏫 <span x-text="item.tutor">Kak Alya Rosaan, S.T.</span>
                        </p>
                    </div>
                </div>
                <a :href="'/classroom/session/' + item.id" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl text-center transition shrink-0">
                    Masuk Kelas
                </a>
            </div>
        </template>
    </div>
</div>
