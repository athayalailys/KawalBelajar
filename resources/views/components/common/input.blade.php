@props(['label' => '', 'name' => '', 'type' => 'text', 'placeholder' => '', 'required' => false])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-bold text-slate-900">
            {{ $label }} @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
    <input type="{{ $type }}" 
           name="{{ $name }}" 
           id="{{ $name }}" 
           value="{{ old($name) }}"
           placeholder="{{ $placeholder }}"
           {{ $required ? 'required' : '' }}
           {{ $attributes->merge(['class' => 'w-full px-5 py-3 bg-white border border-slate-300 rounded-full text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all shadow-sm']) }}>
</div>