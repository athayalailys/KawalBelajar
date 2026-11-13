@props(['title' => 'Portal Tutor'])
<header class="flex items-center justify-between px-4 py-5 sm:px-8">
    <div class="flex items-center gap-2 text-sm font-medium text-slate-500"><span>Portal Tutor</span><span aria-hidden="true">›</span><span class="font-bold text-slate-900">{{ $title }}</span></div>
    <button type="button" aria-label="Notifikasi" class="relative rounded-full border border-slate-300 bg-white p-2.5 text-slate-700 shadow-sm transition hover:bg-slate-50">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
        <span class="absolute right-0 top-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-rose-500"></span>
    </button>
</header>
