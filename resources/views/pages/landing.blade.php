<x-app-layout>
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
                    Temukan Tutor Terbaikmu, Akses Mudah, Interaktif dan Fleksibel. Siap membantumu kapanpun dan dimanapun.
                </p>
                <div class="pt-2">
                    <a href="#paket" class="inline-block bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark font-bold px-8 py-3.5 rounded-full text-sm shadow-md transition">Daftar Sekarang</a>
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
                    ['title' => 'Lokasi Fleksibel', 'desc' => 'Online & Tatap Muka'],
                    ['title' => 'Squad Referral', 'desc' => 'Hemat hingga 50%'],
                    ['title' => 'Rapor Evaluasi', 'desc' => 'Pantau Berkala'],
                ];
            @endphp
            @foreach($benefits as $item)
                <div class="bg-brand-dark text-brand-white p-5 rounded-brand shadow-sm">
                    <h4 class="font-heading font-bold text-sm text-brand-light">{{ $item['title'] }}</h4>
                    <p class="text-xs text-brand-light/70 mt-1">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </section>

        <!-- 4. Program Unggulan Section -->
        <section class="space-y-8">
        <div class="text-center space-y-2">
            <h2 class="font-heading text-3xl sm:text-4xl font-bold text-brand-dark tracking-tight">
                Program Unggulan
            </h2>
            <p class="font-sans text-sm sm:text-base text-brand-dark/70">
                Pilihan jenjang belajar yang disesuaikan dengan kebutuhan dan kurikulum siswa
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($programs ?? [] as $program)
                <!-- Binding data dinamis dari Controller ke x-cards.program -->
                <x-cards.program 
                    :badge="$program->grade_level"
                    :title="$program->package_name"
                    :description="$program->description"
                    :actionUrl="route('booking.index', ['package_id' => $program->package_id])"
                />
            @empty
                <!-- Fallback statis jika data database belum di-seed -->
                <x-cards.program 
                    badge="SD / Sederajat"
                    title="Paket SD Tematik"
                    description="Pendampingan belajar PR, pemahaman konsep dasar matematika, dan persiapan ujian sekolah."
                    actionUrl="#daftar"
                />
                <x-cards.program 
                    badge="SMP / Sederajat"
                    title="Paket SMP Reguler"
                    description="Fokus pendalaman materi IPA, IPS, Matematika, dan persiapan Asesmen Nasional."
                    actionUrl="#daftar"
                />
                <x-cards.program 
                    badge="SMA / UTBK"
                    title="Paket SMA & UTBK-SNBT"
                    description="Persiapan intensif penulisan soal penalaran umum, literasi, dan persiapan masuk PTN favorit."
                    actionUrl="#daftar"
                />
            @endforelse
        </div>
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
                    <p class="text-[11px] text-brand-light/70">Cari kecocokan gaya belajar dengan tutor berpengalaman.</p>
                </div>
                <div class="p-4 bg-brand-teal/60 rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-white block mb-1">3. Bikin Squad & Jadwal</span>
                    <p class="text-[11px] text-brand-light/90">Belajar privat atau squad bareng teman dengan waktu fleksibel.</p>
                </div>
                <div class="p-4 bg-brand-teal rounded-brand flex flex-col justify-center">
                    <span class="font-bold text-brand-white block mb-1">4. Pre-Test & Evaluasi</span>
                    <p class="text-[11px] text-brand-light/90">Ukur progres berkala lewat asesmen dan rapor perkembangan.</p>
                </div>
            </div>
        </section>

        <!-- 6. Dynamic Tutor Section (ERD Table: guru & users) -->
        <section id="tutor" class="bg-brand-dark rounded-brand-xl p-8 md:p-12 text-brand-white space-y-8">
            <div class="text-center space-y-1">
                <h2 class="font-heading text-3xl font-extrabold text-brand-white">Tutor Kami</h2>
                <p class="text-xs text-brand-light/70">Temukan tutor berpengalaman yang siap membimbingmu</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($tutors as $tutor)
                    <x-tutor-card 
                        :name="$tutor->user->nama_lengkap ?? 'Tutor Pilihan'" 
                        :location="$tutor->lokasi_default" 
                        :image="$tutor->avatar_url" 
                    />
                @empty
                    <p class="text-xs text-brand-light/60 col-span-3 text-center py-6">Data tutor belum tersedia.</p>
                @endforelse
            </div>
        </section>

        <!-- 7. Dynamic Paket Belajar Section (ERD Table: paket_modul) -->
        <section id="paket" class="space-y-8">
            <div class="text-center space-y-1">
                <h2 class="font-heading text-3xl font-extrabold">Pilih Paket Belajar</h2>
                <p class="text-xs text-brand-dark/70">Investasi terbaik untuk masa depan prestasimu</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($paketBelajar as $paket)
                    <x-pricing-card 
                        :badge="$paket->jenjang" 
                        :title="$paket->nama_paket" 
                        :price="number_format($paket->harga_dasar, 0, ',', '.')" 
                        :features="explode(',', $paket->deskripsi ?? 'Akses Modul Belajar,Sesi Konsultasi Interaktif,Pre-test & Post-test,Rapor Belajar Siswa')" 
                    />
                @empty
                    <p class="text-xs text-brand-dark/60 col-span-3 text-center">Paket belum tersedia.</p>
                @endforelse
            </div>
            
            <p class="text-center text-xs text-brand-dark/60 pt-2">Pembayaran aman via Transfer Bank & QRIS</p>
        </section>

    </div>
</x-app-layout>