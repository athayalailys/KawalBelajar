<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bergabung Menjadi Pembelajar - KawalBelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">

    <!-- NAVBAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-teal-700 text-xl">
                <span>🎓 KawalBelajar</span>
            </div>
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="#" class="hover:text-teal-600">Beranda</a>
                <a href="#" class="hover:text-teal-600">Program</a>
                <a href="#" class="hover:text-teal-600">Menjadi Tutor</a>
                <a href="#" class="hover:text-teal-600">Penawaran</a>
            </nav>
            <div class="flex items-center gap-3">
                <a href="/login" class="text-sm font-semibold px-4 py-2 hover:text-teal-600">Masuk</a>
                <a href="#" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 text-sm font-bold px-5 py-2 rounded-full">Daftar Gratis</a>
            </div>
        </div>
    </header>

    <!-- FORM CONTAINER WITH ALPINE.JS STATE -->
    <main class="max-w-5xl mx-auto px-4 py-8" x-data="studentRegisterForm()">
        
        <!-- HERO BANNER -->
        <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-6 mb-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-1">Bergabung Menjadi Pembelajar KawalBelajar</h1>
            <p class="text-slate-600 text-sm mb-6">Lengkapi data siswa dan pengamanan orang tua untuk bimbingan tatap muka (Home Visit) dan kelas daring bergaransi.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                    <span class="p-2 bg-amber-100 rounded-lg">📖</span>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Belajar Hemat Skuad</h4>
                        <p class="text-xs text-slate-500">Bagi tarif s.d 35% patungan tetangga & teman.</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                    <span class="p-2 bg-amber-100 rounded-lg">🛡️</span>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Proteksi PIN Ortu</h4>
                        <p class="text-xs text-slate-500">Akses eksklusif Rapor Digital & persetujuan escrow.</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                    <span class="p-2 bg-amber-100 rounded-lg">📍</span>
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">Geotagging Rute Presisi</h4>
                        <p class="text-xs text-slate-500">Tutor langsung ke rumah dengan panduan maps.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM UTAMA -->
        <form @submit.prevent="submitForm" class="space-y-8">
            
            <!-- SECTION 1: IDENTITAS DIRI -->
            <div>
                <h3 class="font-bold text-slate-900 text-base mb-1">Isi Identitas Diri & Riwayat Akun</h3>
                <p class="text-xs text-slate-500 mb-4">Pastikan nomor WhatsApp dan email aktif untuk penerbitan kredensial dan komunikasi bimbingan.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Pembelajar / Anak *</label>
                        <input type="text" x-model="form.full_name" required placeholder="Muhammad Rizky Pratama" class="w-full px-4 py-3 rounded-full border border-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Aktif (Login & Bukti Invoice) *</label>
                        <input type="email" x-model="form.email" required placeholder="rizky.pratama@edu.kawalbelajar.id" class="w-full px-4 py-3 rounded-full border border-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Aktif Pembelajar *</label>
                        <input type="tel" x-model="form.phone_number" required placeholder="0812-9845-1120" class="w-full px-4 py-3 rounded-full border border-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) *</label>
                        <input type="password" x-model="form.password" required placeholder="••••••••••••" class="w-full px-4 py-3 rounded-full border border-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400 text-sm">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: JENJANG PENDIDIKAN -->
            <div>
                <h3 class="font-bold text-slate-900 text-base mb-3">Pilih Jenjang Pendidikan Siswa (Bisa Pilih Sesuai Tingkat)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    <!-- SD -->
                    <div @click="form.education_level = 'SD'" :class="form.education_level === 'SD' ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-4 rounded-xl cursor-pointer transition relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xl">📖</span>
                            <span x-show="form.education_level === 'SD'" class="w-5 h-5 bg-teal-600 text-white rounded-full text-xs flex items-center justify-center font-bold">✓</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Sekolah Dasar (SD)</h4>
                        <p class="text-[11px] text-slate-500 mb-3">Kelas 1-6 • Calistung & Tematik</p>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">FOKUS DASAR</span>
                    </div>

                    <!-- SMP -->
                    <div @click="form.education_level = 'SMP'" :class="form.education_level === 'SMP' ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-4 rounded-xl cursor-pointer transition relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xl">📋</span>
                            <span x-show="form.education_level === 'SMP'" class="w-5 h-5 bg-teal-600 text-white rounded-full text-xs flex items-center justify-center font-bold">✓</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Sekolah Menengah Pertama (SMP)</h4>
                        <p class="text-[11px] text-slate-500 mb-3">Kelas 7-9 • MIPA & Bahasa</p>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">KURIKULUM MERDEKA</span>
                    </div>

                    <!-- SMA -->
                    <div @click="form.education_level = 'SMA'" :class="form.education_level === 'SMA' ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-4 rounded-xl cursor-pointer transition relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xl">🎒</span>
                            <span x-show="form.education_level === 'SMA'" class="w-5 h-5 bg-teal-600 text-white rounded-full text-xs flex items-center justify-center font-bold">✓</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Sekolah Menengah Atas (SMA)</h4>
                        <p class="text-[11px] text-slate-500 mb-3">Kelas 10-12 • Saintek / Soshum & UTBK</p>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">KURIKULUM MERDEKA</span>
                    </div>

                    <!-- UMUM -->
                    <div @click="form.education_level = 'UMUM'" :class="form.education_level === 'UMUM' ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-4 rounded-xl cursor-pointer transition relative">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xl">💼</span>
                            <span x-show="form.education_level === 'UMUM'" class="w-5 h-5 bg-teal-600 text-white rounded-full text-xs flex items-center justify-center font-bold">✓</span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">Pendidikan Umum</h4>
                        <p class="text-[11px] text-slate-500 mb-3">Bahasa Asing, Coding, & Karier</p>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">PROFESIONAL</span>
                    </div>

                </div>
            </div>

            <!-- SECTION 3: DATA ORANG TUA (KONDISIONAL: SD/SMP/SMA) -->
            <div x-show="form.education_level !== 'UMUM'" x-transition class="bg-cyan-50/60 border border-cyan-200 rounded-2xl p-5">
                <h3 class="font-bold text-slate-900 text-sm mb-3">Data Orang Tua & Proteksi PIN Pengawasan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Orang Tua / Wali *</label>
                        <input type="text" x-model="form.parent_name" placeholder="Hendra Pratama, S.T." class="w-full px-4 py-2.5 rounded-full border border-slate-900 text-sm bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp Orang Tua (Laporan Nilai) *</label>
                        <input type="tel" x-model="form.parent_phone" placeholder="081398765432" class="w-full px-4 py-2.5 rounded-full border border-slate-900 text-sm bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Setel PIN Keamanan (6 Digit Ortu) *</label>
                        <input type="password" x-model="form.parent_pin" maxlength="6" placeholder="123456" class="w-full px-4 py-2.5 rounded-full border border-slate-900 text-sm bg-white">
                    </div>
                </div>
            </div>

            <!-- SECTION 4: ALAMAT & GEOTAGGING -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap Rumah & Patokan Fisik (Home Visit)</label>
                        <input type="text" x-model="form.saved_address" placeholder="Jl. Brigjen H. Hasan Basry No. 45, Banjarmasin" class="w-full px-4 py-3 rounded-full border border-slate-900 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Titik Koordinat GPS / Maps</label>
                        <div class="relative">
                            <input type="text" x-model="form.gps_display" readonly class="w-full px-4 py-3 rounded-full border border-slate-900 text-sm bg-slate-100 text-slate-600 cursor-not-allowed">
                            <button type="button" @click="getLocation()" class="absolute right-2 top-1.5 px-3 py-1.5 bg-slate-200 hover:bg-slate-300 rounded-full text-xs font-bold text-slate-700">📍 Tandai Titik</button>
                        </div>
                    </div>
                </div>

                <div class="bg-emerald-100/70 border border-emerald-300 rounded-2xl p-5 text-xs text-slate-700 leading-relaxed">
                    <p class="font-semibold text-slate-900 mb-1">Catatan Verifikasi & Keamanan:</p>
                    <p>Sistem akan memetakan tutor terdekat berdasarkan koordinat GPS rumah. Mode patungan tetangga (Squad) otomatis aktif jika terdapat murid di radius 2 km untuk menghemat biaya bimbingan hingga 35%.</p>
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <div class="flex justify-end pt-4">
                <button type="submit" :disabled="loading" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full flex items-center gap-2 shadow-sm transition disabled:opacity-50">
                    <span x-text="loading ? 'Memproses...' : 'Selanjutnya ➔'"></span>
                </button>
            </div>

        </form>
    </main>

    <!-- SCRIPT FETCH KE BACKEND -->
    <script>
        function studentRegisterForm() {
            return {
                loading: false,
                form: {
                    full_name: '',
                    email: '',
                    phone_number: '',
                    password: '',
                    education_level: 'SMP',
                    parent_name: '',
                    parent_phone: '',
                    parent_pin: '',
                    saved_address: '',
                    latitude: -3.319400,
                    longitude: 114.590800,
                    gps_display: 'Terkunci: Lat -3.319400, Long 114.590800 [📍 Tandai Titik]'
                },
                getLocation() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((position) => {
                            this.form.latitude = position.coords.latitude;
                            this.form.longitude = position.coords.longitude;
                            this.form.gps_display = `Lat ${this.form.latitude.toFixed(6)}, Long ${this.form.longitude.toFixed(6)}`;
                        });
                    }
                },
                async submitForm() {
                    this.loading = true;
                    try {
                        const response = await fetch(@json(route('api.student.register')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const result = await response.json();
                        if (response.ok) {
                            alert('Registrasi berhasil!');
                            window.location.href = '/login';
                        } else {
                            alert(result.message || 'Terjadi kesalahan validasi.');
                        }
                    } catch (error) {
                        alert('Gagal terhubung ke server.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
