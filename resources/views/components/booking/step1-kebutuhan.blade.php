<div x-show="currentStep === 1" x-cloak class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Tentukan Kebutuhan Belajar & Jenjang Siswa</h2>
        <p class="text-xs text-slate-500 mt-1">Pilih jenjang sekolah, mata pelajaran, dan preferensi metode belajar untuk menyaring rekomendasi guru terbaik.</p>
    </div>

    <!-- 1. JENJANG PENDIDIKAN -->
    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">● 1. PILIH JENJANG PENDIDIKAN</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <template x-for="level in levels" :key="level.id">
                <div @click="form.education_level = level.id" 
                     :class="form.education_level === level.id ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'"
                     class="p-4 rounded-2xl cursor-pointer transition hover:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="level.tag"></span>
                    <h4 class="font-bold text-sm text-slate-900 mt-1" x-text="level.title"></h4>
                    <p class="text-[11px] text-slate-500 mt-1" x-text="level.desc"></p>
                </div>
            </template>
        </div>
    </div>

    <!-- 2. MATA PELAJARAN SPESIFIK -->
    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">● 2. PILIH MATA PELAJARAN SPESIFIK</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <template x-for="subject in subjects" :key="subject.name">
                <div @click="form.subject_name = subject.name" 
                     :class="form.subject_name === subject.name ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'"
                     class="p-3 rounded-2xl cursor-pointer transition flex items-center gap-3">
                    <span class="p-2 bg-slate-100 rounded-xl text-base" x-text="subject.icon"></span>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900" x-text="subject.name"></h4>
                        <p class="text-[10px] text-slate-400" x-text="subject.sub"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- 3. PREFERENSI METODE BELAJAR -->
    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">● 3. PREFERENSI METODE BELAJAR</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div @click="form.learning_method = 'home_visit'" 
                 :class="form.learning_method === 'home_visit' ? 'bg-white border-2 border-teal-600 ring-2 ring-teal-600/20' : 'bg-white border border-slate-300'"
                 class="p-5 rounded-2xl cursor-pointer transition relative">
                <div class="flex items-start gap-4">
                    <span class="p-3 bg-amber-100 rounded-2xl text-2xl">🏡</span>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Home Visit (Tutor Datang ke Rumah)</h4>
                        <p class="text-xs text-slate-500 mt-1">Guru privat hadir langsung ke alamat siswa. Belajar lebih intensif, terarah, dan terpantau orang tua.</p>
                        <div class="flex gap-2 mt-3">
                            <span class="text-[10px] font-bold px-2 py-1 bg-slate-100 rounded-md text-slate-600">👥 Maks. 5 Siswa / Skuad</span>
                            <span class="text-[10px] font-bold px-2 py-1 bg-teal-50 text-teal-700 rounded-md">📍 GPS Geotagging Terintegrasi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div @click="form.learning_method = 'online_zoom'" 
                 :class="form.learning_method === 'online_zoom' ? 'bg-white border-2 border-teal-600 ring-2 ring-teal-600/20' : 'bg-white border border-slate-300'"
                 class="p-5 rounded-2xl cursor-pointer transition relative">
                <div class="flex items-start gap-4">
                    <span class="p-3 bg-slate-100 rounded-2xl text-2xl">💻</span>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Online Zoom (Tatap Muka Virtual)</h4>
                        <p class="text-xs text-slate-500 mt-1">Interaksi dua arah melalui HD Video Conference, rekaman sesi cloud, dan papan tulis digital interaktif.</p>
                        <div class="flex gap-2 mt-3">
                            <span class="text-[10px] font-bold px-2 py-1 bg-slate-100 rounded-md text-slate-600">📹 Termasuk Cloud Recording</span>
                            <span class="text-[10px] font-bold px-2 py-1 bg-slate-100 rounded-md text-slate-600">🌐 Akses Seluruh Indonesia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-slate-200 gap-4">
        <div class="text-xs font-semibold px-4 py-2 bg-slate-100 border border-slate-900 rounded-full text-slate-800">
            ● Pilihan saat ini: <span x-text="form.education_level + ' • ' + form.subject_name + ' • ' + (form.learning_method === 'home_visit' ? 'Home Visit' : 'Online Zoom')"></span>
        </div>
        <button @click="fetchTutors(); goToStep(2)" class="w-full sm:w-auto bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full flex items-center justify-center gap-2 shadow-sm transition">
            Lanjut Cari Tutor Tersedia ➔
        </button>
    </div>
</div>