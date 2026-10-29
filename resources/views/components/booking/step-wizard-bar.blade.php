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

<style>
    .booking-step-chevron {
        clip-path: polygon(0% 0%, calc(100% - 15px) 0%, 100% 50%, calc(100% - 15px) 100%, 0% 100%, 15px 50%);
    }

    .booking-step-chevron-first {
        clip-path: polygon(0% 0%, calc(100% - 15px) 0%, 100% 50%, calc(100% - 15px) 100%, 0% 100%);
    }
</style>

<div class="w-full mb-8 overflow-x-auto py-1">
    <div class="flex w-full min-w-[680px] items-center">
        @foreach ($steps as $number => $title)
            <div
                class="flex-1 py-3 text-center text-xs font-bold transition-all duration-300 select-none {{ $loop->first ? 'rounded-l-lg booking-step-chevron-first' : '-ml-[15px] booking-step-chevron' }}"
                :class="currentStep >= {{ $number }} ? 'bg-[#36A9B8] text-white' : 'bg-[#D9D9D9] text-slate-500'"
            >
                <span class="px-2 whitespace-nowrap">{{ $title }}</span>
            </div>
        @endforeach
    </div>
</div>
