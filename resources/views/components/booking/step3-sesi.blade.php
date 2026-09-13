@props(['guru', 'paket'])

<div class="space-y-6">
    <div class="bg-brand-white border border-brand-light p-5 rounded-brand-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ $guru->avatar_url }}" class="w-12 h-12 rounded-brand">
            <div>
                <h4 class="font-bold text-sm">{{ $guru->user->nama_lengkap }}</h4>
                <p class="text-xs opacity-60">HomeVisit (Datang ke Rumah)</p>
            </div>
        </div>
        <span class="bg-brand-yellow/20 text-xs font-bold px-3 py-1 rounded-full">Paket 1 Bulan</span>
    </div>

    <!-- Live Price Summary Calculator -->
    <div class="bg-brand-light/30 border border-brand-teal/20 p-5 rounded-brand space-y-2">
        <span class="text-xs font-bold text-brand-teal uppercase tracking-wider block">Rincian Biaya Transparan</span>
        <div class="flex justify-between items-end">
            <div>
                <span class="text-xs opacity-70 block">Biaya Akun Pribadimu:</span>
                <span class="font-heading text-2xl font-black text-brand-teal">Rp {{ number_format($paket->harga_dasar ?? 350000, 0, ',', '.') }}</span>
            </div>
            <span class="text-xs font-bold bg-brand-yellow px-3 py-1 rounded-full">Hemat 15% Squad</span>
        </div>
    </div>

    <a href="?step=4&guru_id={{ $guru->guru_id }}&paket_id={{ $paket->paket_id }}" class="w-full block text-center bg-brand-yellow hover:bg-brand-yellow-hover font-bold text-sm py-3.5 rounded-full border border-brand-dark transition">
        Lanjut Pilih Jadwal →
    </a>
</div>