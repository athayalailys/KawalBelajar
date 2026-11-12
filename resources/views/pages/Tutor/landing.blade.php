<x-common.app-layout>
    <x-slot:title>Menjadi Tutor - KawalBelajar</x-slot:title>
    <x-slot:description>Bergabung sebagai mitra pengajar KawalBelajar. Ajukan kualifikasi dan jenjang mengajar Anda.</x-slot:description>

    <div class="space-y-12 py-4">
        <section class="relative overflow-hidden rounded-3xl border border-amber-200 bg-amber-50/70 px-6 py-12 sm:px-10 lg:px-14 lg:py-16">
            <div class="relative z-10 max-w-3xl space-y-5">
                <span class="inline-flex rounded-full border border-amber-300 bg-white px-4 py-1.5 text-xs font-bold uppercase tracking-wide text-amber-900">Mitra Pengajar KawalBelajar</span>
                <h1 class="text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl lg:text-5xl">Bergabung Menjadi Mitra Pengajar KawalBelajar</h1>
                <p class="max-w-2xl text-base leading-relaxed text-slate-600">Bagikan keahlian Anda dan dampingi pembelajar dengan kebutuhan yang sesuai. Ajukan bidang keahlian serta dokumen untuk ditinjau tim KawalBelajar.</p>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('tutor.register') }}" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-900 bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 shadow-sm transition hover:bg-amber-500">Daftar Sebagai Mitra Tutor <span aria-hidden="true">&#8594;</span></a>
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Sudah punya akun? Masuk</a>
                </div>
            </div>
        </section>

        <section aria-labelledby="tutor-benefits-heading" class="space-y-5">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Mengajar bersama KawalBelajar</p>
                <h2 id="tutor-benefits-heading" class="mt-2 text-2xl font-extrabold text-slate-900">Dukungan untuk kegiatan mengajar Anda</h2>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-xl" aria-hidden="true">&#128338;</div>
                    <h3 class="font-bold text-slate-900">Fleksibilitas Waktu</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Atur ketersediaan jadwal mengajar sesuai waktu yang Anda miliki.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-xl" aria-hidden="true">&#128176;</div>
                    <h3 class="font-bold text-slate-900">Tarif dan kelompok belajar</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Dampingi pembelajar melalui kelas privat maupun kelompok belajar yang tersedia di platform.</p>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-xl" aria-hidden="true">&#128205;</div>
                    <h3 class="font-bold text-slate-900">Panduan lokasi kunjungan</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Informasi lokasi pembelajar membantu Anda menyiapkan perjalanan untuk sesi tatap muka di rumah.</p>
                </article>
            </div>
        </section>

        <section aria-labelledby="tutor-steps-heading" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Proses pendaftaran</p>
                <h2 id="tutor-steps-heading" class="mt-2 text-2xl font-extrabold text-slate-900">Tiga langkah untuk mengajukan diri</h2>
            </div>
            <ol class="mt-7 grid gap-5 md:grid-cols-3">
                <li class="rounded-2xl bg-slate-50 p-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-sm font-extrabold text-white">1</span>
                    <h3 class="mt-4 font-bold text-slate-900">Isi Identitas dan Spesialisasi</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Lengkapi informasi kontak, riwayat akademik, mata pelajaran, dan jenjang yang diajukan.</p>
                </li>
                <li class="rounded-2xl bg-slate-50 p-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-sm font-extrabold text-white">2</span>
                    <h3 class="mt-4 font-bold text-slate-900">Unggah Berkas Pendukung</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Kirim CV, identitas, transkrip atau KHS, serta sertifikat jika tersedia.</p>
                </li>
                <li class="rounded-2xl bg-slate-50 p-5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-sm font-extrabold text-white">3</span>
                    <h3 class="mt-4 font-bold text-slate-900">Tunggu Verifikasi Admin</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Tim KawalBelajar meninjau dokumen dan pengajuan jenjang sebelum akun pengajar diaktifkan.</p>
                </li>
            </ol>
            <div class="mt-7 border-t border-slate-100 pt-6">
                <a href="{{ route('tutor.register') }}" class="inline-flex items-center gap-2 rounded-full border-2 border-slate-900 bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-amber-500">Mulai Pendaftaran Tutor <span aria-hidden="true">&#8594;</span></a>
            </div>
        </section>
    </div>
</x-common.app-layout>
