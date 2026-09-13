<x-app-layout>
    <x-slot:title>Bergabung Menjadi Mitra Pengajar - KawalBelajar</x-slot:title>

    <div class="max-w-5xl mx-auto py-8 px-4" x-data="tutorWizard">
        
        <!-- Header Banner -->
        <div class="bg-[#FFF5D6] border border-[#E2D2A4] rounded-2xl p-6 mb-8 shadow-sm">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#1E293B] mb-2 font-heading">
                Bergabung Menjadi Mitra Pengajar
            </h1>
            <p class="text-sm text-[#475569]">
                Isi data diri, tentukan jenjang spesialisasi mengajar mu, dan unggah dokumen pendukung untuk diverifikasi langsung oleh admin
            </p>
        </div>

        <!-- Wizard Stepper Tab -->
        <div class="relative flex items-center justify-between mb-8 overflow-hidden rounded-xl bg-gray-200">
            <div 
                class="w-1/2 py-3 px-6 text-center font-bold text-sm transition-all duration-300 relative flex items-center justify-center cursor-pointer"
                :class="step === 1 ? 'bg-[#2BB0C1] text-white' : 'bg-gray-200 text-gray-500'"
                @click="step = 1"
            >
                <span>Biodata Akademik & Peminatan Jenjang</span>
                <div class="absolute right-[-15px] top-0 bottom-0 w-0 h-0 border-t-[24px] border-t-transparent border-b-[24px] border-b-transparent border-l-[15px] z-10"
                     :class="step === 1 ? 'border-l-[#2BB0C1]' : 'border-l-gray-200'"></div>
            </div>

            <div 
                class="w-1/2 py-3 px-6 text-center font-bold text-sm transition-all duration-300 relative flex items-center justify-center cursor-pointer"
                :class="step === 2 ? 'bg-[#2BB0C1] text-white' : 'bg-gray-200 text-gray-500'"
                @click="step = 2"
            >
                <span>Unggah Berkas</span>
            </div>
        </div>

        <!-- Form Utama -->
        <form action="{{ route('tutor.register.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- ================= STEP 1: BIODATA & AKADEMIK ================= -->
            <div x-show="step === 1" x-cloak class="space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-[#1E293B]">Isi Identitas Diri & Riwayat Akademik</h2>
                    <p class="text-xs text-[#64748B]">Pastikan nomor whatsapp dan email aktif untuk penerbitan kredensial akun</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-[#1E293B] mb-1">Nama Lengkap beseta Gelar jika ada</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Contoh: Athaya Laily Syafitri, S.Pd" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1E293B] mb-1">Alamat Email Aktif</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="athay.lailyz@gmail.com" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1E293B] mb-1">Nomor WhatsApp Aktif</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="08XX-XXXX-XXXX" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1E293B] mb-1">Alamat Domisili Asal</label>
                        <input type="text" name="domicile_address" value="{{ old('domicile_address') }}" placeholder="Contoh: jl. Hasan basri, Kayu tangi, banjarmasin" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1] focus:outline-none">
                    </div>
                </div>

                <!-- Section Informasi Akademik -->
                <div class="bg-[#EBF2F7] rounded-2xl p-5 border border-gray-200">
                    <h3 class="text-sm font-bold text-[#1E293B] mb-3">Informasi Akademik</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1E293B] mb-1">Universitas / Kampus</label>
                            <input type="text" name="university" value="{{ old('university') }}" placeholder="Contoh : universitas Lambung mangkurat" class="w-full px-4 py-2 rounded-full border border-gray-300 text-xs focus:ring-2 focus:ring-[#2BB0C1] bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1E293B] mb-1">Program Studi</label>
                            <input type="text" name="study_program" value="{{ old('study_program') }}" placeholder="Contoh : Pendidikan Fisika" class="w-full px-4 py-2 rounded-full border border-gray-300 text-xs focus:ring-2 focus:ring-[#2BB0C1] bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1E293B] mb-1">Semester Saat ini/Lulus</label>
                            <input type="text" name="semester" value="{{ old('semester') }}" placeholder="Semester 6" class="w-full px-4 py-2 rounded-full border border-gray-300 text-xs focus:ring-2 focus:ring-[#2BB0C1] bg-white">
                        </div>
                    </div>
                </div>

                <!-- Pilihan Jenjang Mengajar -->
                <div>
                    <label class="block text-sm font-bold text-[#1E293B] mb-2">Pilih Pengajuan Jenjang Awal (Bisa Pilih lebih dari satu)</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="level in levels" :key="level.id">
                            <div 
                                class="p-4 rounded-xl border-2 transition-all cursor-pointer flex flex-col justify-between"
                                :class="selectedLevels.includes(level.id) ? 'bg-[#FFF5D6] border-[#F2C94C]' : 'bg-gray-100 border-gray-200'"
                                @click="toggleLevel(level.id)"
                            >
                                <div>
                                    <h4 class="font-bold text-base text-[#1E293B]" x-text="level.id"></h4>
                                    <p class="text-[11px] text-gray-500 mt-1" x-text="level.desc"></p>
                                </div>
                                <input type="checkbox" name="selected_levels[]" :value="level.id" :checked="selectedLevels.includes(level.id)" class="hidden">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Fokus Mata Pelajaran & Notice Box -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-xs font-bold text-[#1E293B] mb-1">Fokus Mata Pelajaran</label>
                        <input type="text" name="focus_subject" value="{{ old('focus_subject') }}" placeholder="Matematika & Fisika" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1]">
                    </div>
                    <div class="bg-[#D3F9D8] border border-[#A1E8AF] p-4 rounded-xl text-xs text-[#22543D]">
                        <strong>Catatan Verifikasi:</strong> Admin akan meninjau Transkrip Nilai/KHS dan CV kamu untuk mencocokkan kelayakan kualifikasi pada jenjang yang kamu centang.
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="button" @click="step = 2" class="bg-[#FFC043] hover:bg-[#F2B530] text-[#1E293B] font-bold px-8 py-3 rounded-full shadow transition-all flex items-center gap-2 text-sm">
                        <span>Selanjutnya</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>

            <!-- ================= STEP 2: UNGGAH BERKAS ================= -->
            <div x-show="step === 2" x-cloak class="space-y-6">
                <div>
                    <h2 class="text-xl font-extrabold text-[#1E293B]">Unggah Berkas Kualifikasi</h2>
                    <p class="text-xs text-[#64748B]">Unggah berkas resmi untuk proses verifikasi kredensial oleh Admin.</p>
                </div>

                <!-- Alert Server Validation Error (Diterima dari Controller / FormRequest) -->
                @if ($errors->any())
                    <div class="bg-[#FFDADA] border border-[#FF9B9B] rounded-xl p-4 text-[#C53030]">
                        <h4 class="font-bold text-sm">Validasi File Gagal</h4>
                        <ul class="text-xs mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Upload Boxes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 1. File CV -->
                    <div class="border border-gray-300 bg-[#F8FAFC] rounded-2xl p-6 text-center space-y-3">
                        <label class="block font-bold text-xs text-[#1E293B] text-left">1. Berkas CV (Curriculum Vitae)*</label>
                        <div class="py-4">
                            <label class="bg-[#2BB0C1] hover:bg-[#2392A0] text-white text-xs font-bold px-5 py-2.5 rounded-full cursor-pointer inline-block transition">
                                <span>Pilih File CV (.PDF)</span>
                                <input type="file" name="cv_file" @change="setFileName($event, 'cv')" accept=".pdf" class="hidden">
                            </label>
                            <p class="text-[10px] text-gray-400 mt-2" x-text="fileNames.cv || 'Hanya format .PDF (Maks. 10 MB)'"></p>
                        </div>
                    </div>

                    <!-- 2. KTP/KTM -->
                    <div class="border border-gray-300 bg-[#F8FAFC] rounded-2xl p-6 text-center space-y-3">
                        <label class="block font-bold text-xs text-[#1E293B] text-left">2. Foto KTP / KTM Mahasiswa *</label>
                        <div class="py-4">
                            <label class="bg-[#2BB0C1] hover:bg-[#2392A0] text-white text-xs font-bold px-5 py-2.5 rounded-full cursor-pointer inline-block transition">
                                <span>Unggah KTP/KTM</span>
                                <input type="file" name="ktp_ktm_file" @change="setFileName($event, 'ktp')" accept=".pdf,.jpg,.png" class="hidden">
                            </label>
                            <p class="text-[10px] text-gray-400 mt-2" x-text="fileNames.ktp || 'Format .PDF / .JPG / .PNG'"></p>
                        </div>
                    </div>

                    <!-- 3. Transkrip Nilai -->
                    <div class="border border-gray-300 bg-[#F8FAFC] rounded-2xl p-6 text-center space-y-3">
                        <label class="block font-bold text-xs text-[#1E293B] text-left">3. Scan Transkrip Nilai / KHS*</label>
                        <div class="py-4">
                            <label class="bg-[#2BB0C1] hover:bg-[#2392A0] text-white text-xs font-bold px-5 py-2.5 rounded-full cursor-pointer inline-block transition">
                                <span>Unggah KHS/Transkrip</span>
                                <input type="file" name="transcript_file" @change="setFileName($event, 'transcript')" accept=".pdf" class="hidden">
                            </label>
                            <p class="text-[10px] text-gray-400 mt-2" x-text="fileNames.transcript || 'Hanya format .PDF (Maks. 10 MB)'"></p>
                        </div>
                    </div>

                    <!-- 4. Sertifikat (Opsional) -->
                    <div class="border border-gray-300 bg-[#F8FAFC] rounded-2xl p-6 text-center space-y-3 relative">
                        <span class="absolute top-4 right-4 bg-gray-400 text-white text-[10px] font-bold px-3 py-0.5 rounded-full">Opsional</span>
                        <label class="block font-bold text-xs text-[#1E293B] text-left">4. Sertifikat Lomba / Keahlian</label>
                        <div class="py-4">
                            <label class="bg-[#2BB0C1] hover:bg-[#2392A0] text-white text-xs font-bold px-5 py-2.5 rounded-full cursor-pointer inline-block transition">
                                <span>Pilih Sertifikat</span>
                                <input type="file" name="certificate_file" @change="setFileName($event, 'certificate')" accept=".pdf" class="hidden">
                            </label>
                            <p class="text-[10px] text-gray-400 mt-2" x-text="fileNames.certificate || 'Hanya format .PDF (Maks. 10 MB)'"></p>
                        </div>
                    </div>
                </div>

                <!-- Link Video Micro-teaching -->
                <div>
                    <label class="block text-xs font-bold text-[#1E293B] mb-1">Link Video Perkenalan / Micro-teaching Singkat (Opsional - YouTube / Google Drive)</label>
                    <input type="url" name="video_link" value="{{ old('video_link') }}" placeholder="https://link//youtube" class="w-full px-4 py-2.5 rounded-full border border-gray-300 text-sm focus:ring-2 focus:ring-[#2BB0C1]">
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between pt-6">
                    <button type="button" @click="step = 1" class="text-xs font-bold text-[#1E293B] hover:underline flex items-center gap-1">
                        ← Kembali
                    </button>
                    <button type="submit" class="bg-[#FFC043] hover:bg-[#F2B530] text-[#1E293B] font-bold px-10 py-3 rounded-full shadow transition-all text-sm">
                        Kirim
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>