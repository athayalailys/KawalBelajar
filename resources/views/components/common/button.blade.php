@props([
    'variant' => 'primary', // primary (amber), dark (slate), outline
    'type' => 'button'
])

@php
    $classes = match($variant) {
        'primary' => 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold shadow-amber-200',
        'dark' => 'bg-slate-900 hover:bg-slate-800 text-white font-bold',
        'outline' => 'bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold',
        default => 'bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold'
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "px-6 py-3 rounded-full text-xs transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2 $classes"]) }}>
    {{ $slot }}
</button>