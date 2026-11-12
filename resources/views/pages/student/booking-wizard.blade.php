<x-student-layout title="Pesan Bimbingan - KawalBelajar">
    <section
        class="mx-auto max-w-5xl space-y-6"
        x-data="bookingWizard()"
        data-catalog-url="{{ route('api.student.catalog') }}"
        data-subjects-url="{{ route('api.student.catalog.subjects') }}"
        data-calculate-url="{{ route('api.student.booking.calculate') }}"
        data-slots-url="{{ route('api.student.booking.tutor-slots', ['teacherId' => '__TEACHER_ID__']) }}"
        data-checkout-url="{{ route('api.student.booking.checkout') }}"
        data-education-level="{{ $educationLevel ?? 'SMP' }}"
    >
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <h1 class="mt-3 text-2xl font-bold text-slate-900 md:text-3xl">Pesan sesi bimbingan</h1>
            <p class="mt-2 text-sm text-slate-600">Biaya dan ketersediaan jadwal akan diperiksa sebelum pesanan dibuat.</p>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6" aria-label="Tahapan pemesanan">
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Langkah pemesanan</h2>
                    <p class="text-xs text-slate-500">Ikuti tahapan berikut sampai pesanan siap dibayar.</p>
                </div>
                <p class="text-xs font-semibold text-teal-800" x-text="order ? 'Pesanan siap dibayar' : (isPaymentOpen ? 'Rincian pembayaran' : `Langkah ${step} dari 3`)"></p>
            </div>

            <ol class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                <li class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition"
                    :class="!order && !isPaymentOpen && step === 1 ? 'border-teal-700 bg-teal-600 text-white' : (step > 1 || isPaymentOpen || order ? 'border-teal-200 bg-teal-50 text-teal-900' : 'border-slate-200 bg-slate-100 text-slate-500')">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/90 text-sm font-extrabold text-teal-800" x-text="step > 1 || isPaymentOpen || order ? '✓' : '1'"></span>
                    <span class="text-sm font-bold">Pilih kebutuhan</span>
                </li>
                <li class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition"
                    :class="!order && !isPaymentOpen && step === 2 ? 'border-teal-700 bg-teal-600 text-white' : (step > 2 || isPaymentOpen || order ? 'border-teal-200 bg-teal-50 text-teal-900' : 'border-slate-200 bg-slate-100 text-slate-500')">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/90 text-sm font-extrabold text-teal-800" x-text="step > 2 || isPaymentOpen || order ? '✓' : '2'"></span>
                    <span class="text-sm font-bold">Pilih tutor</span>
                </li>
                <li class="flex items-center gap-3 rounded-xl border-2 px-4 py-3 transition"
                    :class="!order && !isPaymentOpen && step === 3 ? 'border-teal-700 bg-teal-600 text-white' : (isPaymentOpen || order ? 'border-teal-200 bg-teal-50 text-teal-900' : 'border-slate-200 bg-slate-100 text-slate-500')">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/90 text-sm font-extrabold text-teal-800" x-text="isPaymentOpen || order ? '✓' : '3'"></span>
                    <span class="text-sm font-bold">Pilih sesi dan jadwal</span>
                </li>
            </ol>
        </section>

        <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>

        <div x-show="!order" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <x-booking.step1-kebutuhan />
            <x-booking.step2-tutor-card />
            <div x-show="step === 3" x-cloak class="space-y-6">
                <x-booking.step3-sesi />
                <x-booking.step4-jadwal />
            </div>
        </div>

        <x-booking.step5-pembayaran />

        <div x-show="order" x-cloak class="rounded-3xl border border-emerald-200 bg-white p-6 shadow-sm md:p-8">
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase text-amber-900">Menunggu pembayaran</span>
            <h2 class="mt-4 text-2xl font-bold text-slate-900">Pesanan berhasil dibuat</h2>
            <p class="mt-2 text-sm text-slate-600">Selesaikan pembayaran melalui halaman tagihan. Status pesanan akan diperbarui setelah pembayaran dikonfirmasi.</p>
            <dl class="mt-5 grid gap-3 rounded-2xl bg-slate-50 p-5 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Nomor tagihan</dt><dd class="font-bold" x-text="order?.invoice_number"></dd></div>
                <div><dt class="text-slate-500">Total</dt><dd class="font-bold" x-text="order ? rupiah(order.total_amount) : ''"></dd></div>
                <div><dt class="text-slate-500">Status pembayaran</dt><dd class="font-bold" x-text="({ pending: 'Menunggu pembayaran', settled: 'Lunas', paid: 'Lunas', expired: 'Kedaluwarsa', failed: 'Gagal' })[order?.payment_status] || 'Menunggu pembayaran'"></dd></div>
                <div><dt class="text-slate-500">Kedaluwarsa</dt><dd class="font-bold" x-text="order?.expires_at ? new Date(order.expires_at).toLocaleString('id-ID') : '—'"></dd></div>
                <div x-show="order?.squad_code"><dt class="text-slate-500">Kode kelompok belajar</dt><dd class="font-bold" x-text="order?.squad_code"></dd></div>
            </dl>
            <div class="mt-6 flex flex-wrap gap-3">
                <a x-show="order?.payment_url" :href="order?.payment_url" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-teal-700 px-5 py-3 text-sm font-bold text-white">Lanjutkan pembayaran</a>
                <a href="{{ route('student.schedule') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-800">Lihat jadwal</a>
            </div>
        </div>
    </section>
</x-student-layout>
