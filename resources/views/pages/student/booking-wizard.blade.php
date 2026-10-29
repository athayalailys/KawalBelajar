<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Bimbingan - KawalBelajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased">

    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-amber-500 text-xl">
                <span>🎓 KawalBelajar</span>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Laman Pemesanan Bimbingan Resmi
            </div>
        </div>
    </header>

    <!-- KONTEN UTAMA WIZARD -->
    <main class="max-w-5xl mx-auto px-4 py-8" x-data="bookingWizard()" x-init="initData()"
        data-booking-api-routes
        data-catalog-url="{{ route('api.student.catalog') }}"
        data-calculate-url="{{ route('api.student.booking.calculate') }}"
        data-slots-url="{{ route('api.student.booking.tutor-slots', ['teacherId' => '__TEACHER_ID__']) }}"
        data-checkout-url="{{ route('api.student.booking.checkout') }}">
        <div class="bg-white border-2 border-slate-900 rounded-3xl p-6 md:p-8 shadow-sm">
            <x-booking.step-wizard-bar />
            <x-booking.step1-kebutuhan />
            <x-booking.step2-tutor-card />
            <x-booking.step3-sesi />
            <x-booking.step4-jadwal />
            <x-booking.step5-pembayaran />
        </div>
    </main>

    <!-- LOGIC ALPINE.JS -->
    <script>
        function bookingWizard() {
            return {
                currentStep: 1,
                steps: ['Pilih Kebutuhan', 'Pilih Tutor', 'Pilih Sesi', 'Pilih Jadwal', 'Bayar'],
                
                levels: [
                    { id: 'SD', tag: 'Tingkat Dasar', title: 'SD Kelas 1-6', desc: 'Tematik & Calistung' },
                    { id: 'SMP', tag: 'Merdeka', title: 'SMP Kelas 7-9', desc: 'Fokus Mapel Inti' },
                    { id: 'SMA', tag: 'Tingkat Lanjut', title: 'SMA Kelas 10-12', desc: 'IPA, IPS & SNBT' },
                    { id: 'UMUM', tag: 'Profesional', title: 'Skill & Bahasa', desc: 'IELTS, Coding, dsb.' }
                ],
                subjects: [
                    { name: 'Fisika', sub: 'Teori & Praktik', icon: '⚡' },
                    { name: 'Matematika', sub: 'Aljabar & Bangun', icon: '📐' },
                    { name: 'IPA Terpadu', sub: 'Sains Terintegrasi', icon: '🔬' },
                    { name: 'Bahasa Inggris', sub: 'Grammar & Talk', icon: '🌐' },
                    { name: 'Biologi', sub: 'Sains Hayati', icon: '🧬' }
                ],

                form: {
                    education_level: 'SMP',
                    subject_name: 'Fisika',
                    learning_method: 'home_visit',
                    session_package: 4,
                    format: 'host',
                    squad_code: '',
                    target_capacity: 3
                },

                tutors: [],
                selectedTutor: null,
                searchQuery: '',
                slots: [],
                selectedSlot: null,
                isSubmitting: false,

                initData() {
                    this.fetchTutors();
                },

                goToStep(step) {
                    this.currentStep = step;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async fetchTutors() {
                    try {
                        const apiRoutes = document.querySelector('[data-booking-api-routes]').dataset;
                        const catalogUrl = new URL(apiRoutes.catalogUrl, window.location.origin);
                        catalogUrl.search = new URLSearchParams({
                            education_level: this.form.education_level,
                            learning_method: this.form.learning_method,
                            search: this.searchQuery
                        });
                        const res = await fetch(catalogUrl);
                        const json = await res.json();
                        
                        // Jika API sukses & ada data dari database
                        if (json.success && json.data && json.data.length > 0) {
                            this.tutors = json.data;
                        } else {
                            // Fallback otomatis jika API kosong/error
                            this.tutors = this.getDummyTutors();
                        }
                    } catch (e) {
                        console.error("Gagal memuat API, menggunakan data dummy fallback:", e);
                        this.tutors = this.getDummyTutors();
                    }

                    if (this.tutors.length > 0) {
                        this.selectedTutor = this.tutors[0];
                    }
                },

                getDummyTutors() {
                    return [
                        {
                            teacher_id: "01a132f3-8639-711a-b749-e579c6d1c31a",
                            full_name: "M. Haikal Fauzan, S.Pd",
                            rating: 4.9,
                            reviews_count: 120,
                            starting_price: 56250
                        },
                        {
                            teacher_id: "01a132f3-8639-711a-b749-e579c6d1c31b",
                            full_name: "Sarah Amanda, M.Si",
                            rating: 4.8,
                            reviews_count: 85,
                            starting_price: 60000
                        },
                        {
                            teacher_id: "01a132f3-8639-711a-b749-e579c6d1c31c",
                            full_name: "Dimas Arya, S.Si",
                            rating: 4.8,
                            reviews_count: 95,
                            starting_price: 55000
                        }
                    ];
                },

                async calculatePricing() {
                    try {
                        const apiRoutes = document.querySelector('[data-booking-api-routes]').dataset;
                        const res = await fetch(apiRoutes.calculateUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        const json = await res.json();
                        if (json.success) {
                            console.log("Kalkulasi Harga Berhasil:", json.data);
                        }
                    } catch (e) {
                        console.error("Gagal menghitung harga", e);
                    }
                },

                async fetchSlots() {
                    if (!this.selectedTutor) return;
                    try {
                        const apiRoutes = document.querySelector('[data-booking-api-routes]').dataset;
                        const slotsUrl = apiRoutes.slotsUrl;
                        const res = await fetch(slotsUrl.replace('__TEACHER_ID__', encodeURIComponent(this.selectedTutor.teacher_id)));
                        const json = await res.json();
                        
                        // Jika API sukses & ada data slot dari database
                        if (json.success && json.data && json.data.length > 0) {
                            this.slots = json.data;
                        } else {
                            // Fallback ke data dummy jika database kosong
                            this.slots = this.getDummySlots();
                        }
                    } catch (e) {
                        console.error("Gagal memuat slot jadwal, menggunakan data dummy fallback:", e);
                        this.slots = this.getDummySlots();
                    }
                },

                getDummySlots() {
                    return [
                        { slot_id: 'slot-1', day_of_week: 'Senin', start_time: '14:00', end_time: '15:30', is_available: false },
                        { slot_id: 'slot-2', day_of_week: 'Senin', start_time: '18:00', end_time: '19:30', is_available: true },
                        { slot_id: 'slot-3', day_of_week: 'Rabu', start_time: '14:00', end_time: '15:30', is_available: true },
                        { slot_id: 'slot-4', day_of_week: 'Rabu', start_time: '18:00', end_time: '19:30', is_available: false },
                        { slot_id: 'slot-5', day_of_week: 'Jumat', start_time: '14:00', end_time: '15:30', is_available: false },
                        { slot_id: 'slot-6', day_of_week: 'Jumat', start_time: '16:00', end_time: '17:30', is_available: false },
                        { slot_id: 'slot-7', day_of_week: 'Jumat', start_time: '18:00', end_time: '19:30', is_available: true },
                        { slot_id: 'slot-8', day_of_week: 'Jumat', start_time: '19:45', end_time: '21:15', is_available: true },
                        { slot_id: 'slot-9', day_of_week: 'Sabtu', start_time: '14:00', end_time: '15:30', is_available: true },
                        { slot_id: 'slot-10', day_of_week: 'Sabtu', start_time: '18:00', end_time: '19:30', is_available: true }
                    ];
                },
                

                async submitCheckout() {
                    this.isSubmitting = true;
                    const payload = {
                        ...this.form,
                        teacher_id: this.selectedTutor ? this.selectedTutor.teacher_id : null,
                        selected_slot_id: this.selectedSlot ? this.selectedSlot.slot_id : null
                    };

                    try {
                        const apiRoutes = document.querySelector('[data-booking-api-routes]').dataset;
                        const res = await fetch(apiRoutes.checkoutUrl, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json();
                        if (res.ok && json.success) {
                            alert('Pesanan berhasil dibuat! Anda akan diarahkan ke halaman pembayaran Xendit.');
                            window.location.href = json.data.xendit_payment_url;
                        } else {
                            alert(json.message || 'Gagal memproses transaksi.');
                        }
                    } catch (e) {
                        alert('Terjadi kesalahan koneksi ke server.');
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
