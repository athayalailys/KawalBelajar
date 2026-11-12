@props(['name' => '', 'value' => '', 'title' => '', 'subtitle' => '', 'badge' => '', 'icon' => '📚', 'selected' => false])

<label class="relative block p-5 rounded-2xl cursor-pointer transition-all border-2 {{ $selected ? 'bg-amber-50/60 border-slate-900 shadow-sm' : 'bg-white border-slate-200 hover:border-slate-300' }}">
    <input type="radio" name="{{ $name }}" value="{{ $value }}" {{ $selected ? 'checked' : '' }} class="sr-only">
    
    <div x-show="{{ $selected ? 'true' : 'false' }}" class="absolute top-4 right-4 w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">
        ✓
    </div>

    <div class="w-10 h-10 mb-3 bg-amber-100/80 rounded-xl flex items-center justify-center text-amber-800 text-lg">
        {{ $icon }}
    </div>

    <h4 class="font-bold text-slate-900 text-sm leading-snug">{{ $title }}</h4>
    @if($subtitle)
        <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
    @endif

    @if($badge)
        <span class="inline-block mt-3 px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded uppercase tracking-wider">
            {{ $badge }}
        </span>
    @endif
</label>