<x-student-layout title="Beranda Siswa - KawalBelajar">
    <div x-data="studentDashboard()" class="space-y-6">
        
        <!-- Hero Greeting Card -->
        <div class="relative bg-white border-2 border-slate-200/90 rounded-3xl p-6 lg:p-8 shadow-sm">
            <!-- Jenjang Badge Top Right -->
            <div class="absolute top-6 right-6">
                <span class="inline-flex items-center gap-1.5 bg-sky-100/80 text-sky-900 text-xs font-bold px-3.5 py-1.5 rounded-full border border-sky-200/60">
                    <span>🎓</span>
                    <span>{{ $student->grade_level ?? 'Jenjang SMP' }}</span>
                </span>
            </div>

            <!-- Header & Text -->
            <div class="max-w-2xl space-y-3">
                <h2 class="text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Halo, Selamat Belajar, {{ $student->name ?? 'Rizky' }}! 🚀
                </h2>
                <p class="text-slate-600 text-sm lg:text-base leading-relaxed">
                    Ada <span class="font-semibold text-slate-900 border-b border-slate-400">1 sesi bimbingan tatap muka terjadwal hari ini</span>. Selesaikan Pre-Test diagnostik sebelum sesi dimulai untuk membuka tautan koordinat dan materi bimbingan.
                </p>
            </div>
        </div>

        <!-- 3 Quick Action Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Card 1: Cari & Pesan Kelas Baru -->
            <a href="{{ route('student.booking') }}" 
               class="group border-2 border-slate-900 rounded-2xl p-4 bg-white shadow-sm hover:shadow-md hover:border-amber-500 transition-all duration-200 flex items-center gap-4 cursor-pointer">
                <div class="w-14 h-14 bg-[#FFD54F] border-2 border-slate-900 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="text-2xl">🔍</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base leading-snug group-hover:text-amber-600 transition-colors">
                        Cari & Pesan Kelas Baru
                    </h3>
                    <p class="text-slate-500 text-xs font-medium mt-0.5">
                        Wizard eksplorasi bimbingan
                    </p>
                </div>
            </a>

            <!-- Card 2: Buka Rapor & Evaluasi -->
            <a href="{{ Route::has('student.report') ? route('student.report') : '#rapor' }}" 
               class="group border-2 border-slate-900 rounded-2xl p-4 bg-white shadow-sm hover:shadow-md hover:border-sky-500 transition-all duration-200 flex items-center gap-4 cursor-pointer">
                <div class="w-14 h-14 bg-[#B2EBF2] border-2 border-slate-900 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="text-2xl">📊</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base leading-snug group-hover:text-sky-600 transition-colors">
                        Buka Rapor & Evaluasi
                    </h3>
                    <p class="text-slate-500 text-xs font-medium mt-0.5">
                        Grafik progres Pre-Post Test
                    </p>
                </div>
            </a>

            <!-- Card 3: Akses Orang Tua -->
            <a href="{{ Route::has('student.parent') ? route('student.parent') : '#laman-orang-tua' }}" 
               class="group border-2 border-slate-900 rounded-2xl p-4 bg-white shadow-sm hover:shadow-md hover:border-rose-500 transition-all duration-200 flex items-center gap-4 cursor-pointer">
                <div class="w-14 h-14 bg-[#FFCDD2] border-2 border-slate-900 rounded-xl flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <span class="text-2xl">🛡️</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base leading-snug group-hover:text-rose-600 transition-colors">
                        Akses Orang Tua
                    </h3>
                    <p class="text-slate-500 text-xs font-medium mt-0.5">
                        Pemantauan dengan PIN 6-digit
                    </p>
                </div>
            </a>

        </div>

    </div>

    <script>
        function studentDashboard() {
            return {
                init() {
                    // Logic Alpine.js jika diperlukan
                }
            }
        }
    </script>
</x-student-layout>