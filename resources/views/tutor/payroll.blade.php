<x-tutor-layout title="Rekap Gaji & Payroll - KawalBelajar" heading="Rekap Gaji & Payroll">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border-2 border-slate-900 bg-white p-6 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-teal-700">Transparansi pendapatan</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Rekap Gaji &amp; Payroll</h1>
            <p class="mt-2 text-sm text-slate-600">Honor tutor sebesar 25% dari total kas sesi, dihitung setelah sesi selesai dan Clock-Out tercatat.</p>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan payroll">
            <article class="rounded-2xl border-2 border-slate-900 bg-emerald-50 p-5"><p class="text-xs font-bold uppercase text-slate-600">Total honor bersih</p><p class="mt-2 text-2xl font-extrabold text-emerald-800">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</p></article>
            <article class="rounded-2xl border-2 border-slate-900 bg-amber-50 p-5"><p class="text-xs font-bold uppercase text-slate-600">Menunggu pencairan</p><p class="mt-2 text-2xl font-extrabold text-amber-800">Rp {{ number_format($pendingEarnings, 0, ',', '.') }}</p></article>
            <article class="rounded-2xl border-2 border-slate-900 bg-cyan-50 p-5"><p class="text-xs font-bold uppercase text-slate-600">Sudah dicairkan</p><p class="mt-2 text-2xl font-extrabold text-cyan-800">Rp {{ number_format($paidEarnings, 0, ',', '.') }}</p></article>
            <article class="rounded-2xl border-2 border-slate-900 bg-white p-5"><p class="text-xs font-bold uppercase text-slate-600">Sesi &amp; jam mengajar</p><p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $completedSessionCount }} sesi</p><p class="text-sm text-slate-600">{{ number_format($totalMinutes / 60, 1, ',', '.') }} jam ({{ $totalMinutes }} menit)</p></article>
        </section>

        <section class="overflow-hidden rounded-2xl border-2 border-slate-900 bg-white">
            <div class="border-b border-slate-200 px-5 py-4"><h2 class="font-extrabold text-slate-900">Riwayat kerja per sesi</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-cyan-50 text-xs uppercase text-slate-700"><tr><th class="px-4 py-3">Tanggal sesi</th><th class="px-4 py-3">Program / materi</th><th class="px-4 py-3">Durasi</th><th class="px-4 py-3">Kas sesi</th><th class="px-4 py-3">Honor tutor (25%)</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($logs as $log)
                            @php($session = $log->session)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3">{{ $session?->scheduled_at?->translatedFormat('d M Y') ?? $log->created_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="font-semibold text-slate-900">{{ $session?->order?->package?->package_name ?? $session?->material?->title ?? 'Sesi pembelajaran' }}</span><span class="block text-xs text-slate-500">{{ $session?->material?->title ?? 'Sesi #'.($session?->session_number ?? '—') }}</span></td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $log->duration_minutes }} menit</td>
                                <td class="whitespace-nowrap px-4 py-3">Rp {{ number_format((float) $log->gross_session_amount, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-emerald-800">Rp {{ number_format((float) $log->teacher_earnings, 0, ',', '.') }}</td>
                                <td class="px-4 py-3"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">{{ str($log->payroll_status)->replace('_', ' ')->title() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada rekap honor. Log kerja akan tercatat setelah sesi selesai dan tutor melakukan Clock-Out.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-tutor-layout>
