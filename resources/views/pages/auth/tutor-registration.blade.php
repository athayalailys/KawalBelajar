<x-common.app-layout>
    <x-slot:title>Bergabung Menjadi Mitra Pengajar - KawalBelajar</x-slot:title>

    <main class="mx-auto max-w-5xl px-4 py-8" x-data="tutorWizard"
        x-init="selectedLevels = @js(old('selected_levels', ['SMP'])); step = @js($errors->hasAny(['cv_file', 'ktp_ktm_file', 'transcript_file', 'certificate_file', 'video_link']) ? 2 : max(1, min(2, (int) ($step ?? 1))))">

        <section class="mb-8 rounded-2xl border border-amber-200/80 bg-amber-50/60 p-6">
            <h1 class="mb-1 text-2xl font-bold text-slate-900">Bergabung Menjadi Mitra Pengajar KawalBelajar</h1>
            <p class="mb-6 text-sm text-slate-600">Lengkapi data diri, pilih jenjang pengajaran, dan unggah dokumen untuk diverifikasi admin.</p>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <article class="flex items-start gap-3 rounded-xl border border-amber-200 bg-white p-4">
                    <span class="rounded-lg bg-amber-100 p-2" aria-hidden="true">&#127891;</span>
                    <div><h2 class="text-xs font-bold text-slate-900">Profil Terverifikasi</h2><p class="text-xs text-slate-500">Dokumen ditinjau sebelum akun pengajar aktif.</p></div>
                </article>
                <article class="flex items-start gap-3 rounded-xl border border-amber-200 bg-white p-4">
                    <span class="rounded-lg bg-amber-100 p-2" aria-hidden="true">&#128218;</span>
                    <div><h2 class="text-xs font-bold text-slate-900">Pilih Jenjang Mengajar</h2><p class="text-xs text-slate-500">Ajukan satu atau beberapa jenjang sesuai kualifikasi.</p></div>
                </article>
                <article class="flex items-start gap-3 rounded-xl border border-amber-200 bg-white p-4">
                    <span class="rounded-lg bg-amber-100 p-2" aria-hidden="true">&#128221;</span>
                    <div><h2 class="text-xs font-bold text-slate-900">Unggah Dokumen</h2><p class="text-xs text-slate-500">CV, identitas, dan transkrip mendukung proses verifikasi.</p></div>
                </article>
            </div>
        </section>

        @if (session('success'))
            <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
                <h2 class="text-sm font-bold">Periksa kembali data pendaftaran</h2>
                <ul class="mt-1 list-inside list-disc text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav aria-label="Tahapan pendaftaran" class="mb-8 grid grid-cols-2 overflow-hidden rounded-xl bg-slate-200">
            <button type="button" @click="step = 1" :aria-current="step === 1 ? 'step' : null"
                :class="step === 1 ? 'bg-amber-400 text-slate-900' : 'bg-slate-200 text-slate-500'"
                class="px-4 py-3 text-center text-sm font-bold transition sm:px-6">
                1. Biodata & Akademik
            </button>
            <button type="button" @click="step = 2" :aria-current="step === 2 ? 'step' : null"
                :class="step === 2 ? 'bg-amber-400 text-slate-900' : 'bg-slate-200 text-slate-500'"
                class="px-4 py-3 text-center text-sm font-bold transition sm:px-6">
                2. Unggah Berkas
            </button>
        </nav>

        <form action="{{ route('register.tutor.store') }}" method="POST" enctype="multipart/form-data" novalidate class="space-y-8">
            @csrf

            <section x-show="step === 1" x-cloak class="space-y-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Isi Identitas Diri & Riwayat Akademik</h2>
                    <p class="mb-4 text-xs text-slate-500">Pastikan nomor WhatsApp dan email aktif untuk komunikasi pendaftaran.</p>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="full_name" class="mb-1 block text-xs font-bold text-slate-700">Nama Lengkap beserta Gelar *</label>
                            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" placeholder="Contoh: Athaya Laily Syafitri, S.Pd" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="email" class="mb-1 block text-xs font-bold text-slate-700">Alamat Email Aktif *</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="phone_number" class="mb-1 block text-xs font-bold text-slate-700">Nomor WhatsApp Aktif *</label>
                            <input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" placeholder="08XX-XXXX-XXXX" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="domicile_address" class="mb-1 block text-xs font-bold text-slate-700">Alamat Domisili *</label>
                            <input id="domicile_address" type="text" name="domicile_address" value="{{ old('domicile_address') }}" required autocomplete="street-address" placeholder="Kecamatan, kota/kabupaten" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </div>

                <section class="rounded-2xl border border-slate-200 bg-sky-50/70 p-5">
                    <h3 class="mb-3 text-sm font-bold text-slate-900">Informasi Akademik</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="university" class="mb-1 block text-xs font-semibold text-slate-700">Universitas / Kampus *</label>
                            <input id="university" type="text" name="university" value="{{ old('university') }}" required placeholder="Nama universitas" class="w-full rounded-full border border-slate-900 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="study_program" class="mb-1 block text-xs font-semibold text-slate-700">Program Studi *</label>
                            <input id="study_program" type="text" name="study_program" value="{{ old('study_program') }}" required placeholder="Contoh: Pendidikan Fisika" class="w-full rounded-full border border-slate-900 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label for="semester" class="mb-1 block text-xs font-semibold text-slate-700">Semester Saat Ini / Tahun Lulus *</label>
                            <input id="semester" type="text" name="semester" value="{{ old('semester') }}" required placeholder="Contoh: Semester 6" class="w-full rounded-full border border-slate-900 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </section>

                <fieldset>
                    <legend class="mb-3 text-base font-bold text-slate-900">Pilih Jenjang Pengajuan *</legend>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <template x-for="level in levels" :key="level.id">
                            <label class="relative flex cursor-pointer flex-col justify-between rounded-xl p-4 transition"
                                :class="selectedLevels.includes(level.id) ? 'border-2 border-slate-900 bg-amber-100' : 'border border-slate-300 bg-white hover:border-amber-400'">
                                <input type="checkbox" name="selected_levels[]" :value="level.id" :checked="selectedLevels.includes(level.id)" @change="toggleLevel(level.id)" class="sr-only">
                                <span class="mb-2 flex items-center justify-between">
                                    <span class="text-xl" aria-hidden="true">&#128218;</span>
                                    <span x-show="selectedLevels.includes(level.id)" class="flex h-5 w-5 items-center justify-center rounded-full bg-teal-600 text-xs font-bold text-white" aria-hidden="true">&#10003;</span>
                                </span>
                                <span class="text-sm font-bold text-slate-900" x-text="level.id"></span>
                                <span class="mb-3 mt-1 text-[11px] text-slate-500" x-text="level.desc"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Jenjang pengajuan</span>
                            </label>
                        </template>
                    </div>
                    @error('selected_levels') <p class="mt-2 text-xs text-rose-600">{{ $message }}</p> @enderror
                </fieldset>

                <div class="grid grid-cols-1 items-center gap-4 md:grid-cols-2">
                    <div>
                        <label for="focus_subject" class="mb-1 block text-xs font-bold text-slate-700">Fokus Mata Pelajaran *</label>
                        <input id="focus_subject" type="text" name="focus_subject" value="{{ old('focus_subject') }}" required placeholder="Matematika dan Fisika" class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <aside class="rounded-2xl border border-emerald-300 bg-emerald-100/70 p-5 text-xs leading-relaxed text-slate-700">
                        <p class="mb-1 font-semibold text-slate-900">Catatan Verifikasi</p>
                        <p>Admin akan meninjau transkrip nilai atau KHS dan CV untuk mencocokkan kualifikasi pada jenjang yang diajukan.</p>
                    </aside>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" @click="step = 2" class="flex items-center gap-2 rounded-full border-2 border-slate-900 bg-amber-400 px-8 py-3 text-sm font-bold text-slate-900 shadow-sm transition hover:bg-amber-500">
                        Selanjutnya <span aria-hidden="true">&#8594;</span>
                    </button>
                </div>
            </section>

            <section x-show="step === 2" x-cloak class="space-y-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Unggah Berkas Kualifikasi</h2>
                    <p class="text-xs text-slate-500">Berkas resmi digunakan untuk proses verifikasi oleh admin.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5">
                        <label for="cv_file" class="block text-xs font-bold text-slate-900">1. Berkas CV (PDF) *</label>
                        <label for="cv_file" class="inline-flex cursor-pointer rounded-full bg-teal-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-700">Pilih File CV
                            <input id="cv_file" type="file" name="cv_file" required @change="handleFileUpload($event, 'cv')" accept=".pdf,application/pdf" class="sr-only">
                        </label>
                        <p class="text-[10px] text-slate-500" x-text="fileNames.cv || 'PDF, maksimal 10 MB'"></p>
                    </div>
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5">
                        <label for="ktp_ktm_file" class="block text-xs font-bold text-slate-900">2. Foto KTP / KTM *</label>
                        <label for="ktp_ktm_file" class="inline-flex cursor-pointer rounded-full bg-teal-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-700">Pilih KTP / KTM
                            <input id="ktp_ktm_file" type="file" name="ktp_ktm_file" required @change="handleFileUpload($event, 'ktp')" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="sr-only">
                        </label>
                        <p class="text-[10px] text-slate-500" x-text="fileNames.ktp || 'PDF, JPG, atau PNG; maksimal 10 MB'"></p>
                    </div>
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5">
                        <label for="transcript_file" class="block text-xs font-bold text-slate-900">3. Transkrip Nilai / KHS (PDF) *</label>
                        <label for="transcript_file" class="inline-flex cursor-pointer rounded-full bg-teal-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-700">Pilih Transkrip / KHS
                            <input id="transcript_file" type="file" name="transcript_file" required @change="handleFileUpload($event, 'transcript')" accept=".pdf,application/pdf" class="sr-only">
                        </label>
                        <p class="text-[10px] text-slate-500" x-text="fileNames.transcript || 'PDF, maksimal 10 MB'"></p>
                    </div>
                    <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-2">
                            <label for="certificate_file" class="block text-xs font-bold text-slate-900">4. Sertifikat Lomba / Keahlian</label>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600">Opsional</span>
                        </div>
                        <label for="certificate_file" class="inline-flex cursor-pointer rounded-full bg-teal-600 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-teal-700">Pilih Sertifikat
                            <input id="certificate_file" type="file" name="certificate_file" @change="handleFileUpload($event, 'certificate')" accept=".pdf,application/pdf" class="sr-only">
                        </label>
                        <p class="text-[10px] text-slate-500" x-text="fileNames.certificate || 'PDF, maksimal 10 MB'"></p>
                    </div>
                </div>

                <div>
                    <label for="video_link" class="mb-1 block text-xs font-bold text-slate-700">Tautan video perkenalan atau simulasi mengajar (opsional)</label>
                    <input id="video_link" type="url" name="video_link" value="{{ old('video_link') }}" placeholder="https://youtube.com/..." class="w-full rounded-full border border-slate-900 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>

                <p x-show="errorMessage" x-cloak role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-800" x-text="errorMessage"></p>

                <div class="flex items-center justify-between border-t border-slate-200 pt-5">
                    <button type="button" @click="step = 1" class="flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-slate-900">
                        <span aria-hidden="true">&#8592;</span> Kembali
                    </button>
                    <button type="submit" class="rounded-full border-2 border-slate-900 bg-amber-400 px-10 py-3 text-sm font-bold text-slate-900 shadow-sm transition hover:bg-amber-500">Kirim Pendaftaran</button>
                </div>
            </section>
        </form>
    </main>
</x-common.app-layout>
