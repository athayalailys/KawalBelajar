<div x-show="isPaymentOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="payment-dialog-title" @keydown.escape.window="closePayment()">
    <button type="button" class="absolute inset-0 h-full w-full cursor-default bg-slate-950/45 backdrop-blur-sm" aria-label="Tutup rincian pembayaran" @click="closePayment()"></button>

    <section x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-4 scale-95 opacity-0" x-transition:enter-end="translate-y-0 scale-100 opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0 scale-100 opacity-100" x-transition:leave-end="translate-y-4 scale-95 opacity-0" class="relative z-10 flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border-2 border-slate-900 bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-7">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700">Langkah akhir · Pembayaran</p>
                <h2 id="payment-dialog-title" class="mt-1 text-lg font-extrabold text-slate-900 sm:text-2xl">Konfirmasi pesanan dan rincian pembayaran</h2>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">Periksa kembali pesanan sebelum membuat tagihan.</p>
            </div>
            <button type="button" @click="closePayment()" aria-label="Kembali ke jadwal" class="ml-4 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xl text-slate-700 transition hover:bg-slate-100">×</button>
        </header>

        <div class="grid flex-1 gap-5 overflow-y-auto p-5 md:grid-cols-[1.2fr_0.8fr] md:gap-6 md:p-7">
            <section class="space-y-3 rounded-2xl border-2 border-slate-900 p-4 sm:p-5">
                <h3 class="border-b border-slate-300 pb-3 text-sm font-extrabold uppercase tracking-wide text-slate-900">📚 Ringkasan bimbingan belajar</h3>
                <div class="rounded-xl border border-slate-300 bg-slate-50 p-3">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Mata pelajaran dan jenjang</span>
                    <div class="mt-1 flex flex-wrap items-center justify-between gap-2"><h4 class="text-sm font-bold text-slate-900" x-text="`${subject} · ${educationLevel}`"></h4><span class="rounded-md bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-900" x-text="squadOption === 'solo' ? 'Privat' : 'Kelompok belajar'"></span></div>
                </div>
                <div class="rounded-xl border border-slate-300 bg-slate-50 p-3">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Tutor dan paket</span>
                    <h4 class="mt-1 text-sm font-bold text-slate-900" x-text="`${selectedTutor?.full_name || 'Tutor'} · ${selectedPackage?.package_name || 'Paket'} (${sessionPackage} sesi)`"></h4>
                </div>
                <div class="rounded-xl border border-slate-300 bg-slate-50 p-3">
                    <span class="text-[10px] font-bold uppercase text-slate-500">Jadwal pilihan</span>
                    <h4 class="mt-1 text-sm font-bold text-slate-900" x-text="selectedSchedule ? `Setiap ${dayLabel(selectedSchedule.day_of_week)}, ${selectedSchedule.start_time}–${selectedSchedule.end_time} WITA` : 'Belum dipilih'"></h4>
                    <p class="mt-1 text-xs text-slate-500" x-text="`${sessionPackage} sesi · ${method === 'home_visit' ? 'Tutor datang ke rumah' : 'Belajar daring melalui Zoom'}`"></p>
                </div>
                <div class="rounded-xl border border-teal-200 bg-teal-50 p-3 text-xs leading-relaxed text-teal-950">
                    <strong class="block text-[11px] tracking-wide">🛡️ JAMINAN PEMBAYARAN</strong>
                    Pembayaran diproses melalui Xendit. Status pesanan akan diperbarui setelah pembayaran dikonfirmasi.
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border-2 border-slate-900 p-4 sm:p-5">
                <h3 class="border-b border-slate-300 pb-3 text-sm font-extrabold uppercase tracking-wide text-slate-900">🧾 Rincian pembayaran</h3>
                <div class="rounded-2xl border-2 border-slate-900 bg-amber-300 p-4">
                    <div class="flex items-center justify-between gap-3"><div><span class="block text-[10px] font-extrabold uppercase tracking-wide">Total tagihan</span><span class="text-2xl font-black text-slate-950" x-text="pricing ? rupiah(pricing.total_amount) : 'Menghitung…'"></span></div><span class="rounded-full border border-slate-900 bg-white px-3 py-1 text-[10px] font-bold" x-text="pricing ? `${rupiah(pricing.total_amount / Math.max(Number(sessionPackage), 1))} / sesi` : '—' "></span></div>
                    <div class="mt-3 flex justify-between border-t border-slate-900/20 pt-2 text-xs"><span x-text="`Biaya bimbingan · ${sessionPackage} sesi`"></span><span class="font-bold" x-text="pricing ? rupiah(pricing.total_amount) : '—'"></span></div>
                    <div class="mt-1 flex justify-between text-xs"><span>Diskon kelompok</span><span class="font-bold text-emerald-900" x-text="pricing ? `${pricing.discount_percentage}%` : '—'"></span></div>
                </div>

                <fieldset>
                    <legend class="mb-2 text-xs font-extrabold uppercase tracking-wide text-slate-800">Pilih metode pembayaran</legend>
                    <div class="space-y-2 text-xs">
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-300 p-3 hover:border-teal-600" :class="paymentMethod === 'qris' ? 'border-2 border-teal-700 bg-teal-50' : ''"><span><input type="radio" x-model="paymentMethod" value="qris" class="mr-2 accent-teal-700"><strong>QRIS</strong><span class="ml-1 text-slate-500">· E-wallet dan mobile banking</span></span><span class="rounded bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-800">Instan</span></label>
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-300 p-3 hover:border-teal-600" :class="paymentMethod === 'virtual_account' ? 'border-2 border-teal-700 bg-teal-50' : ''"><span><input type="radio" x-model="paymentMethod" value="virtual_account" class="mr-2 accent-teal-700"><strong>Virtual Account</strong><span class="ml-1 text-slate-500">· Transfer bank</span></span><span class="text-[10px] font-bold text-slate-500">24 jam</span></label>
                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-300 p-3 hover:border-teal-600" :class="paymentMethod === 'credit_card' ? 'border-2 border-teal-700 bg-teal-50' : ''"><span><input type="radio" x-model="paymentMethod" value="credit_card" class="mr-2 accent-teal-700"><strong>Kartu kredit</strong></span><span class="text-[10px] font-bold text-slate-500">Xendit</span></label>
                    </div>
                </fieldset>
                <p x-show="pricingLoading" class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600">Menghitung rincian biaya…</p>
                <p x-show="error" x-cloak class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs text-rose-800" x-text="error"></p>
            </section>
        </div>

        <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <button type="button" @click="closePayment()" class="rounded-xl border-2 border-slate-900 bg-slate-50 px-5 py-3 text-xs font-bold text-slate-900 hover:bg-slate-100">← Kembali ke jadwal</button>
            <button type="button" @click="placeOrder()" :disabled="submitting || loading || !pricing || !selectedScheduleId" class="rounded-xl border-2 border-slate-900 bg-emerald-500 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50"><span x-text="submitting ? 'Menyiapkan tagihan…' : `Bayar sekarang (${pricing ? rupiah(pricing.total_amount) : '—'}) →`"></span></button>
        </footer>
    </section>
</div>
