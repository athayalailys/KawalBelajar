@props(['tutor', 'paket'])

@php
    $tarif = $tutor->tutorPaketTarif->first()?->tarif_kategori ?? $paket->harga_dasar ?? 300000;
@endphp

<div class="bg-brand-white border border-brand-light p-5 rounded-brand-xl space-y-4 shadow-xs hover:shadow-md transition">
    <div class="flex items-start gap-4">
        <img src="{{ $tutor->avatar_url }}" alt="{{ $tutor->user->nama_lengkap }}" class="w-16 h-16 rounded-brand object-cover bg-brand-yellow/20 flex-shrink-0">
        <div class="space-y-1">
            <h3 class="font-bold text-base">{{ $tutor->user->nama_lengkap }}</h3>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold">
                <span class="bg-brand-light/40 text-brand-teal px-2.5 py-0.5 rounded-full">Spesialis Aljabar</span>
            </div>
            <p class="text-[11px] opacity-60">📍 {{ $tutor->lokasi_default ?? 'Banjarmasin' }}</p>
        </div>
    </div>

    <div class="pt-3 border-t border-brand-dark/10 flex items-center justify-between">
        <div>
            <span class="text-[10px] opacity-60 block">Mulai dari</span>
            <span class="font-heading text-lg font-black text-brand-teal">Rp {{ number_format($tarif, 0, ',', '.') }}</span>
        </div>
        <a href="?step=3&guru_id={{ $tutor->guru_id }}" class="bg-brand-yellow hover:bg-brand-yellow-hover font-bold text-xs px-5 py-2.5 rounded-full border border-brand-dark transition">
            Pilih Tutor Ini →
        </a>
    </div>
</div>