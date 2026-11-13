<nav aria-label="Navigasi tutor" class="mx-4 mb-2 flex gap-2 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 lg:hidden">
    <a href="{{ route('tutor.dashboard') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('tutor.dashboard') ? 'bg-amber-100 text-slate-900' : 'text-slate-600' }}">Beranda</a>
    <a href="{{ route('tutor.classes') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('tutor.classes*') ? 'bg-amber-100 text-slate-900' : 'text-slate-600' }}">Kelas &amp; Siswa</a>
    <a href="{{ route('tutor.class-details') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('tutor.class-details', 'tutor.sessions.*', 'tutor.classroom*') ? 'bg-amber-100 text-slate-900' : 'text-slate-600' }}">Cockpit</a>
    <a href="{{ route('tutor.availability') }}" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('tutor.availability*') ? 'bg-amber-100 text-slate-900' : 'text-slate-600' }}">Ketersediaan</a>
    <a href="{{ route('tutor.payroll') }}" @if (request()->routeIs('tutor.payroll')) aria-current="page" @endif class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('tutor.payroll') ? 'bg-amber-100 text-slate-900' : 'text-slate-600' }}">Payroll</a>
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">@csrf<button type="submit" class="whitespace-nowrap rounded-xl px-3 py-2 text-xs font-semibold text-rose-700">Keluar</button></form>
</nav>
