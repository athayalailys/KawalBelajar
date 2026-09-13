@props([
    'guru',          // Instance App\Models\Guru (relasi 'user')
    'paket',         // Instance App\Models\PaketModul
    'tarif' => null, // Optional: App\Models\TutorPaketTarif
    'formatKelas' => 'Individu (1-on-1)',
    'jumlahSesi' => '4 Sesi/Bln (60 Menit/Sesi)',
    'tipeJadwal' => 'FLEXIBLE'
])

@php
    $totalHarga = $tarif?->tarif_kategori ?? $paket->harga_dasar;
@endphp

<div class="space-y-6">
    <!-- Tombol Trigger Booking Paket (Sesuai Kiri Atas UI) -->
    <div>
        <button 
            type="button" 
            class="bg-brand-white border border-brand-dark/20 font-bold text-xs px-4 py-2 rounded-xl shadow-xs hover:bg-brand-bg transition"
        >
            Booking Paket
        </button>
    </div>

    <!-- Container Utama Halaman Pembayaran -->
    <div class="bg-brand-white rounded-brand-xl p-8 border border-brand-light shadow-sm space-y-6">
        
        <!-- Header Section -->
        <div class="space-y-1">
            <span class="text-brand-teal font-bold text-sm">Konfirmasi Booking Paket</span>
            <h2 class="font-heading text-2xl md:text-3xl font-black">Halaman Pembayaran</h2>
        </div>

        <!-- Warning Callout Box -->
        <div class="bg-brand-yellow/15 border border-brand-yellow/40 rounded-brand p-4 text-xs font-semibold space-y-2">
            <div class="flex items-start gap-2">
                <span class="text-brand-yellow">•</span>
                <p>Sesi belajar yang sudah dipesan tidak dapat dibatalkan H-1</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-brand-yellow">•</span>
                <p>Bebas Biaya Admin untuk seluruh metode pembayaran Xendit</p>
            </div>
        </div>

        <!-- Rincian Order (Strictly Dynamic Database Fields) -->
        <div class="bg-brand-bg/60 rounded-brand p-6 space-y-3 border border-brand-light/30 text-xs">
            <div class="flex justify-between items-center">
                <span class="opacity-70">Tutor & Program:</span>
                <span class="font-bold">
                    {{ $guru->user->nama_lengkap ?? 'Alya Rosaan, S.Kom.' }} ({{ $paket->nama_paket }} - {{ $paket->jenjang }})
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="opacity-70">Format Kelas:</span>
                <span class="font-bold text-brand-teal">{{ $formatKelas }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="opacity-70">Jumlah Sesi & Durasi:</span>
                <span class="font-bold">{{ $jumlahSesi }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="opacity-70">Tipe Jadwal:</span>
                <span class="font-bold tracking-wider">{{ strtoupper($tipeJadwal) }}</span>
            </div>

            <div class="pt-4 border-t border-brand-dark/10 flex justify-between items-center">
                <span class="font-bold">Total Pembayaran (Semua Siswa):</span>
                <span class="font-heading text-xl font-bold text-brand-teal">
                    Rp{{ number_format($totalHarga, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Form Submit & Action Footer -->
        <form action="{{ route('booking.checkout') }}" method="POST" class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            @csrf
            <!-- Hidden Inputs Data ERD -->
            <input type="hidden" name="guru_id" value="{{ $guru->guru_id }}">
            <input type="hidden" name="paket_id" value="{{ $paket->paket_id }}">
            <input type="hidden" name="total_bayar" value="{{ $totalHarga }}">

            <div class="space-y-0.5 text-center sm:text-left">
                <span class="text-xs opacity-70 font-medium block">Total Biaya/Paket</span>
                <div class="font-heading text-3xl font-black text-brand-teal">
                    Rp {{ number_format($totalHarga, 0, ',', '.') }}
                </div>
            </div>

            <div class="flex items-center gap-4 w-full sm:w-auto justify-end">
                <a 
                    href="?step=4&guru_id={{ $guru->guru_id }}&paket_id={{ $paket->paket_id }}" 
                    class="opacity-70 hover:opacity-100 font-bold text-sm px-4 py-2 transition"
                >
                    Batal
                </a>
                
                <button 
                    type="submit" 
                    class="bg-brand-yellow hover:bg-brand-yellow-hover font-bold text-sm px-8 py-3.5 rounded-full border border-brand-dark shadow-sm transition flex items-center gap-2"
                >
                    <span>Lanjut Pembayaran</span>
                    <span>→</span>
                </button>
            </div>
        </form>

    </div>
</div>