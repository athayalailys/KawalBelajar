@props(['guru', 'schedules'])

<div class="space-y-6">
    <div class="space-y-1">
        <h2 class="font-heading text-2xl font-black">Pilih Jadwal Sesi Belajar</h2>
        <p class="text-xs opacity-70">Pilih hari dan jam yang tersedia sesuai slot tutor</p>
    </div>

    <!-- Tipe Schedule -->
    <div class="grid grid-cols-2 gap-4 text-xs font-bold">
        <button type="button" class="p-4 bg-brand-yellow/20 border border-brand-yellow rounded-brand text-left">
            <span class="block font-bold">Flexible Schedule</span>
            <span class="text-[10px] opacity-70 font-normal">Pilih slot jam berbeda per minggu</span>
        </button>
        <button type="button" class="p-4 bg-brand-white border border-brand-light rounded-brand text-left opacity-60">
            <span class="block font-bold">Fixed Schedule</span>
            <span class="text-[10px] opacity-70 font-normal">Hari dan jam sama tiap minggu</span>
        </button>
    </div>

    <!-- Slot Hari & Jam -->
    <div class="bg-brand-white border border-brand-light p-5 rounded-brand-xl space-y-3">
        <h4 class="font-bold text-xs">Pilih Hari & Jam yang Tersedia:</h4>
        <div class="flex flex-wrap gap-2 text-xs">
            @forelse($schedules as $jadwal)
                <button type="button" class="px-4 py-2 rounded-full border border-brand-dark font-bold hover:bg-brand-yellow transition">
                    {{ $jadwal->jadwal_mulai->format('D, H:i') }} WITA
                </button>
            @empty
                <button type="button" class="px-4 py-2 rounded-full bg-brand-yellow font-bold border border-brand-dark">Jumat, 18:00</button>
                <button type="button" class="px-4 py-2 rounded-full border border-brand-dark font-bold">Sabtu, 17:00</button>
                <button type="button" class="px-4 py-2 rounded-full border border-brand-dark font-bold">Minggu, 17:00</button>
            @endforelse
        </div>
    </div>

    <a href="?step=5&guru_id={{ $guru->guru_id }}" class="w-full block text-center bg-brand-yellow hover:bg-brand-yellow-hover font-bold text-sm py-3.5 rounded-full border border-brand-dark transition">
        Lanjutkan Pemesanan →
    </a>
</div>