@props([
    'name' => 'Nama Tutor',
    'location' => 'Online / Home Visit',
    'subject' => 'Mata Pelajaran',
    'badge' => 'Terverifikasi',
    'rating' => 5.0,
    'reviewsCount' => 0,
    'profileUrl' => '#profil',
    'photoUrl' => null
])

<div class="bg-[#3D525E] border border-white/5 p-6 sm:p-7 rounded-brand-xl flex flex-col justify-between text-white w-full shadow-md hover:bg-[#435B69] transition duration-200 h-full">
    <div>
        <!-- Profil Header & Avatar -->
        <div class="flex items-center space-x-4 mb-6">
            <!-- Box Avatar Kuning / Render Foto -->
            <div class="w-16 h-16 rounded-2xl bg-brand-yellow flex-shrink-0 flex items-center justify-center overflow-hidden shadow-inner">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-8 h-8 text-brand-dark/40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                @endif
            </div>

            <div class="space-y-1 min-w-0">
                <span class="text-xs font-semibold text-brand-teal block truncate">
                    {{ $badge }}
                </span>
                <h3 class="font-heading text-lg sm:text-xl font-bold text-white leading-tight truncate">
                    {{ $name }}
                </h3>
                <p class="font-sans text-xs sm:text-sm text-white/70 truncate">
                    {{ $subject }} • {{ $location }}
                </p>
            </div>
        </div>

        <!-- Rating Bintang -->
        <div class="flex items-center space-x-2 mb-6">
            <div class="flex items-center text-brand-yellow">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= floor($rating))
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-white/30 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endif
                @endfor
            </div>
            <span class="font-sans text-xs text-white/80 font-medium">
                {{ $rating }} ({{ $reviewsCount }})
            </span>
        </div>
    </div>

    <!-- Tombol CTA Navigasi -->
    <a href="{{ $profileUrl }}" class="w-full block text-center font-sans font-bold text-sm bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark py-3 rounded-full shadow-sm transition duration-200">
        Lihat Profil
    </a>
</div>