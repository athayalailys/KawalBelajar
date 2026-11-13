<x-admin-layout title="Beranda Admin - KawalBelajar" heading="Beranda">
    <div class="space-y-6">
        <section class="rounded-2xl border-2 border-slate-900 bg-white p-6">
            <p class="text-sm font-bold uppercase tracking-wider text-teal-700">Ringkasan KawalBelajar</p>
            <h1 class="mt-2 text-2xl font-black text-slate-900">Halo, {{ auth()->user()->full_name }}.</h1>
            <p class="mt-1 text-slate-600">Pantau verifikasi tutor, transaksi, dan kegiatan operasional dari satu tempat.</p>
        </section>
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Antrean verifikasi', $pendingTutorCount, 'bg-amber-50'], ['Tutor aktif', $activeTutorCount, 'bg-cyan-50'], ['Kas platform', $platformRevenue, 'bg-emerald-50'], ['Honor tutor 25%', $tutorEarnings, 'bg-white']] as [$label, $value, $color])
                <article class="rounded-2xl border-2 border-slate-900 {{ $color }} p-5"><p class="text-sm font-semibold text-slate-600">{{ $label }}</p><p class="mt-2 text-2xl font-black text-slate-900">{{ is_numeric($value) && in_array($label, ['Kas platform', 'Honor tutor 25%']) ? 'Rp '.number_format((float)$value, 0, ',', '.') : $value }}</p></article>
            @endforeach
        </section>
        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-2xl border-2 border-slate-900 bg-white p-5"><div class="flex items-center justify-between"><h2 class="font-extrabold">Pengajuan tutor terbaru</h2><a class="font-bold text-teal-700" href="{{ route('admin.tutors') }}">Lihat semua</a></div><div class="mt-4 divide-y divide-slate-200">@forelse($recentApplications as $teacher)<p class="py-3">{{ $teacher->user?->full_name }} <span class="text-slate-500">· {{ $teacher->focus_subject ?? 'Tutor baru' }}</span></p>@empty<p class="py-3 text-slate-500">Tidak ada pengajuan yang menunggu.</p>@endforelse</div></article>
            <article class="rounded-2xl border-2 border-slate-900 bg-white p-5"><div class="flex items-center justify-between"><h2 class="font-extrabold">Perubahan tarif terbaru</h2><a class="font-bold text-teal-700" href="{{ route('admin.tariffs') }}">Buka audit tarif</a></div><div class="mt-4 divide-y divide-slate-200">@forelse($recentTariffChanges as $audit)<p class="py-3">Rp {{ number_format((float)$audit->old_price, 0, ',', '.') }} → Rp {{ number_format((float)$audit->new_price, 0, ',', '.') }} <span class="text-slate-500">· {{ $audit->changed_at?->format('d M Y H:i') }}</span></p>@empty<p class="py-3 text-slate-500">Belum ada perubahan tarif.</p>@endforelse</div></article>
        </section>
    </div>
</x-admin-layout>
