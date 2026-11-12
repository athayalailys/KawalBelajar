<header class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Logo Brand -->
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-lg text-slate-900 tracking-tight">
            <span class="p-1.5 bg-amber-400 rounded-xl">🎓</span> KawalBelajar
        </a>

        <!-- Navigasi Utama -->
        <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-600">
            <a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Beranda</a>
            <a href="{{ route('home') }}#program" class="hover:text-slate-900 transition-colors">Kelas</a>
            <a href="{{ route('tutor.landing') }}" class="hover:text-slate-900 transition-colors">Menjadi Tutor</a>
        </nav>

        <!-- Action Button Right -->
        <div class="flex items-center gap-3">
            @auth
                @php
                    $authenticatedDashboardRoute = match (auth()->user()->role) {
                        'student' => 'student.dashboard',
                        'teacher', 'tutor' => 'tutor.landing',
                        'admin' => 'admin.tutors.index',
                        default => 'home',
                    };
                @endphp
                <a href="{{ route($authenticatedDashboardRoute) }}" class="px-5 py-2 bg-slate-900 text-white text-xs font-bold rounded-full transition-all shadow-sm">
                    Beranda
                </a>
            @else
                <!-- Tombol Masuk -> Single Login Page -->
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-slate-700 hover:text-slate-900 transition-colors">
                    Masuk
                </a>

                <!-- Tautan pendaftaran pembelajar -->
                <a href="{{ route('register', ['return_to' => request()->routeIs('login') ? 'login' : 'home']) }}" class="px-5 py-2 bg-amber-400 hover:bg-amber-500 text-slate-900 text-xs font-bold rounded-full transition-all shadow-sm">
                    Daftar sebagai Pembelajar
                </a>
            @endauth
        </div>
    </div>
</header>
