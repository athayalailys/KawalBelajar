<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-8">
        
        <!-- Header Nav -->
        <div class="flex items-center justify-between">
            <h1 class="font-heading text-2xl font-black">Pemesanan Layanan HomeVisit</h1>
            <a href="javascript:history.back()" class="text-xs font-bold bg-brand-light/40 px-4 py-2 rounded-full hover:bg-brand-light transition">
                ← Kembali
            </a>
        </div>

        <!-- Wizard Progress Bar (Step 1 - 5) -->
        <x-booking.step-wizard-bar :currentStep="$step" />

        <!-- Render View per Step -->
        <div class="bg-brand-white/60 backdrop-blur-xs border border-brand-light p-6 md:p-8 rounded-brand-xl shadow-xs">
            @if($step === 1)
                <x-booking.step1-kebutuhan :pakets="$pakets" />
            @elseif($step === 2)
                <div class="space-y-4">
                    <h2 class="font-heading text-2xl font-black">Pilih Tutor Pengajar yang Tersedia</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($tutors as $tutor)
                            <x-booking.step2-tutor-card :tutor="$tutor" :paket="$selectedPaket" />
                        @endforeach
                    </div>
                </div>
            @elseif($step === 3)
                <x-booking.step3-sesi :guru="$selectedGuru" :paket="$selectedPaket" />
            @elseif($step === 4)
                <x-booking.step4-jadwal :guru="$selectedGuru" :schedules="$availableSchedules" />
            @elseif($step === 5)
                <!-- Step 5 Full Page Pembayaran -->
                <x-booking.step5-pembayaran 
                    :guru="$selectedGuru" 
                    :paket="$selectedPaket" 
                />
            @endif
        </div>

    </div>
</x-app-layout>