<section x-show="step === 3" x-cloak class="rounded-3xl border-2 border-slate-900 bg-white p-5 md:p-7">
    <div class="mb-5">
        <h2 class="text-lg font-extrabold text-slate-900 md:text-xl">Pilih jenis paket bimbingan & mode kelompok</h2>
        <p class="mt-1 text-xs text-slate-500">Pilih jumlah sesi dan cara belajar bersama tutor pilihanmu.</p>
    </div>

    <div>
        <h3 class="mb-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-600">Pilih jumlah sesi</h3>
        <div class="grid gap-3 md:grid-cols-2">
            <button type="button" @click="sessionPackage = 1; calculatePricing()" :aria-pressed="Number(sessionPackage) === 1" :class="Number(sessionPackage) === 1 ? 'border-2 border-slate-900 bg-white ring-1 ring-slate-200' : 'border border-slate-300 bg-white hover:border-slate-700'" class="rounded-2xl p-4 text-left transition">
                <div class="flex items-start justify-between gap-3"><div><span class="rounded bg-slate-100 px-2 py-1 text-[9px] font-bold text-slate-700">FLEKSIBEL</span><h4 class="mt-2 text-sm font-extrabold text-slate-900">1 Sesi Instan</h4><p class="mt-1 text-[11px] text-slate-500">Satu pertemuan untuk kebutuhan belajarmu.</p></div><span class="mt-1 h-4 w-4 rounded-full border-2" :class="Number(sessionPackage) === 1 ? 'border-teal-600 bg-teal-600 ring-2 ring-teal-100' : 'border-slate-400'"></span></div>
                <div class="mt-3 border-t border-slate-200 pt-2 text-xs font-extrabold text-slate-900" x-text="pricing && Number(sessionPackage) === 1 ? `${rupiah(pricing.total_amount)} total` : (selectedPackage ? `${rupiah(selectedPackage.teacher_rate_per_session)} / sesi` : 'Tarif mengikuti pilihan tutor')"></div>
            </button>

            <button type="button" @click="sessionPackage = 4; calculatePricing()" :aria-pressed="Number(sessionPackage) === 4" :class="Number(sessionPackage) === 4 ? 'border-2 border-teal-700 bg-cyan-50 ring-1 ring-teal-100' : 'border border-slate-300 bg-white hover:border-teal-600'" class="rounded-2xl p-4 text-left transition">
                <div class="flex items-start justify-between gap-3"><div><span class="rounded bg-teal-600 px-2 py-1 text-[9px] font-bold text-white">PAKET RUTIN</span><h4 class="mt-2 text-sm font-extrabold text-slate-900">Paket 4 Sesi Bulanan</h4><p class="mt-1 text-[11px] text-slate-500">Empat pertemuan di hari dan jam yang sama setiap minggu.</p></div><span class="mt-1 h-4 w-4 rounded-full border-2" :class="Number(sessionPackage) === 4 ? 'border-teal-600 bg-teal-600 ring-2 ring-teal-100' : 'border-slate-400'"></span></div>
                <div class="mt-3 border-t border-slate-200 pt-2 text-xs font-extrabold text-slate-900" x-text="pricing && Number(sessionPackage) === 4 ? `${rupiah(pricing.total_amount)} / 4 sesi` : (selectedPackage ? `${rupiah(selectedPackage.teacher_rate_per_session * 4)} / 4 sesi` : 'Tarif mengikuti pilihan tutor')"></div>
            </button>
        </div>
    </div>

    <div class="mt-5">
        <h3 class="mb-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-600">Pilih cara bergabung</h3>
        <div class="space-y-2">
            <button type="button" @click="squadOption = 'join_squad'; initializeBookingCalendar(); calculatePricing()" :aria-pressed="squadOption === 'join_squad'" :class="squadOption === 'join_squad' ? 'border-2 border-slate-900 bg-cyan-50' : 'border border-slate-300 bg-white hover:border-slate-700'" class="w-full rounded-xl p-3 text-left transition">
                <span class="flex items-start gap-2"><span class="mt-0.5 text-teal-700" x-text="squadOption === 'join_squad' ? '⦿' : '◯'"></span><span class="min-w-0 flex-1"><span class="flex flex-wrap items-center gap-2 text-xs font-extrabold text-slate-900">Gabung kelompok belajar <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] text-emerald-800">HEMAT</span></span><span class="mt-1 block text-[10px] text-slate-500">Masukkan kode kelompok dari teman atau penyelenggara.</span></span><span class="whitespace-nowrap text-[10px] font-bold text-teal-800" x-show="pricing && squadOption === 'join_squad'" x-text="pricing ? `${rupiah(pricing.final_price_per_session)} / sesi` : ''"></span></span>
                <div x-show="squadOption === 'join_squad'" class="mt-3 pl-5"><label class="block text-[10px] font-bold text-slate-700">Kode kelompok<input type="text" x-model="squadCode" @click.stop @input="initializeBookingCalendar()" @change="calculatePricing()" placeholder="Contoh: SQD-FISIKA-094" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-teal-600 focus:outline-none"></label></div>
            </button>

            <button type="button" @click="squadOption = 'create_squad'; initializeBookingCalendar(); calculatePricing()" :aria-pressed="squadOption === 'create_squad'" :class="squadOption === 'create_squad' ? 'border-2 border-slate-900 bg-amber-50' : 'border border-slate-300 bg-white hover:border-slate-700'" class="w-full rounded-xl p-3 text-left transition">
                <span class="flex items-start gap-2"><span class="mt-0.5 text-teal-700" x-text="squadOption === 'create_squad' ? '⦿' : '◯'"></span><span class="min-w-0 flex-1"><span class="flex flex-wrap items-center gap-2 text-xs font-extrabold text-slate-900">Buat kelompok baru <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[9px] text-amber-900">BAGIKAN KODE</span></span><span class="mt-1 block text-[10px] text-slate-500">Ajak teman belajar bersama dan berbagi biaya.</span></span><span class="whitespace-nowrap text-[10px] font-bold text-teal-800" x-show="pricing && squadOption === 'create_squad'" x-text="pricing ? `${rupiah(pricing.final_price_per_session)} / sesi` : ''"></span></span>
                <div x-show="squadOption === 'create_squad'" class="mt-3 pl-5"><label class="block text-[10px] font-bold text-slate-700">Kapasitas kelompok (maksimal <span x-text="maxCapacity"></span> pembelajar)<input type="number" min="2" :max="maxCapacity" x-model.number="targetCapacity" @click.stop @change="calculatePricing()" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-xs"></label></div>
            </button>

            <button type="button" @click="squadOption = 'solo'; initializeBookingCalendar(); calculatePricing()" :aria-pressed="squadOption === 'solo'" :class="squadOption === 'solo' ? 'border-2 border-slate-900 bg-white' : 'border border-slate-300 bg-white hover:border-slate-700'" class="w-full rounded-xl p-3 text-left transition">
                <span class="flex items-start gap-2"><span class="mt-0.5 text-teal-700" x-text="squadOption === 'solo' ? '⦿' : '◯'"></span><span class="min-w-0 flex-1"><span class="block text-xs font-extrabold text-slate-900">Privat solo</span><span class="mt-1 block text-[10px] text-slate-500">Sesi khusus untuk satu pembelajar.</span></span><span class="whitespace-nowrap text-[10px] font-bold text-slate-700" x-show="selectedPackage" x-text="selectedPackage ? `${rupiah(selectedPackage.teacher_rate_per_session)} / sesi` : ''"></span></span>
            </button>
        </div>
    </div>

    <div x-show="squadOption !== 'solo' && pricing" class="flex items-center gap-3 rounded-xl border-2 border-slate-900 bg-amber-50 p-3 text-[11px] text-slate-800">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-400 text-lg">💡</span>
        <span><strong>Lebih hemat bersama kelompok.</strong> Potongan saat ini <strong class="text-teal-800" x-text="pricing ? `${pricing.discount_percentage}%` : ''"></strong>; tarif akhir dihitung berdasarkan paket dan kapasitas yang dipilih.</span>
    </div>
</section>
