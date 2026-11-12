<x-common.app-layout>
    <x-slot:title>Buat Kata Sandi Baru - KawalBelajar</x-slot:title>

    <section class="mx-auto my-10 max-w-md rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-xl md:p-8">
        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900">Buat Kata Sandi Baru</h1>
            <p class="text-sm text-slate-600">Gunakan kata sandi minimal 8 karakter.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-1 block text-xs font-bold text-slate-900">Email akun</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="password" class="mb-1 block text-xs font-bold text-slate-900">Kata sandi baru</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-xs font-bold text-slate-900">Ulangi kata sandi baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-amber-400 py-3.5 text-xs font-bold text-slate-900 shadow-md transition hover:bg-amber-500">Simpan kata sandi baru</button>
        </form>
    </section>
</x-common.app-layout>
