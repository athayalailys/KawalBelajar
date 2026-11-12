@php
    $navigation = [
        ['name' => 'Beranda', 'route' => 'student.dashboard', 'icon' => '⌂'],
        ['name' => 'Jadwal Belajar', 'route' => 'student.schedule', 'icon' => '▦'],
        ['name' => 'Ruang Kelas', 'route' => 'student.classroom', 'icon' => '▣'],
        ['name' => 'Rapor Belajar', 'route' => 'student.report', 'icon' => '▤'],
    ];
@endphp

<aside class="hidden w-64 shrink-0 flex-col justify-between border-r border-slate-200 bg-white p-4 lg:flex">
    <div class="space-y-6">
        <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3 border-b border-slate-100 pb-5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-700 text-xl text-white">K</span>
            <span>
                <span class="block font-bold leading-tight text-slate-900">KawalBelajar</span>
                <span class="mt-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500">Portal Pembelajar</span>
            </span>
        </a>

        <nav aria-label="Navigasi pembelajar" class="space-y-1.5 text-sm font-medium">
            @foreach ($navigation as $item)
                @php($active = request()->routeIs($item['route']))
                <a href="{{ route($item['route']) }}" @class([
                    'flex items-center gap-3 rounded-xl px-3.5 py-3 transition',
                    'border border-amber-200 bg-amber-50 font-bold text-slate-900' => $active,
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !$active,
                ]) @if ($active) aria-current="page" @endif>
                    <span aria-hidden="true" class="w-5 text-center text-lg">{{ $item['icon'] }}</span>
                    <span>{{ $item['name'] }}</span>
                </a>
            @endforeach

            @if (auth()->user()?->student?->education_level !== 'UMUM')
                @php($parentActive = request()->routeIs('student.parent'))
                <a href="{{ route('student.parent') }}" @class([
                    'flex items-center justify-between rounded-xl px-3.5 py-3 transition',
                    'border border-amber-200 bg-amber-50 font-bold text-slate-900' => $parentActive,
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !$parentActive,
                ]) @if ($parentActive) aria-current="page" @endif>
                    <span class="flex items-center gap-3"><span aria-hidden="true" class="w-5 text-center text-lg">◇</span><span>Mode Orang Tua</span></span>
                    <span class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">PIN</span>
                </a>
            @endif
        </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="pt-5">
        @csrf
        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
            <span aria-hidden="true" class="w-5 text-center text-lg">&#x21AA;</span>
            <span>Keluar</span>
        </button>
    </form>

</aside>
