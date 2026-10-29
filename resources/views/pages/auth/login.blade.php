<x-common.app-layout>
<div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-center text-2xl font-bold text-slate-900">Masuk ke KawalBelajar</h2>
            <p class="mt-2 text-center text-xs text-slate-500">
                Belum punya akun? <a href="/register/student" class="font-semibold text-emerald-600 hover:text-emerald-500">Daftar Siswa</a>
            </p>
        </div>
        <form class="mt-8 space-y-4" action="{{ route('login.store') }}" method="POST">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="nama@email.com">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="••••••••">
            </div>
            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition shadow-md">
                Masuk Sekarang ➔
            </button>
        </form>
    </div>
</div>
</x-common.app-layout>
