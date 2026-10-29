<aside class="w-64 bg-white border-r border-slate-200 min-h-screen flex flex-col justify-between p-4 flex-shrink-0">
    <div class="space-y-6">
        <!-- Sidebar Header / Logo -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-700 rounded-xl flex items-center justify-center text-white shadow-sm">
                    <!-- Icon Topi Toga / KawalBelajar -->
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4l7 3.82 7-3.82v-4L12 17l-7-3.82z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 text-base leading-tight">KawalBelajar</h1>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">PORTAL SISWA</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    </div>
                </div>
            </div>
            <!-- Toggle / Collapse Button -->
            <button class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-1.5 font-medium text-sm">
            <!-- Beranda Siswa (Active) -->
            <a href="{{ route('student.dashboard') }}" 
               class="flex items-center justify-between px-3.5 py-3 rounded-2xl bg-[#FFFDE7] text-slate-900 font-bold border border-amber-200/60 shadow-sm transition">
                <div class="flex items-center gap-3">
                    <span class="text-base">🏫</span>
                    <span>Beranda Siswa</span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
            </a>

            <!-- Jadwal Belajar -->
            <a href="{{ route('student.schedule') }}"
               class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                <div class="flex items-center gap-3">
                    <span class="text-base">📅</span>
                    <span>Jadwal Belajar</span>
                </div>
                <span class="px-2 py-0.5 bg-slate-200/70 text-slate-700 text-xs font-semibold rounded-lg">{{ $upcomingSessionsCount ?? 0 }} Sesi </span>
            </a>

            <!-- Ruang Kelas & Pre-Test -->
            <a href="{{ route('student.classroom') }}"
               class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                <div class="flex items-center gap-3">
                    <span class="text-base">💻</span>
                    <span>Ruang Kelas & Pre-Test</span>
                </div>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            </a>

            <!-- Rapor Belajar Siswa -->
            <a href="{{ route('student.report') }}"
               class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                <div class="flex items-center gap-3">
                    <span class="text-base">📊</span>
                    <span>Rapor Belajar Siswa</span>
                </div>
            </a>

            <!-- Laman Orang Tua -->
            <a href="{{ route('student.parent') }}"
               class="flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
                <div class="flex items-center gap-3">
                    <span class="text-base">🛡️</span>
                    <span>Laman Orang Tua</span>
                </div>
                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-md uppercase">PIN</span>
            </a>
        </nav>
    </div>
</aside>
