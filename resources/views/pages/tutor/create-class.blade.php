<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-google-analytics />
    <title>Buat Kelas Baru | KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
<div class="flex min-h-screen">
    <x-tutor.sidebar />
    <main class="min-w-0 flex-1">
    <x-tutor.topbar title="Buat Kelas Baru" />
    <x-tutor.mobile-nav />
    <div class="mx-auto max-w-[1440px] px-4 pb-8 sm:px-8 lg:px-10">
    <a href="{{ route('tutor.classes') }}" class="mb-5 inline-flex rounded-xl border-2 border-slate-900 bg-white px-4 py-2 text-sm font-bold text-slate-900 hover:bg-amber-50">← Kembali ke Manajemen Kelas</a>
    <ol class="mb-8 grid grid-cols-2 gap-2 overflow-hidden rounded-2xl border-2 border-slate-900 bg-slate-200 text-center text-xs font-extrabold md:grid-cols-4 sm:text-sm" aria-label="Tahapan buat kelas">
        @foreach (['Kesediaan Jadwal','Detail & Moda Kelas','Slot Jadwal','Review & Simpan'] as $index => $label)<li data-wizard-step="{{ $index + 1 }}" class="rounded-xl px-2 py-4 {{ $index === 0 ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-600' }}">Langkah {{ $index + 1 }}: {{ $label }}</li>@endforeach
    </ol>
    <form id="create-class-form" method="POST" action="{{ route('tutor.classes.store') }}" data-draft-save-url="{{ route('tutor.classes.draft.store') }}" data-draft-delete-url="{{ route('tutor.classes.draft.delete') }}" data-draft-key="kb_tutor_class_draft_{{ $tutor->teacher_id }}" data-clear-key="kb_tutor_class_draft_clear_{{ $tutor->teacher_id }}" class="space-y-6 rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-sm md:p-8">
        @csrf
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
            <span id="draft-status" role="status" aria-live="polite" class="text-xs font-semibold text-slate-600">Draft tersimpan otomatis saat Anda mulai mengisi.</span>
            <button type="button" id="reset-class-draft" class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-50">Reset / Hapus Draft</button>
        </div>
        @if ($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><b>Periksa kembali data kelas:</b><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <input type="hidden" name="package_type" id="package-type" value="{{ old('package_type','monthly') }}"><input type="hidden" name="grade_level" id="grade-level" value="{{ old('grade_level') }}"><input type="hidden" name="learning_method" id="learning-method" value="{{ old('learning_method') }}">
        <section data-step-panel="1" class="space-y-6">
            <div><h1 class="text-2xl font-extrabold sm:text-3xl">Langkah 1: Kesediaan Jadwal</h1><p class="mt-2 text-sm text-slate-600">Pastikan slot rutin yang ingin ditawarkan sudah tersedia. Slot untuk kelas dipilih pada Langkah 3.</p></div>
            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-extrabold">Jadwal Master Availability aktif</h2><p class="mt-1 text-sm text-slate-600">{{ $availabilitySlots->count() }} slot tersedia untuk dipilih.</p></div><a href="{{ route('tutor.availability', ['return_to' => 'create_class', 'step' => 1]) }}" class="rounded-xl border-2 border-slate-900 bg-amber-300 px-4 py-2.5 text-sm font-bold">Tambah / Atur Jam</a></div>
                <ul data-availability-summary class="mt-4 grid gap-2 sm:grid-cols-2">@forelse ($availabilitySlots as $slot)<li data-slot-summary class="rounded-xl border border-cyan-100 bg-white p-3 text-sm"><b>{{ $days[$slot->day_of_week] ?? $slot->day_of_week }}, {{ substr($slot->start_time,0,5) }}–{{ substr($slot->end_time,0,5) }}</b><span class="mt-1 block text-xs text-slate-500">{{ $slot->learning_method ? str_replace('_',' ', $slot->learning_method) : 'Online dan Home Visit' }}</span></li>@empty<li class="rounded-xl border border-amber-200 bg-white p-4 text-sm text-amber-900 sm:col-span-2">Belum ada slot aktif. Tambahkan jam ketersediaan sebelum melanjutkan.</li>@endforelse</ul>
            </div>
        </section>
        <section data-step-panel="2" class="hidden space-y-6">
            <div><h1 class="text-2xl font-extrabold sm:text-3xl">Langkah 2: Identitas Kelas &amp; Target Capaian</h1><p class="mt-2 text-sm text-slate-600">Tentukan jenjang, mata pelajaran, tujuan program, moda, dan detail lokasi atau tautan kelas.</p></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Pilih jenjang mengajar</legend><p class="mt-1 text-xs text-slate-500">Pilihan mengikuti jenjang yang telah diverifikasi.</p><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">@forelse ($approvedLevels as $level)<button type="button" data-grade-choice="{{ $level }}" aria-pressed="false" class="grade-choice rounded-2xl border border-slate-200 bg-white px-5 py-4 text-left font-bold text-slate-900 transition hover:border-amber-400">{{ $level }}<span class="mt-1 block text-xs font-normal text-slate-500">Jenjang terverifikasi</span></button>@empty<p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Belum ada jenjang terverifikasi.</p>@endforelse</div></fieldset>
            <fieldset><legend class="text-sm font-extrabold">Pilih mata pelajaran <span class="text-rose-600">*</span></legend><p class="mt-1 text-xs text-slate-500">Pilihan mengikuti daftar mata pelajaran aktif dari Admin.</p><input type="hidden" name="subject_name" id="subject-name" value="{{ old('subject_name') }}" required><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">@foreach ($subjectsByLevel as $subjectLevel => $subjects)@foreach ($subjects as $subject)@if($subject['active'])<button type="button" data-subject-choice="{{ $subject['name'] }}" data-subject-level="{{ $subjectLevel }}" aria-pressed="false" class="subject-choice hidden rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold hover:border-amber-400">{{ $subject['name'] }}</button>@endif @endforeach @endforeach</div></fieldset>
            <label class="block text-sm font-extrabold">Nama program / judul topik <span class="text-rose-600">*</span><input name="package_name" required value="{{ old('package_name') }}" maxlength="255" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-4 py-3 font-normal" placeholder="Contoh: Pendalaman Hukum Newton"></label>
            <label class="block text-sm font-extrabold">Deskripsi &amp; target capaian materi <span class="text-rose-600">*</span><textarea name="target_learning" required rows="4" maxlength="3000" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-4 py-3 font-normal" placeholder="Jelaskan target belajar siswa">{{ old('target_learning') }}</textarea></label>
        </section>
        <section data-step-panel="2" class="hidden space-y-6">
            <div><h2 class="text-xl font-extrabold">Moda Belajar, Kuota &amp; Paket</h2><p class="mt-2 text-sm text-slate-600">Tarif mengikuti tarif resmi yang dikonfigurasi admin. Kelas ini tidak memakai diskon squad.</p></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Format / moda belajar</legend><div class="mt-3 grid gap-3 md:grid-cols-2"><button type="button" data-method-choice="online" class="method-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b class="text-lg">Online (Zoom)</b><span class="mt-1 block text-sm text-slate-600">Bimbingan daring interaktif. Kuota maksimal 20 siswa.</span></button><button type="button" data-method-choice="home_visit" class="method-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b class="text-lg">Home Visit</b><span class="mt-1 block text-sm text-slate-600">Bimbingan tatap muka di area yang ditentukan. Kuota maksimal 5 siswa.</span></button></div></fieldset>
            <div class="grid gap-4 rounded-2xl bg-cyan-50 p-5 md:grid-cols-2"><label class="text-sm font-bold">Kapasitas maksimal siswa<select id="capacity-select" name="target_capacity" required class="mt-2 w-full rounded-xl border-2 border-slate-300 bg-white px-3 py-3 font-normal"><option value="">Pilih kuota</option>@foreach (range(1,20) as $count)<option value="{{ $count }}" data-capacity-option="{{ $count }}" @selected(old('target_capacity') == $count)>{{ $count }} siswa</option>@endforeach</select></label><label id="location-field" class="hidden text-sm font-bold">Cakupan area / catatan lokasi Home Visit<input name="default_location" value="{{ old('default_location', $defaultLocation ?? '') }}" maxlength="1000" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-3 py-3 font-normal" placeholder="Contoh: area Panakkukang, Makassar"></label><label id="zoom-url-field" class="hidden text-sm font-bold md:col-span-2">Tautan meeting Zoom (opsional)<input type="url" name="zoom_meeting_url" value="{{ old('zoom_meeting_url') }}" maxlength="255" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-3 py-3 font-normal" placeholder="https://zoom.us/j/..."></label></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Paket sesi</legend><div class="mt-3 grid gap-3 sm:grid-cols-2"><button type="button" data-package-choice="instant" class="package-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b>1 Sesi Instan</b><span class="mt-1 block text-sm text-slate-600">Satu pertemuan pembelajaran.</span></button><button type="button" data-package-choice="monthly" class="package-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b>Paket 4 Sesi</b><span class="mt-1 block text-sm text-slate-600">Empat pertemuan dalam satu paket.</span></button></div></fieldset>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><p class="text-xs font-bold uppercase text-emerald-800">Tarif resmi per sesi per siswa</p><p class="mt-1 text-2xl font-extrabold" id="tariff-display">Pilih jenjang dan format untuk melihat tarif</p><p class="mt-1 text-sm font-bold text-emerald-800" id="package-estimate">Estimasi nilai paket per siswa: —</p><p class="mt-1 text-xs text-slate-600">Dihitung dari tarif resmi dikali jumlah sesi, tanpa diskon squad. Nilai pencairan akhir tetap mengikuti aturan pembayaran platform yang berlaku.</p></div>
        </section>
        <section data-step-panel="3" class="hidden space-y-5">
            <div><h2 class="text-2xl font-extrabold">Langkah 3: Pilih Slot Master Availability</h2><p class="mt-2 text-sm text-slate-600">Pilih jadwal rutin dari slot yang sudah Anda masukkan di Master Availability Pool. Slot yang bentrok atau nonaktif tidak dapat dipilih.</p></div>
            <div class="grid gap-3 sm:grid-cols-2">@forelse ($availabilitySlots as $slot)<label data-availability-card data-method="{{ $slot->learning_method ?? 'all' }}" class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-slate-200 p-4 hover:border-cyan-500"><input type="checkbox" name="availability_slot_ids[]" value="{{ $slot->slot_id }}" @checked(in_array($slot->slot_id, old('availability_slot_ids', []))) class="h-5 w-5 accent-cyan-700"><span><b>{{ $days[$slot->day_of_week] ?? $slot->day_of_week }}, {{ substr($slot->start_time,0,5) }}–{{ substr($slot->end_time,0,5) }}</b><small class="block text-slate-500">Slot Master Availability · {{ $slot->learning_method ? str_replace('_',' ', $slot->learning_method) : 'Semua metode' }}</small></span></label>@empty<div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-900 sm:col-span-2">Belum ada slot aktif di Master Availability. Tambahkan slot sebelum membuat kelas. <a class="font-bold underline" href="{{ route('tutor.availability', ['return_to' => 'create_class', 'step' => 3]) }}">Buka Master Availability</a></div>@endforelse</div>
            <a href="{{ route('tutor.availability', ['return_to' => 'create_class', 'step' => 3]) }}" class="inline-flex rounded-lg border border-[#102d3f] px-4 py-2 text-sm font-bold">Tambah / Atur Jam di Master Availability</a>
        </section>
        <section data-step-panel="4" class="hidden space-y-5">
            <div><h2 class="text-2xl font-extrabold">Langkah 4: Review &amp; Simpan Draf</h2><p class="mt-2 text-sm text-slate-600">Pastikan identitas, metode, tarif dan jadwal sudah benar.</p></div>
            <dl class="divide-y divide-slate-200 rounded-2xl border-2 border-[#102d3f] bg-slate-50 px-5"><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Nama kelas</dt><dd data-review="package_name">—</dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Jenjang &amp; mata pelajaran</dt><dd><span data-review="grade_level">—</span> · <span data-review="subject_name">—</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Target capaian</dt><dd data-review="target_learning">—</dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Metode &amp; kuota</dt><dd><span data-review="learning_method">—</span> · <span data-review="target_capacity">—</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Paket &amp; tarif</dt><dd><span data-review="package_type">—</span> · <span id="review-tariff">—</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Slot pilihan</dt><dd id="review-slots">Belum dipilih</dd></div></dl>
            <p class="rounded-xl bg-cyan-50 p-4 text-sm">Setelah draf tersimpan, Anda akan diarahkan ke Studio Materi untuk mengisi modul dan soal tiap pertemuan. Kelas belum tampil di katalog sampai materi memenuhi syarat publikasi.</p>
        </section>
        <footer class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5"><button type="button" id="wizard-back" class="hidden rounded-2xl border-2 border-slate-900 bg-white px-5 py-3 font-bold text-slate-900 hover:bg-slate-50">← Sebelumnya</button><span></span><button type="button" id="wizard-next" class="rounded-2xl border-2 border-slate-900 bg-amber-400 px-6 py-3 font-extrabold text-slate-950 hover:bg-amber-300">Lanjut ke Langkah Berikutnya →</button><button type="submit" id="wizard-submit" class="hidden rounded-2xl border-2 border-slate-900 bg-amber-400 px-6 py-3 font-extrabold text-slate-950">Simpan Draf &amp; Menuju Studio Materi →</button></footer>
    </form>
    </div>
</main>
</div>
<script>
window.kbTutorClassDraftConfig = {
    serverDraft: @json($serverDraft),
    initialStep: {{ $initialStep }},
    stepOverride: @json(request()->filled('step')),
    oldValues: @json(old()),
    tariffs: @json($tariffs),
};
</script>
</body>
</html>
