<div x-show="currentStep === 5" x-cloak class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Konfirmasi Pemesanan & Rincian Pembayaran</h2>
        <p class="text-xs text-slate-500 mt-1">Periksa kembali ringkasan pesanan bimbingan privat Anda sebelum melanjutkan ke pembayaran.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- RINGKASAN BIMBINGAN -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase">📚 RINGKASAN BIMBINGAN BELAJAR</h3>
            
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase">PAKET & LOKASI</span>
                <div class="flex items-center justify-between mt-0.5">
                    <h4 class="font-bold text-sm text-slate-900" x-text="form.subject_name + ' • ' + form.education_level"></h4>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-amber-100 text-amber-800 rounded-md">Privat 1-on-1</span>
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase">TUTOR PENDAMPING</span>
                <h4 class="font-bold text-sm text-slate-900" x-text="selectedTutor?.full_name || 'Tutor Terpilih'"></h4>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                <span class="text-[10px] text-slate-400 uppercase">JADWAL RUTIN TERPILIH</span>
                <h4 class="font-bold text-sm text-slate-900" x-text="selectedSlot ? selectedSlot.day_of_week + ', ' + selectedSlot.start_time + ' WITA' : '-' "></h4>
            </div>
        </div>

        <!-- RINCIAN PEMBAYARAN -->
        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-300 space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase">🧾 RINCIAN PEMBAYARAN</h3>
            
            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Bimbingan Privat (4 Sesi)</span>
                    <span class="font-bold text-slate-900" x-text="'Rp ' + Number(form.base_price || 300000).toLocaleString('id-ID')"></span>
                </div>

                <div class="flex justify-between text-slate-600">
                    <span>Biaya Pemeliharaan & Bank Soal</span>
                    <span class="font-semibold text-slate-900">Rp 5.000</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-slate-900 block">TOTAL TAGIHAN</span>
                    <span class="text-[10px] text-slate-500">Termasuk pajak & platform fee</span>
                </div>
                <span class="text-xl font-extrabold text-emerald-700" x-text="'Rp ' + Number((form.base_price || 300000) + 5000).toLocaleString('id-ID')"></span>
            </div>

            <button @click="submitCheckout()" :disabled="isSubmitting" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-full text-xs shadow-md transition disabled:opacity-50">
                <span x-text="isSubmitting ? 'Memproses Transaksi...' : 'Bayar Sekarang via Xendit ➔'"></span>
            </button>
        </div>
    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
        <button @click="goToStep(4)" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100 transition">
            ← Kembali ke Kunci Jadwal
        </button>
    </div>
</div>