@props([
    'title' => 'Nama Paket',
    'badge' => 'Semua Jenjang',
    'formattedPrice' => 'Rp 0',
    'period' => '/Bulan',
    'features' => [],
    'ctaText' => 'Daftar Kelas',
    'ctaUrl' => '#daftar'
])

<div class="bg-white border border-brand-light/80 rounded-brand-xl p-6 sm:p-7 flex flex-col justify-between shadow-sm hover:shadow-md transition duration-200 h-full">
    <div>
        <!-- Badge / Tag Jenjang -->
        <div>
            <span class="inline-block text-[10px] font-bold text-brand-dark/70 bg-brand-light px-3 py-1 rounded-full uppercase tracking-wider">
                {{ $badge }}
            </span>
        </div>

        <!-- Judul Paket -->
        <h3 class="font-heading text-xl sm:text-2xl font-bold text-brand-dark mt-3 tracking-tight">
            {{ $title }}
        </h3>
        
        <!-- Harga Paket (Menerima string bersih dari Model Accessor) -->
        <div class="mt-4 mb-6 flex items-baseline gap-1">
            <span class="font-heading text-2xl sm:text-3xl font-extrabold text-brand-dark">
                {{ $formattedPrice }}
            </span>
            <span class="font-sans text-xs text-brand-dark/60 font-medium">
                {{ $period }}
            </span>
        </div>

        <!-- List Fitur Paket -->
        <ul class="space-y-3 font-sans text-xs sm:text-sm text-brand-dark/80 border-t border-brand-light/60 pt-4">
            @foreach($features as $feature)
                <li class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-brand-teal flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ $feature }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Tombol CTA -->
    <div class="pt-6">
        <a href="{{ $ctaUrl }}" class="w-full block text-center bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark font-sans font-bold py-3 rounded-full text-xs sm:text-sm shadow-sm transition duration-200">
            {{ $ctaText }}
        </a>
    </div>
</div>