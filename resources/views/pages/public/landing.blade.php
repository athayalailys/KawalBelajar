<x-common.app-layout>
    <div class="max-w-6xl mx-auto px-4 md:px-8 space-y-20 py-4">
        
        <!-- 1. Promo Bar -->
        <div class="bg-gradient-to-r from-brand-dark via-brand-teal to-brand-yellow rounded-brand-xl p-6 text-brand-white flex justify-between items-center shadow-sm">
            <div>
                <h3 class="font-heading text-xl font-bold">7 Hari Gratis</h3>
                <p class="text-xs text-brand-light/90 mt-1">Coba Semua Fitur Secara Gratis</p>
                <button class="mt-4 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark font-bold text-xs px-5 py-2 rounded-full transition">Mulai Gratis</button>
            </div>
            <div class="flex gap-1.5">
                <span class="w-2.5 h-2.5 bg-brand-white rounded-full"></span>
                <span class="w-2.5 h-2.5 bg-brand-white/40 rounded-full"></span>
                <span class="w-2.5 h-2.5 bg-brand-white/40 rounded-full"></span>
            </div>
        </div>

        <!-- 2. Hero Section -->
        <section id="beranda" class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
            <div class="space-y-5">
                <span class="text-xs bg-brand-light text-brand-teal-hover px-3.5 py-1.5 rounded-full font-bold">Platform Bimbingan Belajar Modern</span>
                <h1 class="font-heading text-4xl md:text-5xl font-black leading-tight">
                    Belajar <span class="text-brand-yellow">Lebih Terarah</span> dengan Kawal<span class="text-brand-teal">Belajar</span>
                </h1>
                <p class="text-sm text-brand-dark/80 leading-relaxed max-w-lg">
                    Temukan tutor yang tepat dengan layanan belajar yang mudah, interaktif, dan fleksibel—siap mendampingimu kapan pun dan di mana pun.
                </p>
                <div class="pt-2">
                    <a href="#program" class="inline-block bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark font-bold px-8 py-3.5 rounded-full text-sm shadow-md transition">Jelajahi Kelas</a>
                </div>
            </div>

            <!-- Uploadable Admin Banner -->
            <div class="flex justify-center">
                @if($banner)
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title ?? 'Hero Banner' }}" class="rounded-brand-xl shadow-lg w-full max-w-md object-cover">
                @else
                    <div class="w-full max-w-md h-72 bg-brand-yellow/20 border-2 border-dashed border-brand-yellow rounded-brand-xl flex items-center justify-center font-bold text-brand-dark">
                        Banner Belum Diupload
                    </div>
                @endif
            </div>
        </section>

        <!-- 3. Key Benefit Mini Cards -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $benefits = [
                    ['title' => 'Tutor Terverifikasi', 'desc' => 'Akademis & Praktisi'],
                    ['title' => 'Pilihan Cara Belajar', 'desc' => 'Daring atau tatap muka'],
                    ['title' => 'Belajar Berkelompok', 'desc' => 'Biaya lebih hemat'],
                    ['title' => 'Rapor Perkembangan', 'desc' => 'Pantau kemajuan belajar'],
                ];
            @endphp
            @foreach($benefits as $item)
                <div class="bg-brand-dark text-brand-white p-5 rounded-brand shadow-sm">
                    <h4 class="font-heading font-bold text-sm text-brand-light">{{ $item['title'] }}</h4>
                    <p class="text-xs text-brand-light/70 mt-1">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </section>

        <!-- 4. Katalog Kelas dikelompokkan berdasarkan jenjang -->
        <section id="program" class="space-y-8 scroll-mt-24">
            <div class="text-center space-y-2">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-teal">Daftar Kelas KawalBelajar</span>
                <h2 class="font-heading text-3xl sm:text-4xl font-bold text-brand-dark tracking-tight">Pilih Kelas Sesuai Jenjang</h2>
                <p class="font-sans text-sm sm:text-base text-brand-dark/70">Buka jenjang untuk melihat kelas dan paket belajar yang tersedia.</p>
            </div>

            @if($programs->isNotEmpty())
                <div class="mx-auto max-w-4xl space-y-3">
                    @foreach($programs->groupBy(fn ($program) => trim((string) ($program->grade_level ?: 'Jenjang lainnya'))) as $gradeLevel => $levelPrograms)
                        <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" @if($loop->first) open @endif>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 bg-white px-5 py-4 font-heading font-bold text-brand-dark transition hover:bg-brand-light/50">
                                <span class="flex items-center gap-3"><span class="rounded-lg bg-brand-light px-3 py-1.5 text-xs font-extrabold uppercase text-brand-teal">{{ $gradeLevel }}</span><span class="text-xs font-medium text-slate-500">{{ $levelPrograms->count() }} kelas tersedia</span></span>
                                <span class="text-brand-teal transition group-open:rotate-180" aria-hidden="true">⌄</span>
                            </summary>
                            <div class="grid gap-4 border-t border-slate-100 bg-slate-50/70 p-4 sm:grid-cols-2">
                                @foreach($levelPrograms as $program)
                                    <article class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex-1">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-brand-teal">{{ $program->subject_name ?: $gradeLevel }}</p>
                                            <h3 class="mt-1 font-heading text-base font-extrabold text-brand-dark">{{ $program->package_name }}</h3>
                                            <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $program->description ?: 'Kelas bimbingan belajar bersama tutor KawalBelajar.' }}</p>
                                            <p class="mt-3 text-sm font-extrabold text-brand-dark">{{ $program->base_price !== null ? 'Mulai Rp ' . number_format((float) $program->base_price, 0, ',', '.') : 'Tarif dikonfirmasi saat pemesanan' }}</p>
                                        </div>
                                        <a href="{{ route('student.booking', ['package_id' => $program->package_id]) }}" class="mt-4 inline-flex items-center justify-center rounded-full bg-brand-yellow px-5 py-2.5 text-xs font-bold text-brand-dark transition hover:bg-brand-yellow-hover">Pilih kelas & cari tutor <span class="ml-2" aria-hidden="true">→</span></a>
                                    </article>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            @else
                <div class="mx-auto max-w-2xl rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <h3 class="font-heading text-lg font-bold text-brand-dark">Kelas sedang disiapkan</h3>
                    <p class="mt-2 text-sm text-slate-600">Paket belajar akan tampil di sini setelah tersedia.</p>
                </div>
            @endif
        </section>

        <!-- 5. Cara Kerja (Flow Arrow Banner) -->
        <section class="space-y-6">
            <div class="text-center space-y-1">
                <h2 class="font-heading text-3xl font-extrabold">Cara Kerja KawalBelajar</h2>
                <p class="text-xs text-brand-dark/70">Langkah mudah memulai bimbingan belajar berkualitas</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 bg-brand-dark text-brand-white rounded-brand-xl overflow-hidden p-2 text-xs">
                <div class="p-4 bg-brand-dark rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-light block mb-1">1. Pilih Kebutuhan & Jenjang</span>
                    <p class="text-[11px] text-brand-light/70">Tentukan jenjang SD, SMP, SMA, UTBK sesuai fokus belajar.</p>
                </div>
                <div class="p-4 bg-brand-teal/40 rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-light block mb-1">2. Pilih Tutor Terverifikasi</span>
                    <p class="text-[11px] text-brand-light/70">Temukan tutor berpengalaman yang sesuai dengan kebutuhan belajarmu.</p>
                </div>
                <div class="p-4 bg-brand-teal/60 rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-white block mb-1">3. Atur kelompok dan jadwal</span>
                    <p class="text-[11px] text-brand-light/90">Pilih belajar privat atau bersama teman dengan jadwal yang fleksibel.</p>
                </div>
                <div class="p-4 bg-brand-teal rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-white block mb-1">4. Tes awal dan evaluasi</span>
                    <p class="text-[11px] text-brand-light/90">Pantau kemajuan melalui tes dan rapor belajar.</p>
                </div>
            </div>
        </section>

        <!-- 6. Dynamic Tutor Section (ERD Table: guru & users) -->
        <section id="tutor" class="bg-brand-dark rounded-brand-xl p-8 md:p-12 text-brand-white space-y-8">
            <div class="text-center space-y-1">
                <h2 class="font-heading text-3xl font-extrabold text-brand-white">Tutor Kami</h2>
                <p class="text-xs text-brand-light/70">Temukan tutor berpengalaman yang siap mendampingimu.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($tutors as $tutor)
                    <x-landing.tutor-card
                        :name="$tutor->user->full_name ?? 'Tutor Pilihan'"
                        :location="$tutor->default_location ?? 'Daring atau tatap muka di rumah'"
                        :subject="$tutor->focus_subject ?? 'Tutor Terverifikasi'"
                    />
                @empty
                    <p class="text-xs text-brand-light/60 col-span-3 text-center py-6">Data tutor belum tersedia.</p>
                @endforelse
            </div>
        </section>

    </div>
</x-common.app-layout>
