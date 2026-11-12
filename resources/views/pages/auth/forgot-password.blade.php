<x-common.app-layout>
    <x-slot:title>Lupa Kata Sandi - KawalBelajar</x-slot:title>

    <section class="mx-auto my-10 max-w-md rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-xl md:p-8">
        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900">Lupa Kata Sandi?</h1>
            <p class="text-sm text-slate-600">Masukkan email akun. Jika terdaftar, kami akan mengirim tautan untuk membuat kata sandi baru.</p>
        </div>

        @if (session('status'))
            <div role="status" class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-xs font-bold text-slate-900">Email akun</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <button type="submit" class="w-full rounded-2xl bg-teal-600 py-3.5 text-xs font-bold text-white shadow-md transition hover:bg-teal-700">Kirim tautan pengaturan ulang</button>
        </form>

        <a href="{{ route('login') }}" class="mt-5 block text-center text-xs font-semibold text-teal-700 hover:underline">Kembali ke halaman masuk</a>
    </section>
</x-common.app-layout>
