@props(['pakets'])

<div class="space-y-6">
    <div class="space-y-1">
        <h2 class="font-heading text-2xl font-black">Tentukan Kebutuhan Belajarmu</h2>
        <p class="text-xs opacity-70">Sistem akan menyaring tutor terbaik sesuai kriteria yang kamu pilih</p>
    </div>

    <!-- Mode Durasi -->
    <div class="p-1.5 border border-brand-teal/30 rounded-brand-xl flex gap-2">
        <button type="button" class="flex-1 py-3 bg-brand-teal text-brand-white font-bold rounded-brand text-xs">Paket 1 Bulan (4+ Sesi)</button>
        <button type="button" class="flex-1 py-3 font-bold rounded-brand text-xs opacity-60">1 Sesi Pembelajaran</button>
    </div>

    <!-- Pilih Jenjang -->
    <div class="bg-brand-white/80 border border-brand-light p-5 rounded-brand space-y-3">
        <h4 class="font-bold text-xs uppercase tracking-wider text-brand-teal">Pilih Jenjangmu</h4>
        <div class="flex gap-2">
            @foreach(['SD', 'SMP', 'SMA', 'UMUM'] as $jenjang)
                <button type="button" class="px-5 py-2 rounded-full border border-brand-light text-xs font-bold hover:bg-brand-teal hover:text-brand-white transition">
                    {{ $jenjang }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Metode Belajar -->
    <div class="bg-brand-white/80 border border-brand-light p-5 rounded-brand space-y-3">
        <h4 class="font-bold text-xs uppercase tracking-wider text-brand-teal">Metode Belajar</h4>
        <div class="grid grid-cols-2 gap-3 text-xs font-bold">
            <button type="button" class="py-3 rounded-full border border-brand-light opacity-60">Online (Zoom)</button>
            <button type="button" class="py-3 rounded-full bg-brand-dark text-brand-white">HomeVisit (Datang ke Rumah)</button>
        </div>
    </div>

    <a href="?step=2" class="w-full block text-center bg-brand-yellow hover:bg-brand-yellow-hover font-bold text-sm py-3.5 rounded-full border border-brand-dark shadow-sm transition">
        Tampilkan Tutor yang Tersedia →
    </a>
</div>