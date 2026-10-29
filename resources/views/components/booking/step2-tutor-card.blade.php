<div x-show="currentStep === 2" x-cloak class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pilih Tutor Terverifikasi Sesuai Preferensi</h2>
            <p class="text-xs text-slate-500 mt-1">Tutor dengan kurikulum tervalidasi siap mendampingi sesi belajar Anda.</p>
        </div>
        <span class="text-xs font-bold px-3 py-1.5 bg-teal-50 text-teal-800 rounded-full border border-teal-200 w-fit">
            ● Mapel: <span x-text="form.subject_name + ' ' + form.education_level"></span>
        </span>
    </div>

    <!-- SEARCH & FILTER -->
    <div class="flex flex-col sm:flex-row gap-3">
        <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchTutors()" placeholder="Cari nama tutor..." class="flex-1 px-4 py-2.5 rounded-full border border-slate-300 text-sm focus:outline-none focus:border-slate-900">
        <select class="px-4 py-2.5 rounded-full border border-slate-300 text-sm font-semibold text-slate-700 bg-white">
            <option>Rating Tertinggi (4.8+)</option>
            <option>Harga Terendah</option>
        </select>
    </div>

    <!-- KATALOG TUTOR LIST -->
    <div class="space-y-4">
        <template x-for="tutor in tutors" :key="tutor.teacher_id">
            <div @click="selectedTutor = tutor" 
                 :class="selectedTutor?.teacher_id === tutor.teacher_id ? 'border-2 border-slate-900 bg-teal-50/30' : 'border border-slate-300 bg-white'"
                 class="p-5 rounded-2xl cursor-pointer transition flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-slate-800">
                
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-amber-200 rounded-2xl flex items-center justify-center font-bold text-xl border border-slate-900" x-text="tutor.full_name.charAt(0)"></div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base text-slate-900" x-text="tutor.full_name"></h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md">Terverifikasi</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Alumni PTN / Praktisi Ahli</p>
                        <div class="flex items-center gap-3 mt-2 text-xs font-semibold text-slate-600">
                            <span>⭐ <strong class="text-slate-900" x-text="tutor.rating"></strong>/5.0</span>
                            <span>📚 120+ Jam Mengajar</span>
                        </div>
                    </div>
                </div>

                <div class="text-left md:text-right">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Tarif Patungan</span>
                    <div class="text-lg font-extrabold text-teal-700">Mulai Rp <span x-text="tutor.starting_price.toLocaleString('id-ID')"></span></div>
                    <span class="text-[10px] text-slate-400">/sesi per siswa</span>
                </div>
            </div>
        </template>
    </div>

    <!-- SELECTED TUTOR SUMMARY BAR -->
    <div x-show="selectedTutor" class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl flex items-center justify-between text-xs text-emerald-900 font-semibold">
        <span>🟢 Tutor Terpilih: <strong x-text="selectedTutor?.full_name"></strong> (Terverifikasi Dikbud & SKCK)</span>
        <span class="px-3 py-1 bg-white border border-emerald-300 rounded-full font-bold text-[10px]">Garansi Guru Pengganti</span>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
        <button @click="goToStep(1)" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">
            ← Kembali ke Preferensi
        </button>
        <button @click="if(selectedTutor) goToStep(3)" :disabled="!selectedTutor" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full text-xs shadow-sm disabled:opacity-50">
            Lanjut ke Pilih Sesi & Skuad (Langkah 3) ➔
        </button>
    </div>
</div>