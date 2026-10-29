<div x-show="currentStep === 4" x-cloak class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Kunci Slot Jadwal Belajar Bebas Bentrok</h2>
        <p class="text-xs text-slate-500 mt-1">Slot yang dipilih otomatis terkunci di kalender belajar rutin 4 pekan.</p>
    </div>

    <!-- DISPLAY SLOT TERKUNCI -->
    <div class="p-4 bg-slate-50 border border-slate-900 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="p-3 bg-amber-400 border border-slate-900 rounded-xl">📅</span>
            <div>
                <span class="text-[10px] font-bold px-2 py-0.5 bg-teal-600 text-white rounded-md">TERKUNCI & TERPILIH</span>
                <h4 class="font-bold text-sm text-slate-900 mt-1" x-text="selectedSlot ? selectedSlot.day_of_week + ', ' + selectedSlot.start_time + ' WITA' : 'Pilih slot pada kalender di bawah'"></h4>
            </div>
        </div>
        <div class="text-right text-xs">
            <span class="font-bold text-slate-900" x-text="selectedTutor?.full_name"></span>
            <p class="text-slate-400 text-[10px]">Durasi 90 Menit • Rutin 4 Pekan</p>
        </div>
    </div>

    <!-- KALENDER GRID INTERAKTIF -->
    <div class="border border-slate-300 rounded-2xl p-4 bg-white overflow-x-auto">
        <h4 class="font-bold text-xs text-slate-900 mb-4">Kalender Mengajar & Ketersediaan Tutor</h4>
        <div class="grid grid-cols-8 text-center text-xs gap-2 min-w-[600px]">
            <div class="font-bold text-slate-400">WAKTU</div>
            <div class="font-bold text-slate-700">SEN</div>
            <div class="font-bold text-slate-700">SEL</div>
            <div class="font-bold text-slate-700">RAB</div>
            <div class="font-bold text-slate-700">KAM</div>
            <div class="font-bold text-teal-700 bg-teal-50 py-1 rounded-md">JUM</div>
            <div class="font-bold text-slate-700">SAB</div>
            <div class="font-bold text-slate-700">MIN</div>

            <template x-for="slot in slots" :key="slot.slot_id">
                <div class="col-span-8 grid grid-cols-8 items-center gap-2 py-2 border-t border-slate-100">
                    <div class="text-[10px] font-bold text-slate-500" x-text="slot.start_time + ' - ' + slot.end_time"></div>
                    <template x-for="day in ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu']" :key="day">
                        <div>
                            <button @click="if(slot.is_available) selectedSlot = slot" 
                                    :disabled="!slot.is_available"
                                    :class="{
                                        'bg-teal-600 text-white font-bold border-2 border-slate-900': selectedSlot?.slot_id === slot.slot_id && slot.day_of_week === day,
                                        'bg-slate-100 text-slate-400 cursor-not-allowed line-through': !slot.is_available && slot.day_of_week === day,
                                        'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100': slot.is_available && slot.day_of_week === day
                                    }"
                                    class="w-full py-2 text-[10px] rounded-lg transition">
                                <span x-text="slot.day_of_week === day ? (slot.is_available ? 'Kosong' : 'Terisi') : '-'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
        <button @click="goToStep(3)" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">
            ← Kembali ke Sesi & Skuad
        </button>
        <button @click="if(selectedSlot) goToStep(5)" :disabled="!selectedSlot" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full text-xs shadow-sm disabled:opacity-50">
            Lanjut ke Rincian Pembayaran & Checkout (Langkah 5) ➔
        </button>
    </div>
</div>