<section x-show="step === 3" x-cloak class="rounded-3xl border-2 border-slate-900 bg-white p-5 md:p-7">
    <div class="text-center">
        <h2 class="text-lg font-extrabold text-slate-900 md:text-xl">Kapan ingin memulai sesi belajar?</h2>
        <p class="mt-1 text-[11px] text-slate-500">Zona waktu: Waktu Indonesia Tengah (WITA / GMT+8)</p>
    </div>

    <div class="mx-auto mt-4 flex w-fit rounded-full border border-teal-700 bg-slate-100 p-1 text-[11px] font-bold">
        <button type="button" @click="calendarMode = 'day'" :class="calendarMode === 'day' ? 'bg-teal-600 text-white shadow' : 'text-slate-600'" class="rounded-full px-4 py-2 transition">Harian</button>
        <button type="button" @click="calendarMode = 'month'" :class="calendarMode === 'month' ? 'bg-teal-600 text-white shadow' : 'text-slate-600'" class="rounded-full px-4 py-2 transition">Bulanan</button>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-2xl border-2 border-slate-900 bg-slate-50 p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <button x-show="calendarMode === 'month'" type="button" @click="moveCalendarMonth(-1)" aria-label="Bulan sebelumnya" class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-400 bg-white text-sm font-bold hover:bg-slate-100">‹</button>
                <button x-show="calendarMode === 'day'" type="button" @click="moveCalendarDate(-1)" aria-label="Hari tersedia sebelumnya" class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-400 bg-white text-sm font-bold hover:bg-slate-100">‹</button>
                <h3 x-show="calendarMode === 'month'" class="text-center text-xs font-extrabold capitalize text-slate-900" x-text="calendarMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })"></h3>
                <h3 x-show="calendarMode === 'day'" class="text-center text-xs font-extrabold capitalize text-slate-900" x-text="selectedCalendarDateLabel"></h3>
                <button x-show="calendarMode === 'month'" type="button" @click="moveCalendarMonth(1)" aria-label="Bulan berikutnya" class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-400 bg-cyan-100 text-sm font-bold text-slate-900 hover:bg-cyan-200">›</button>
                <button x-show="calendarMode === 'day'" type="button" @click="moveCalendarDate(1)" aria-label="Hari tersedia berikutnya" class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-400 bg-cyan-100 text-sm font-bold text-slate-900 hover:bg-cyan-200">›</button>
            </div>

            <div x-show="calendarMode === 'month'" class="grid grid-cols-7 gap-1 text-center">
                <template x-for="label in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="label"><span class="py-1 text-[9px] font-bold text-slate-500" x-text="label"></span></template>
                <template x-for="(date, index) in calendarDays()" :key="date?.key || `empty-${index}`">
                    <span>
                        <button x-show="date" type="button" @click="date?.available && selectCalendarDate(date.key)" :disabled="!date?.available" :aria-pressed="selectedCalendarDate === date?.key" :class="selectedCalendarDate === date?.key ? 'bg-teal-600 text-white ring-2 ring-teal-200' : (date?.available ? 'bg-cyan-50 text-teal-800 hover:bg-cyan-100' : 'text-slate-300')" class="mx-auto flex h-8 w-8 items-center justify-center rounded-full text-[10px] font-bold transition disabled:cursor-not-allowed" x-text="date?.day"></button>
                    </span>
                </template>
            </div>

            <div x-show="calendarMode === 'day'" class="flex min-h-40 flex-col items-center justify-center rounded-xl bg-white p-4 text-center">
                <span class="text-4xl font-black text-teal-700" x-text="selectedCalendarDate ? Number(selectedCalendarDate.split('-')[2]) : '—'"></span>
                <span class="mt-1 text-xs font-semibold text-slate-700" x-text="selectedCalendarDateLabel"></span>
                <span class="mt-2 rounded-full bg-emerald-100 px-3 py-1 text-[10px] font-bold text-emerald-800" x-show="slotsForSelectedDate.length">Ada slot tutor</span>
            </div>

            <p class="mt-3 text-center text-[10px] leading-relaxed text-slate-500">Pilih hari yang cocok. Sesi pertama dijadwalkan pada hari terdekat setelah pembayaran, lalu berulang tiap minggu untuk paket 4 sesi.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 text-center">
                <h3 class="text-xs font-extrabold capitalize text-slate-900" x-text="selectedCalendarDateLabel"></h3>
                <p class="mt-1 text-[10px] text-slate-500" x-text="Number(sessionPackage) === 4 ? 'Pilih jam yang sama setiap minggu untuk 4 sesi.' : 'Pilih salah satu jam yang tersedia.'"></p>
            </div>

            <p x-show="slotsLoading" class="rounded-lg bg-slate-50 p-3 text-center text-xs text-slate-500">Memeriksa ketersediaan jadwal…</p>
            <p x-show="!loading && !selectedCalendarDate" class="rounded-lg bg-amber-50 p-3 text-center text-xs text-amber-900">Belum ada tanggal yang cocok dengan ketersediaan tutor.</p>
            <p x-show="!loading && selectedCalendarDate && slotsForSelectedDate.length === 0" class="rounded-lg bg-slate-50 p-3 text-center text-xs text-slate-600">Tidak ada jam tersedia pada tanggal ini. Silakan pilih tanggal lain.</p>

            <div class="space-y-2">
                <template x-for="slot in slotsForSelectedDate" :key="slot.slot_id">
                    <button type="button" @click="selectedScheduleId = slot.slot_id" :aria-pressed="selectedScheduleId === slot.slot_id" :class="selectedScheduleId === slot.slot_id ? 'border-2 border-slate-900 bg-teal-600 text-white' : 'border border-slate-300 bg-white text-slate-800 hover:border-teal-700 hover:bg-teal-50'" class="w-full rounded-xl px-4 py-3 text-left transition">
                        <span class="flex items-center justify-between gap-3"><span class="text-xs font-extrabold" x-text="`${slot.start_time}–${slot.end_time} WITA`"></span><span x-show="selectedScheduleId === slot.slot_id" class="rounded-full bg-white/20 px-2 py-1 text-[9px] font-bold">✓ DIPILIH</span></span>
                        <span class="mt-1 block text-[10px] opacity-80" x-text="Number(sessionPackage) === 4 ? 'Berulang setiap minggu' : 'Satu kali pertemuan'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <div class="mt-5 flex flex-col-reverse items-stretch justify-between gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center">
        <button type="button" @click="step = 2" class="rounded-xl border border-slate-900 bg-white px-4 py-2.5 text-xs font-bold text-slate-900 hover:bg-slate-50">← Kembali ke sesi & tutor</button>
        <button type="button" @click="openPayment()" :disabled="!selectedScheduleId || slotsLoading" class="rounded-xl border-2 border-slate-900 bg-amber-400 px-5 py-3 text-xs font-extrabold text-slate-900 shadow-sm transition hover:bg-amber-500 disabled:cursor-not-allowed disabled:opacity-50">Lanjut ke rincian pembayaran →</button>
    </div>
</section>
