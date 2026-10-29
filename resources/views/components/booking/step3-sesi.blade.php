<div x-show="currentStep === 3" x-cloak class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Pilih Jenis Paket Bimbingan & Mode Squad Patungan</h2>
        <p class="text-xs text-slate-500 mt-1">Pilih belajar mandiri atau bergabung dengan squad patungan teman untuk hemat biaya hingga 35%.</p>
    </div>

    <!-- PILIHAN A: PAKET BELAJAR -->
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">PILIHAN A: PAKET BELAJAR</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div @click="form.session_package = 1; calculatePricing()" 
                 :class="form.session_package === 1 ? 'border-2 border-slate-900 bg-white' : 'border border-slate-300 bg-white'"
                 class="p-5 rounded-2xl cursor-pointer transition">
                <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 rounded-md">Fleksibel</span>
                <h4 class="font-bold text-base text-slate-900 mt-2">1 Sesi Instan</h4>
                <p class="text-xs text-slate-500 mt-1">60 menit fokus tuntas tugas harian, bedah kisi-kisi ulangan.</p>
                <div class="mt-4 font-bold text-sm text-slate-900">Rp 75.000 <span class="text-xs font-normal text-slate-400">/ sesi</span></div>
            </div>

            <div @click="form.session_package = 4; calculatePricing()" 
                 :class="form.session_package = 4 ? 'border-2 border-teal-600 bg-teal-50/20' : 'border border-slate-300 bg-white'"
                 class="p-5 rounded-2xl cursor-pointer transition relative">
                <span class="text-[10px] font-bold px-2 py-0.5 bg-teal-600 text-white rounded-md">Terpopuler</span>
                <h4 class="font-bold text-base text-slate-900 mt-2">Paket 4 Sesi Intensif Bulanan</h4>
                <p class="text-xs text-slate-500 mt-1">Garansi Rapor Belajar & Studio Kuis. 4x pertemuan terjadwal rutin per bulan.</p>
                <div class="mt-4 font-bold text-sm text-slate-900">Rp 225.000 <span class="text-xs font-semibold text-teal-700 bg-teal-100 px-2 py-0.5 rounded-full">Hemat Rp 75rb</span></div>
            </div>
        </div>
    </div>

    <!-- PILIHAN B: MODEL PEMESANAN -->
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">PILIHAN B: MODEL PEMESANAN (PATUNGAN SKUAD VS PRIVAT TUNGGAL)</h3>
        <div class="space-y-3">
            <div @click="form.format = 'joiner'; calculatePricing()" 
                 :class="form.format === 'joiner' ? 'border-2 border-slate-900 bg-amber-50/30' : 'border border-slate-300 bg-white'"
                 class="p-4 rounded-2xl cursor-pointer transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="radio" name="format" :checked="form.format === 'joiner'" class="accent-amber-500">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Gabung Squad Patungan Terdaftar</h4>
                            <p class="text-xs text-slate-500">Bergabung dengan teman. Belajar tetap efektif maksimal 3-5 siswa.</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-teal-700">Rp 56.250 <span class="text-[10px] font-normal text-slate-400">/sesi</span></span>
                </div>
                <div x-show="form.format === 'joiner'" class="mt-3 p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between">
                    <input type="text" x-model="form.squad_code" placeholder="Masukkan Kode Squad (cth: SQD-FISIKA-094)" class="text-xs w-full focus:outline-none">
                </div>
            </div>

            <div @click="form.format = 'host'; calculatePricing()" 
                 :class="form.format === 'host' ? 'border-2 border-slate-900 bg-amber-50/30' : 'border border-slate-300 bg-white'"
                 class="p-4 rounded-2xl cursor-pointer transition flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <input type="radio" name="format" :checked="form.format === 'host'" class="accent-amber-500">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Buat Skuad Baru (Inisiator & Bagikan Kode)</h4>
                        <p class="text-xs text-slate-500">Bentuk kelompok belajarmu sendiri dan dapatkan potongan harga patungan.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-slate-900">Rp 56.250 <span class="text-[10px] font-normal text-slate-400">/sesi</span></span>
            </div>

            <div @click="form.format = 'solo'; calculatePricing()" 
                 :class="form.format === 'solo' ? 'border-2 border-slate-900 bg-amber-50/30' : 'border border-slate-300 bg-white'"
                 class="p-4 rounded-2xl cursor-pointer transition flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <input type="radio" name="format" :checked="form.format === 'solo'" class="accent-amber-500">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Sesi Privat Solo (1 Siswa Eksklusif)</h4>
                        <p class="text-xs text-slate-500">Perhatian 100% dari tutor khusus privat tanpa teman patungan.</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-slate-900">Rp 75.000 <span class="text-[10px] font-normal text-slate-400">/sesi</span></span>
            </div>
        </div>
    </div>

    <!-- BANNER KALKULATOR -->
    <div class="p-4 bg-amber-100/70 border border-amber-300 rounded-2xl flex items-center gap-3 text-xs text-slate-800">
        <span class="p-2 bg-amber-400 rounded-xl">💡</span>
        <span><strong>Kalkulator Penghematan Siswa Aktif:</strong> Kamu menghemat <strong class="text-teal-700">Rp 18.750 per sesi</strong> dengan bergabung ke Squad!</span>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
        <button @click="goToStep(2)" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">
            ← Kembali ke Pilih Tutor
        </button>
        <button @click="fetchSlots(); goToStep(4)" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full text-xs shadow-sm">
            Lanjut Kunci Jadwal Rutin (Langkah 4) ➔
        </button>
    </div>
</div>