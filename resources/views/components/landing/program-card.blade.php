@props([
    'badge' => 'Jenjang Kelas',
    'title' => 'Judul Program',
    'description' => 'Deskripsi kurikulum dan materi pembelajaran yang akan dipelajari.',
    'buttonText' => 'Daftar Kelas',
    'actionUrl' => '#daftar'
])

<div class="bg-white rounded-brand-xl p-7 sm:p-8 border border-brand-light shadow-sm flex flex-col justify-between h-full hover:shadow-md transition-shadow duration-200">
    <div class="space-y-4">
        <!-- Tag / Badge Jenjang Kelas -->
        <div>
            <span class="inline-block px-4 py-1.5 rounded-full bg-brand-light text-brand-teal font-sans text-xs font-semibold">
                {{ $badge }}
            </span>
        </div>

        <!-- Judul Program -->
        <h3 class="font-heading text-xl sm:text-2xl font-bold text-brand-dark tracking-tight">
            {{ $title }}
        </h3>

        <!-- Deskripsi Ringkas -->
        <p class="font-sans text-xs sm:text-sm text-brand-dark/70 leading-relaxed">
            {{ $description }}
        </p>
    </div>

    <!-- Tombol CTA Navigasi -->
    <div class="pt-6">
        <a href="{{ $actionUrl }}" class="w-full block py-3.5 text-center font-sans font-bold text-sm bg-brand-yellow hover:bg-brand-yellow-hover text-brand-dark rounded-full shadow-sm transition duration-200">
            {{ $buttonText }}
        </a>
    </div>
</div>