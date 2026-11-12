<x-common.landing-layout>

    {{-- 1. Hero Section --}}
    <section class="py-12 md:py-20 text-center space-y-6">
        <h1 class="font-heading text-4xl sm:text-6xl font-bold text-brand-dark tracking-tight leading-tight max-w-4xl mx-auto">
            Belajar <span class="text-brand-yellow">Lebih Terarah</span> bersama <span class="text-brand-teal">KawalBelajar</span>
        </h1>
        <p class="font-sans text-brand-dark/80 text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed">
            Platform pendampingan belajar interaktif untuk membantu pembelajar meraih prestasi akademis terbaik.
        </p>
        <div class="pt-4 flex items-center justify-center space-x-4 font-sans">
            <a href="#program" class="px-8 py-3.5 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark font-bold rounded-brand shadow-sm transition">
                Jelajahi Kelas
            </a>
            <a href="#tutor" class="px-8 py-3.5 border-2 border-brand-teal text-brand-teal hover:bg-brand-light/30 font-semibold rounded-brand transition">
                Temukan Tutor
            </a>
        </div>
    </section>

    {{-- 2. Banner Section (Dinamis jika data banner ada) --}}
    @if(isset($banners) && count($banners) > 0)
        <section class="py-8 container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($banners as $banner)
                    <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                        <img src="{{ asset($banner->image_url) }}" alt="{{ $banner->title }}" class="w-full h-48 object-cover">
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 3. Program Unggulan --}}
    <section id="program" class="py-12 bg-white/50 my-8">
        <div class="container mx-auto px-4">
            <h2 class="font-heading text-3xl font-bold text-center mb-8 text-brand-dark">Kelas Belajar Populer</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($programs ?? [] as $program)
                    <x-landing.program-card :badge="$program->grade_level" :title="$program->package_name" :description="$program->description" />
                @empty
                    <p class="text-center text-gray-500 col-span-3">Belum ada kelas yang tersedia saat ini.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- 4. Tutor / Pengajar --}}
    <section id="tutor" class="py-12 container mx-auto px-4">
        <h2 class="font-heading text-3xl font-bold text-center mb-8 text-brand-dark">Tutor KawalBelajar</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @forelse($tutors ?? [] as $tutor)
                <x-landing.tutor-card :name="$tutor->user->full_name ?? 'Tutor Pilihan'" :location="$tutor->default_location ?? 'Daring atau tatap muka di rumah'" :subject="$tutor->focus_subject ?? 'Tutor Terverifikasi'" />
            @empty
                <p class="text-center text-gray-500 col-span-3">Belum ada data tutor yang ditampilkan.</p>
            @endforelse
        </div>
    </section>

</x-common.landing-layout>
