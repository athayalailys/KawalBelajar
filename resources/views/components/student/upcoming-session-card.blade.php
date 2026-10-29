<div class="bg-amber-50/50 border border-amber-200 rounded-2xl p-6 relative overflow-hidden">
    <div class="flex items-center justify-between mb-4">
        <span class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full uppercase tracking-wider">Sesi Belajar Terdekat</span>
        <div class="text-xs font-medium text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-amber-200">
            ⏰ Mulai dalam: <span class="font-bold text-amber-700" x-text="upcomingSession?.countdown || '01 Jam 15 Menit'"></span>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6 items-center">
        <div class="md:col-span-2 space-y-3">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                Pertemuan <span x-text="upcomingSession?.session_number">1</span> dari <span x-text="upcomingSession?.total_sessions">4</span> Sesi
            </div>
            <h2 class="text-xl font-bold text-slate-900" x-text="upcomingSession?.title || 'Pendalaman Hukum Newton: Konsep Inersia & Resultan Gaya'"></h2>
            <p class="text-sm text-slate-600">Tutor: <span class="font-semibold text-slate-800" x-text="upcomingSession?.tutor_name">Kak Alya Rosaan, S.T.</span></p>

            <div class="flex flex-wrap gap-2 text-xs text-slate-600 pt-2">
                <span class="bg-white px-2.5 py-1 rounded-md border border-slate-200">📅 <span x-text="upcomingSession?.time_slot">Hari ini, 18:00 - 19:30 WITA</span></span>
                <span class="bg-white px-2.5 py-1 rounded-md border border-slate-200">🏠 Metode: <span x-text="upcomingSession?.method">Home Visit</span></span>
            </div>
        </div>

        <!-- Action Button & Pre-Test Gating Notice -->
        <div class="flex flex-col gap-3 justify-center">
            <!-- Jika Pre-Test Terkunci -->
            <template x-if="upcomingSession?.is_locked">
                <a :href="'/classroom/exam/' + upcomingSession?.id" class="w-full text-center py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    Masuk Ruang Kelas & Pre-Test
                </a>
            </template>

            <!-- Jika Pre-Test Selesai & Zoom/Lokasi Unlocked -->
            <template x-if="!upcomingSession?.is_locked">
                <a :href="upcomingSession?.zoom_url" target="_blank" class="w-full text-center py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-sm transition">
                    Buka Zoom / Navigasi Lokasi
                </a>
            </template>

            <button class="w-full text-center py-2.5 px-4 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium text-xs rounded-xl transition">
                📥 Unduh Modul (PDF)
            </button>
        </div>
    </div>
</div>