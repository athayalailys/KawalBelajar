<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        @foreach (['Pilih Kebutuhan','Format & Kuota','Slot Jadwal','Review & Simpan'] as $index => $label)<li data-wizard-step="{{ $index + 1 }}" class="rounded-xl px-2 py-4 {{ $index === 0 ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-600' }}">Langkah {{ $index + 1 }}: {{ $label }}</li>@endforeach
    </ol>
    <form id="create-class-form" method="POST" action="{{ route('tutor.classes.store') }}" class="space-y-6 rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-sm md:p-8">
        @csrf
        @if ($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><b>Periksa kembali data kelas:</b><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <input type="hidden" name="package_type" id="package-type" value="{{ old('package_type','monthly') }}"><input type="hidden" name="grade_level" id="grade-level" value="{{ old('grade_level') }}"><input type="hidden" name="learning_method" id="learning-method" value="{{ old('learning_method') }}">
        <section data-step-panel="1" class="space-y-6">
            <div><h1 class="text-2xl font-extrabold sm:text-3xl">Langkah 1: Identitas Kelas &amp; Target Capaian</h1><p class="mt-2 text-sm text-slate-600">Tentukan jenjang, mata pelajaran, dan tujuan program. Materi serta soal dilengkapi setelah draf disimpan.</p></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Pilih jenjang mengajar</legend><p class="mt-1 text-xs text-slate-500">Pilihan mengikuti jenjang yang telah diverifikasi.</p><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">@forelse ($approvedLevels as $level)<button type="button" data-grade-choice="{{ $level }}" aria-pressed="false" class="grade-choice rounded-2xl border border-slate-200 bg-white px-5 py-4 text-left font-bold text-slate-900 transition hover:border-amber-400">{{ $level }}<span class="mt-1 block text-xs font-normal text-slate-500">Jenjang terverifikasi</span></button>@empty<p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Belum ada jenjang terverifikasi.</p>@endforelse</div></fieldset>
            <fieldset><legend class="text-sm font-extrabold">Pilih mata pelajaran <span class="text-rose-600">*</span></legend><p class="mt-1 text-xs text-slate-500">Pilihan menyesuaikan jenjang yang Anda pilih.</p><input type="hidden" name="subject_name" id="subject-name" value="{{ old('subject_name') }}" required><div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">@php($subjectChoices = ['SD' => ['Tematik Terpadu SD', 'Matematika Dasar', 'Calistung', 'IPA SD', 'Bahasa Inggris Anak'], 'SMP' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'], 'SMA' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'], 'UMUM' => ['UI/UX Design', 'Web Coding', 'Bahasa Asing', 'Data Analytics']])@foreach ($subjectChoices as $subjectLevel => $subjects)@foreach ($subjects as $subject)<button type="button" data-subject-choice="{{ $subject }}" data-subject-level="{{ $subjectLevel }}" aria-pressed="false" class="subject-choice hidden rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-left text-sm font-bold hover:border-amber-400">{{ $subject }}</button>@endforeach @endforeach</div></fieldset>
            <label class="block text-sm font-extrabold">Nama program / judul topik <span class="text-rose-600">*</span><input name="package_name" required value="{{ old('package_name') }}" maxlength="255" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-4 py-3 font-normal" placeholder="Contoh: Pendalaman Hukum Newton"></label>
            <label class="block text-sm font-extrabold">Deskripsi &amp; target capaian materi <span class="text-rose-600">*</span><textarea name="target_learning" required rows="4" maxlength="3000" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-4 py-3 font-normal" placeholder="Jelaskan target belajar siswa">{{ old('target_learning') }}</textarea></label>
        </section>
        <section data-step-panel="2" class="hidden space-y-6">
            <div><h2 class="text-2xl font-extrabold">Langkah 2: Format Pengajaran, Kuota &amp; Paket</h2><p class="mt-2 text-sm text-slate-600">Tarif mengikuti tarif resmi yang dikonfigurasi admin. Kelas ini tidak memakai diskon squad.</p></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Format / moda belajar</legend><div class="mt-3 grid gap-3 md:grid-cols-2"><button type="button" data-method-choice="online_zoom" class="method-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b class="text-lg">Online Zoom</b><span class="mt-1 block text-sm text-slate-600">Bimbingan daring interaktif. Kuota maksimal 20 siswa.</span></button><button type="button" data-method-choice="home_visit" class="method-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b class="text-lg">Home Visit</b><span class="mt-1 block text-sm text-slate-600">Bimbingan tatap muka di lokasi yang ditentukan. Kuota maksimal 5 siswa.</span></button></div></fieldset>
            <div class="grid gap-4 rounded-2xl bg-cyan-50 p-5 md:grid-cols-2"><label class="text-sm font-bold">Kapasitas maksimal siswa<select name="target_capacity" required class="mt-2 w-full rounded-xl border-2 border-slate-300 bg-white px-3 py-3 font-normal"><option value="">Pilih kuota</option>@foreach (range(1,20) as $count)<option value="{{ $count }}" @selected(old('target_capacity') == $count)>{{ $count }} siswa</option>@endforeach</select></label><label id="location-field" class="hidden text-sm font-bold">Alamat default Home Visit<input name="default_location" value="{{ old('default_location', $defaultLocation ?? '') }}" maxlength="1000" class="mt-2 w-full rounded-xl border-2 border-slate-300 px-3 py-3 font-normal" placeholder="Masukkan alamat tutor"></label></div>
            <fieldset><legend class="text-sm font-extrabold uppercase">Paket sesi</legend><div class="mt-3 grid gap-3 sm:grid-cols-2"><button type="button" data-package-choice="instant" class="package-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b>1 Sesi Instan</b><span class="mt-1 block text-sm text-slate-600">Satu pertemuan pembelajaran.</span></button><button type="button" data-package-choice="monthly" class="package-choice rounded-2xl border-2 border-slate-300 p-5 text-left"><b>Paket 4 Sesi</b><span class="mt-1 block text-sm text-slate-600">Empat pertemuan dalam satu paket.</span></button></div></fieldset>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><p class="text-xs font-bold uppercase text-emerald-800">Tarif resmi per sesi per siswa</p><p class="mt-1 text-2xl font-extrabold" id="tariff-display">Pilih jenjang dan format untuk melihat tarif</p><p class="mt-1 text-sm font-bold text-emerald-800" id="package-estimate">Estimasi nilai paket per siswa: â€”</p><p class="mt-1 text-xs text-slate-600">Dihitung dari tarif resmi dikali jumlah sesi, tanpa diskon squad. Nilai pencairan akhir tetap mengikuti aturan pembayaran platform yang berlaku.</p></div>
        </section>
        <section data-step-panel="3" class="hidden space-y-5">
            <div><h2 class="text-2xl font-extrabold">Langkah 3: Pilih Slot Master Availability</h2><p class="mt-2 text-sm text-slate-600">Pilih jadwal rutin dari slot yang sudah Anda masukkan di Master Availability Pool. Slot yang bentrok atau nonaktif tidak dapat dipilih.</p></div>
            <div class="grid gap-3 sm:grid-cols-2">@forelse ($availabilitySlots as $slot)<label data-availability-card data-method="{{ $slot->learning_method ?? 'all' }}" class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-slate-200 p-4 hover:border-cyan-500"><input type="checkbox" name="availability_slot_ids[]" value="{{ $slot->slot_id }}" @checked(in_array($slot->slot_id, old('availability_slot_ids', []))) class="h-5 w-5 accent-cyan-700"><span><b>{{ $days[$slot->day_of_week] ?? $slot->day_of_week }}, {{ substr($slot->start_time,0,5) }}â€“{{ substr($slot->end_time,0,5) }}</b><small class="block text-slate-500">Slot Master Availability Â· {{ $slot->learning_method ? str_replace('_',' ', $slot->learning_method) : 'Semua metode' }}</small></span></label>@empty<div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-900 sm:col-span-2">Belum ada slot aktif di Master Availability. Tambahkan slot sebelum membuat kelas. <a class="font-bold underline" href="{{ route('tutor.availability') }}">Buka Master Availability</a></div>@endforelse</div>
            <a href="{{ route('tutor.availability') }}" class="inline-flex rounded-lg border border-[#102d3f] px-4 py-2 text-sm font-bold">Atur Master Availability â†—</a>
        </section>
        <section data-step-panel="4" class="hidden space-y-5">
            <div><h2 class="text-2xl font-extrabold">Langkah 4: Review &amp; Simpan Draf</h2><p class="mt-2 text-sm text-slate-600">Pastikan identitas, metode, tarif dan jadwal sudah benar.</p></div>
            <dl class="divide-y divide-slate-200 rounded-2xl border-2 border-[#102d3f] bg-slate-50 px-5"><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Nama kelas</dt><dd data-review="package_name">â€”</dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Jenjang &amp; mata pelajaran</dt><dd><span data-review="grade_level">â€”</span> Â· <span data-review="subject_name">â€”</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Target capaian</dt><dd data-review="target_learning">â€”</dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Metode &amp; kuota</dt><dd><span data-review="learning_method">â€”</span> Â· <span data-review="target_capacity">â€”</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Paket &amp; tarif</dt><dd><span data-review="package_type">â€”</span> Â· <span id="review-tariff">â€”</span></dd></div><div class="grid gap-1 py-4 sm:grid-cols-[220px_1fr]"><dt class="font-bold">Slot pilihan</dt><dd id="review-slots">Belum dipilih</dd></div></dl>
            <p class="rounded-xl bg-cyan-50 p-4 text-sm">Setelah draf tersimpan, Anda akan diarahkan ke Studio Materi untuk mengisi modul dan soal tiap pertemuan. Kelas belum tampil di katalog sampai materi memenuhi syarat publikasi.</p>
        </section>
        <footer class="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5"><button type="button" id="wizard-back" class="hidden rounded-2xl border-2 border-slate-900 bg-white px-5 py-3 font-bold text-slate-900 hover:bg-slate-50">← Sebelumnya</button><span></span><button type="button" id="wizard-next" class="rounded-2xl border-2 border-slate-900 bg-amber-400 px-6 py-3 font-extrabold text-slate-950 hover:bg-amber-300">Lanjut ke Langkah Berikutnya →</button><button type="submit" id="wizard-submit" class="hidden rounded-2xl border-2 border-slate-900 bg-amber-400 px-6 py-3 font-extrabold text-slate-950">Simpan Draf &amp; Menuju Studio Materi →</button></footer>
    </form>
    </div>
</main>
</div>
<script>
(() => {
    const form = document.getElementById('create-class-form');
    const panels = [...form.querySelectorAll('[data-step-panel]')];
    const indicators = [...document.querySelectorAll('[data-wizard-step]')];
    const grade = document.getElementById('grade-level');
    const method = document.getElementById('learning-method');
    const pkg = document.getElementById('package-type');
    const tariffs = @json($tariffs);
    const days = @json($days);
    let step = 1;
    const pretty = (value) => value?.replaceAll('_',' ').replace(/\b\w/g, letter => letter.toUpperCase()) || 'â€”';
    const price = () => tariffs[`${(grade.value || '').toUpperCase()}|${method.value}`] || null;
    const formatPrice = (value) => value ? new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(value) : 'Tarif belum tersedia';
    const packageTotal = () => price() ? price() * (pkg.value === 'monthly' ? 4 : 1) : null;
    const refreshReview = () => {
        form.querySelectorAll('[data-review]').forEach(node => {
            const key = node.dataset.review;
            const field = form.elements[key];
            let value = field?.value || 'â€”';
            if (key === 'grade_level') value = grade.value || 'â€”';
            if (key === 'learning_method') value = pretty(method.value);
            if (key === 'package_type') value = pkg.value === 'monthly' ? 'Paket 4 sesi' : '1 sesi instan';
            node.textContent = value;
        });
        document.getElementById('review-tariff').textContent = formatPrice(price()) + ' / sesi / siswa';
        document.getElementById('package-estimate').textContent = 'Estimasi nilai paket per siswa: ' + formatPrice(packageTotal());
        const selected = [...form.querySelectorAll('[name="availability_slot_ids[]"]:checked')].map(input => input.closest('label').querySelector('b').textContent);
        document.getElementById('review-slots').textContent = selected.join(' Â· ') || 'Belum dipilih';
    };
    const render = () => {
        panels.forEach(panel => panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step));
        indicators.forEach(item => { const active = Number(item.dataset.wizardStep) <= step; item.classList.toggle('bg-teal-600',active); item.classList.toggle('bg-slate-200',!active); item.classList.toggle('text-white',active); item.classList.toggle('text-slate-600',!active); });
        document.getElementById('wizard-back').classList.toggle('hidden',step === 1);
        document.getElementById('wizard-next').classList.toggle('hidden',step === 4);
        document.getElementById('wizard-submit').classList.toggle('hidden',step !== 4);
        if (step === 4) refreshReview();
        window.scrollTo({top:0,behavior:'smooth'});
    };
    const setChoice = (selector, value, input) => {
        document.querySelectorAll(selector).forEach(button => { const active = button.dataset[input] === value; button.classList.toggle('border-slate-900',active); button.classList.toggle('border-slate-200',!active); button.classList.toggle('bg-amber-50',active); button.classList.toggle('bg-white',!active); button.setAttribute('aria-pressed', String(active)); });
    };
    const subjectInput = document.getElementById('subject-name');
    const subjectButtons = [...document.querySelectorAll('[data-subject-choice]')];
    const showSubjects = (level) => {
        subjectButtons.forEach(button => {
            const visible = button.dataset.subjectLevel === level;
            button.classList.toggle('hidden', !visible);
            if (!visible && button.dataset.subjectChoice === subjectInput.value) subjectInput.value = '';
        });
        setChoice('.subject-choice', subjectInput.value, 'subjectChoice');
    };
    subjectButtons.forEach(button => button.addEventListener('click', () => {
        subjectInput.value = button.dataset.subjectChoice;
        setChoice('.subject-choice', subjectInput.value, 'subjectChoice');
    }));
    document.querySelectorAll('[data-grade-choice]').forEach(button => button.addEventListener('click', () => { grade.value = button.dataset.gradeChoice; setChoice('.grade-choice',grade.value,'gradeChoice'); showSubjects(grade.value); }));
    document.querySelectorAll('[data-method-choice]').forEach(button => button.addEventListener('click', () => { method.value = button.dataset.methodChoice; setChoice('.method-choice',method.value,'methodChoice'); document.getElementById('location-field').classList.toggle('hidden',method.value !== 'home_visit'); document.querySelectorAll('[data-availability-card]').forEach(card => { const allowed = card.dataset.method === 'all' || card.dataset.method === method.value; card.classList.toggle('hidden',!allowed); if (!allowed) card.querySelector('input').checked = false; }); document.getElementById('tariff-display').textContent = formatPrice(price()) + ' / sesi / siswa'; document.getElementById('package-estimate').textContent = 'Estimasi nilai paket per siswa: ' + formatPrice(packageTotal()); }));
    document.querySelectorAll('[data-package-choice]').forEach(button => button.addEventListener('click', () => { pkg.value = button.dataset.packageChoice; setChoice('.package-choice',pkg.value,'packageChoice'); document.getElementById('package-estimate').textContent = 'Estimasi nilai paket per siswa: ' + formatPrice(packageTotal()); }));
    document.getElementById('wizard-next').addEventListener('click', () => {
        if (step === 1 && (!grade.value || !form.elements.subject_name.value.trim() || !form.elements.package_name.value.trim() || !form.elements.target_learning.value.trim())) return alert('Lengkapi identitas kelas dan target capaian.');
        if (step === 2 && (!method.value || !pkg.value || !form.elements.target_capacity.value || !price())) return alert('Pilih format, kuota, paket, serta tarif yang tersedia.');
        if (step === 2 && method.value === 'home_visit' && !form.elements.default_location.value.trim()) return alert('Isi alamat default Home Visit.');
        if (step === 3 && !form.querySelector('[name="availability_slot_ids[]"]:checked')) return alert('Pilih minimal satu slot dari Master Availability.');
        step++; render();
    });
    document.getElementById('wizard-back').addEventListener('click', () => { step--; render(); });
    const oldGrade = @json(old('grade_level')); if (oldGrade) document.querySelector(`[data-grade-choice="${CSS.escape(oldGrade)}"]`)?.click();
    const oldSubject = @json(old('subject_name')); if (oldSubject) subjectButtons.find(button => button.dataset.subjectChoice === oldSubject && button.dataset.subjectLevel === grade.value)?.click();
    const oldMethod = @json(old('learning_method')); if (oldMethod) document.querySelector(`[data-method-choice="${oldMethod}"]`)?.click();
    const oldPackage = @json(old('package_type')); if (oldPackage) document.querySelector(`[data-package-choice="${oldPackage}"]`)?.click();
    else document.querySelector(`[data-package-choice="${pkg.value}"]`)?.click();
    document.getElementById('tariff-display').textContent = formatPrice(price()) + ' / sesi / siswa';
    document.getElementById('package-estimate').textContent = 'Estimasi nilai paket per siswa: ' + formatPrice(packageTotal());
})();
</script>
</body>
</html>
