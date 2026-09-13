@props(['currentStep' => 1])

@php
    $steps = [
        1 => 'Step 1: Pilih Kebutuhan',
        2 => 'Step 2: Pilih Tutor',
        3 => 'Step 3: Pilih Sesi',
        4 => 'Step 4: Pilih Jadwal',
        5 => 'Step 5: Bayar',
    ];
@endphp

<div class="w-full flex items-center bg-brand-light/30 rounded-full overflow-hidden p-1 text-xs font-semibold">
    @foreach($steps as $number => $label)
        <div class="flex-1 text-center py-2.5 rounded-full transition-all duration-300 {{ $currentStep >= $number ? 'bg-brand-teal text-brand-white shadow-xs' : 'opacity-40' }}">
            {{ $label }}
        </div>
    @endforeach
</div>