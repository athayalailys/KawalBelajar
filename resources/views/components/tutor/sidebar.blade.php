@php
    $teacherProfile = auth()->user()?->teacher;
    $tutorName = auth()->user()?->full_name ?? 'Tutor';
    $initials = collect(explode(' ', trim($tutorName)))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
    $items = [
        ['route' => 'tutor.dashboard', 'label' => 'Beranda Tutor', 'icon' => '⌂', 'active' => request()->routeIs('tutor.dashboard')],
        ['route' => 'tutor.classes', 'label' => 'Manajemen Kelas dan Siswa', 'icon' => '▤', 'active' => request()->routeIs('tutor.classes*')],
        ['route' => 'tutor.class-details', 'label' => 'Sesi Mengajar / Cockpit', 'icon' => '◷', 'active' => request()->routeIs('tutor.class-details', 'tutor.sessions.*', 'tutor.classroom*')],
        ['route' => 'tutor.availability', 'label' => 'Master Availability', 'icon' => '▦', 'active' => request()->routeIs('tutor.availability*')],
    ];
@endphp
<aside class="hidden w-64 shrink-0 flex-col justify-between border-r border-slate-200 bg-white p-4 lg:flex">
    <div>
        <a href="{{ route('tutor.dashboard') }}" class="flex items-center gap-3 border-b border-slate-100 pb-5">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl border-2 border-slate-900 bg-amber-300 text-xl font-black">K</span>
            <span><span class="block text-lg font-extrabold tracking-tight text-slate-900">Kawal<span class="text-cyan-600">Belajar</span></span><span class="mt-0.5 block text-[10px] font-bold tracking-wider text-slate-500">PORTAL TUTOR</span></span>
        </a>
        <nav aria-label="Navigasi tutor" class="mt-5 space-y-1.5">
            @foreach ($items as $item)
                <a href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm {{ $item['active'] ? 'border border-amber-200 bg-amber-50 font-bold text-slate-900' : 'border border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <span class="flex h-7 w-7 items-center justify-center text-base" aria-hidden="true">{{ $item['icon'] }}</span><span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            @if (Route::has('tutor.payroll'))
                <a href="{{ route('tutor.payroll') }}" @if (request()->routeIs('tutor.payroll')) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm {{ request()->routeIs('tutor.payroll') ? 'border border-amber-200 bg-amber-50 font-bold text-slate-900' : 'border border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"><span class="flex h-7 w-7 items-center justify-center" aria-hidden="true">▥</span><span>Rekap Gaji &amp; Payroll</span></a>
            @else
                <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-3 text-sm text-slate-400" title="Halaman payroll belum tersedia"><span class="flex h-7 w-7 items-center justify-center" aria-hidden="true">▥</span><span>Rekap Gaji &amp; Payroll</span></span>
            @endif
        </nav>
    </div>
    <div class="border-t border-slate-200 pt-4">
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-300 bg-cyan-100 text-sm font-extrabold text-slate-800">{{ mb_strtoupper($initials) }}</span>
            <span class="min-w-0 flex-1"><span class="block truncate text-xs font-bold text-slate-900">{{ $tutorName }}</span><span class="block truncate text-[10px] text-slate-500">{{ $teacherProfile?->teacher_level ?? $teacherProfile?->focus_subject ?? 'Tutor' }}</span></span>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button type="submit" class="w-full rounded-xl px-3.5 py-2.5 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50">Keluar</button></form>
    </div>
</aside>
