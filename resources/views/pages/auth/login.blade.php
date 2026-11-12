@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-10 px-4" x-data="{ role: @js(old('user_role', 'siswa')) }">
    <div class="w-full max-w-md bg-white border-2 border-slate-900 rounded-3xl p-6 md:p-8 shadow-xl space-y-6">

        <!-- Header Portal -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-teal-600 text-white rounded-2xl flex items-center justify-center mx-auto text-xl font-bold shadow-md">
                🎓
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">KawalBelajar</h1>
            <span class="inline-block px-3 py-1 bg-teal-50 text-teal-700 border border-teal-200 text-[10px] font-bold rounded-full uppercase tracking-wider">
                Portal Autentikasi Terpadu
            </span>
        </div>

        <!-- Pilihan peran pengguna -->
        <div class="space-y-2">
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">
                Pilih Peran Pengguna:
            </label>
            <div class="grid grid-cols-2 gap-1 p-1 bg-slate-100 border border-slate-300 rounded-2xl">
                <!-- Tab pembelajar -->
                <button type="button"
                        @click="role = 'siswa'"
                        :class="role === 'siswa' ? 'bg-teal-600 text-white font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                    <span>🎓</span> Pembelajar
                </button>

                <!-- Tab Tutor -->
                <button type="button"
                        @click="role = 'tutor'"
                        :class="role === 'tutor' ? 'bg-amber-400 text-slate-900 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 font-medium'"
                        class="py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                    <span>👨‍🏫</span> Tutor
                </button>
            </div>
        </div>

        <!-- Info Helper Banner berdasarkan Peran -->
        <div x-show="role === 'siswa'" class="p-3 bg-teal-50 border border-teal-200 rounded-xl text-xs text-teal-800 flex items-start gap-2">
            <span>💡</span>
            <span>Akses bimbingan belajar, tes awal, dan bahan belajar untuk pembelajar terdaftar.</span>
        </div>

        <div x-show="role === 'tutor'" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2">
            <span>🔑</span>
            <span>Gunakan data masuk tutor yang berkasnya telah diverifikasi oleh admin.</span>
        </div>

        <!-- 2. SINGLE FORM LOGIN -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            @if (session('status'))
                <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Nilai peran dipakai sistem untuk memeriksa akses akun -->
            <input type="hidden" name="user_role" :value="role">

            <!-- Alamat email akun -->
            <x-common.input label="Alamat email"
                            name="email"
                            type="email"
                            placeholder="nama@kawalbelajar.id"
                            required />

            <!-- Field Kata Sandi -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-900">Kata Sandi *</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-teal-600 hover:underline font-semibold">Lupa Sandi?</a>
                </div>
                <x-common.input name="password"
                                type="password"
                                placeholder="••••••••"
                                required />
            </div>

            <!-- Ingat perangkat -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    :class="role === 'siswa' ? 'bg-teal-600 hover:bg-teal-700 text-white' : 'bg-amber-400 hover:bg-amber-500 text-slate-900'"
                    class="w-full py-3.5 rounded-2xl font-bold text-xs shadow-md transition-all">
                Masuk sekarang &rarr;
            </button>
        </form>

        <!-- Footer Link Registrasi -->
        <div class="pt-4 border-t border-slate-100 text-center space-y-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Belum Memiliki Akun KawalBelajar?</span>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('register', ['return_to' => 'login']) }}" class="p-2.5 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-700 hover:bg-slate-50 transition-all">
                    Daftar sebagai Pembelajar &rarr;
                </a>
                <a href="{{ route('tutor.register') }}" class="p-2.5 border border-slate-200 rounded-xl text-[11px] font-bold text-slate-700 hover:bg-slate-50 transition-all">
                    Gabung Mitra Tutor &rarr;
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
