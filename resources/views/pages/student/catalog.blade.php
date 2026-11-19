<x-student-layout title="Jelajahi Kelas - KawalBelajar">
    <section class="space-y-6" x-data="studentCatalog"
        data-catalog-url="{{ route('api.student.catalog') }}"
        data-booking-url="{{ route('student.booking') }}"
        data-education-level="{{ auth()->user()?->student?->education_level ?? 'SMP' }}">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Pilihan Kelas KawalBelajar</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 md:text-3xl">Temukan kelas dan tutor yang sesuai</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">Pilih jenjang dan cara belajar untuk melihat kelas yang tersedia bersama tutor terverifikasi.</p>
            <div class="mt-6 grid gap-3 md:grid-cols-[1fr_1fr_2fr_auto]">
                <label class="text-xs font-semibold text-slate-700">Jenjang
                    <select x-model="educationLevel" x-on:change="load()" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        <option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA">SMA</option><option value="UMUM">Umum</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-700">Metode
                    <select x-model="learningMethod" x-on:change="load()" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        <option value="online_zoom">Daring melalui Zoom</option><option value="home_visit">Tutor datang ke rumah</option>
                    </select>
                </label>
                <label class="text-xs font-semibold text-slate-700">Cari tutor atau bidang
                    <input x-model="search" x-on:keydown.enter.prevent="load()" type="search" maxlength="100" placeholder="Masukkan nama tutor atau bidang keahlian" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                </label>
                <button type="button" x-on:click="load()" class="self-end rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-teal-800">Cari</button>
            </div>
        </div>

        <div x-show="error" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" x-text="error"></div>
        <div x-show="loading" class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500">Sedang mencari kelas yang tersedia…</div>
        <div x-show="initialized && !loading && !error && tutors.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <h2 class="font-bold text-slate-900">Belum ada tutor atau paket yang cocok</h2>
            <p class="mt-2 text-sm text-slate-500">Coba ubah jenjang, metode belajar, atau kata pencarian.</p>
        </div>

        <div class="grid gap-5 xl:grid-cols-2">
            <template x-for="tutor in tutors" :key="tutor.teacher_id">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-teal-700" x-text="tutor.teacher_level || 'Tutor terverifikasi'"></p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900" x-text="tutor.full_name || 'Tutor KawalBelajar'"></h2>
                            <p class="mt-1 text-sm text-slate-600" x-text="[tutor.focus_subject, tutor.university].filter(Boolean).join(' · ') || 'Informasi tutor belum tersedia'"></p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700" x-text="learningMethod === 'home_visit' ? 'Tutor datang ke rumah' : 'Daring melalui Zoom'"></span>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="pack in tutor.packages" :key="pack.package_id">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-slate-900" x-text="pack.package_name"></h3>
                                        <p class="mt-1 text-xs text-slate-500" x-text="pack.grade_level"></p>
                                        <p x-show="pack.description" class="mt-2 text-sm text-slate-600" x-text="pack.description"></p>
                                        <p x-show="learningMethod === 'home_visit' && pack.home_visit_area" class="mt-2 text-xs font-semibold text-teal-800" x-text="`Area Home Visit: ${pack.home_visit_area}`"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500">Biaya per sesi</p>
                                        <p class="font-bold text-teal-800" x-text="rupiah(pack.teacher_rate_per_session)"></p>
                                    </div>
                                </div>
                                <a :href="bookingUrl(tutor, pack)" class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-bold text-slate-900 hover:bg-amber-300 sm:w-auto">
                                    Pilih kelas dan jadwal
                                </a>
                            </div>
                        </template>
                    </div>
                </article>
            </template>
        </div>
    </section>
</x-student-layout>
