<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-google-analytics />
    <title>Materi dan Soal - {{ $package->package_name }} | KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    @php
        $sessionQuestions = $questions->get($selectedSession, collect());
        $currentContent = $contents->get($selectedSession);
        $currentMaterial = $materials->get($selectedSession, collect())->first();
        $allReady = $sessionStates->every(fn ($state) => $state['ready']);
        $tutorName = $teacher->user?->full_name ?? 'Tutor';
        $questionLetters = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];
    @endphp
    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="min-w-0 px-5 py-5 sm:px-8 lg:px-10 flex-1">
            <x-tutor.topbar title="Studio Materi dan Kuis" />
            <x-tutor.mobile-nav />
            @if (session('status'))<div role="status" class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
            @if ($errors->any())<div role="alert" class="mb-4 rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

            <section class="rounded-2xl border-2 border-slate-300 bg-white p-5 shadow-sm sm:p-6"><div class="flex flex-wrap items-start justify-between gap-4"><div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full {{ $allReady && $package->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900' }} px-3 py-1 text-[10px] font-extrabold uppercase">{{ $allReady && $package->is_published ? 'Terpublikasi' : 'Belum Terpublikasi' }}</span><span class="font-mono text-[10px] font-bold tracking-wider text-slate-500">ID KELAS: {{ strtoupper(substr(str_replace('-', '', $package->package_id), 0, 12)) }}</span></div><h1 class="mt-3 text-2xl font-extrabold">{{ $package->package_name }}</h1><div class="mt-3 flex flex-wrap gap-2 text-xs font-bold"><span class="rounded-full bg-slate-100 px-3 py-1">Jenjang: {{ $package->grade_level }}</span><span class="rounded-full bg-slate-100 px-3 py-1">Mapel: {{ $package->subject_name }}</span><span class="rounded-full bg-sky-100 px-3 py-1">Metode: {{ $package->learning_method === 'online_zoom' ? 'Online Zoom' : 'Home Visit' }}</span><span class="rounded-full bg-slate-100 px-3 py-1">Paket: {{ $plannedSessions }} Pertemuan</span></div></div><a href="{{ route('tutor.classes') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold hover:bg-slate-50">Daftar Kelas</a></div></section>

            <section class="mt-5"><div class="mb-2 flex flex-wrap items-center justify-between gap-2"><h2 class="font-extrabold">Navigasi Alur Pertemuan</h2><span class="text-xs text-slate-500">Pilih sesi untuk mengedit</span></div><nav class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4" aria-label="Pilih pertemuan">
                @foreach (range(1, $plannedSessions) as $number)
                    @php($state = $sessionStates->get($number))
                    <a href="{{ route('tutor.classes.materials', ['packageId' => $package->package_id, 'session' => $number]) }}" @if ($selectedSession === $number) aria-current="page" @endif class="flex items-center justify-between rounded-xl border-2 px-3 py-3 text-left {{ $selectedSession === $number ? 'border-[#102d3f] bg-cyan-600 text-white' : ($state['ready'] ? 'border-cyan-400 bg-cyan-600 text-white' : 'border-transparent bg-slate-100 text-slate-700') }}"><span><b class="block text-sm">Pertemuan {{ $number }}</b>@unless ($state['ready'])<small class="text-[10px] opacity-80">Belum lengkap</small>@endunless</span><span aria-hidden="true" class="font-bold">{{ $state['ready'] ? 'OK' : '...' }}</span></a>
                @endforeach
            </nav></section>

            <form method="POST" action="{{ route('tutor.classes.materials.session.save', [$package->package_id, $selectedSession]) }}" enctype="multipart/form-data" class="mt-4 rounded-2xl border-l-4 border-cyan-500 bg-white p-5 shadow-sm sm:p-7">@csrf
                <div class="mb-5 flex items-start gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 font-extrabold">{{ $selectedSession }}</span><div><h2 class="text-lg font-extrabold">Pertemuan {{ $selectedSession }}{{ $currentContent?->topic ? ': '.$currentContent->topic : '' }}</h2><p class="text-xs text-slate-500">Isi topik, target belajar, modul, dan bank soal diagnostik pertemuan ini.</p></div></div>

                <section><h3 class="font-bold">1. Silabus &amp; Target Capaian Belajar</h3><div class="mt-3 grid gap-3 md:grid-cols-2"><label class="text-xs font-bold">Topik pembahasan spesifik *<input required name="topic" maxlength="255" value="{{ old('topic', $currentContent?->topic ?? '') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-normal" placeholder="Contoh: Hukum Newton dan gaya gesek"></label><label class="text-xs font-bold">Target capaian belajar siswa *<textarea required name="learning_target" maxlength="2000" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-normal" placeholder="Apa yang siswa mampu lakukan setelah sesi?">{{ old('learning_target', $currentContent?->learning_target ?? '') }}</textarea></label></div></section>

                <section class="mt-5"><h3 class="font-bold">2. Berkas Modul Pembelajaran (PDF / PPT)</h3><div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-100 p-4"><div><p class="text-sm font-bold">{{ $currentMaterial?->title ?? 'Belum ada modul untuk pertemuan ini' }}</p><p class="mt-1 text-xs text-slate-500">File PDF atau PPT, maksimal 20 MB. Unggah berkas baru untuk mengganti modul aktif.</p></div><div class="flex flex-wrap items-center gap-2">@if ($currentMaterial?->file_or_url)<a target="_blank" rel="noopener noreferrer" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentMaterial->file_or_url) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold">Pratinjau Modul</a>@endif<label class="cursor-pointer rounded-lg bg-teal-700 px-3 py-2 text-xs font-bold text-white">Pilih Berkas<input type="file" name="module_file" accept=".pdf,.ppt,.pptx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation" class="sr-only" onchange="this.parentElement.dataset.filename=this.files[0]?.name||''"></label></div></div></section>

                <section class="mt-5"><div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-bold">3. Bank Kuis Diagnostik Pertemuan {{ $selectedSession }}</h3><span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-800">{{ $sessionQuestions->count() }} Soal Tersimpan</span></div><p class="mt-1 text-xs text-slate-600">Minimal 3 soal diperlukan pada setiap pertemuan untuk publikasi kelas. Kunci jawaban dipakai sistem untuk mengoreksi kuis.</p>
                    @forelse ($sessionQuestions as $index => $question)
                        <article class="mt-3 rounded-xl bg-slate-100 p-4"><div class="flex items-start justify-between gap-2"><h4 class="text-sm font-bold">Soal {{ $index + 1 }}</h4><span class="text-[10px] font-bold text-teal-700">Bobot: 10 poin</span></div><p class="mt-2 rounded-lg bg-white p-3 text-sm">{{ $question->question_text }}</p><div class="mt-2 grid gap-2 sm:grid-cols-2">@foreach ($questionLetters as $letter => $label)<div class="rounded-lg px-3 py-2 text-xs {{ strtolower($question->answer_key) === $letter ? 'bg-emerald-100 font-bold text-emerald-800' : 'bg-white text-slate-600' }}"><b>{{ $label }}.</b> {{ $question->{'option_'.$letter} }} @if (strtolower($question->answer_key) === $letter)<span class="float-right">Kunci benar</span>@endif</div>@endforeach</div></article>
                    @empty
                        <p class="mt-3 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">Belum ada soal untuk pertemuan ini. Tambahkan soal dengan empat pilihan dan satu kunci jawaban.</p>
                    @endforelse

                    <div id="new-questions" class="mt-3 space-y-3"></div><button type="button" id="add-question" class="mt-3 rounded-lg border border-teal-700 px-4 py-2 text-xs font-bold text-teal-800 hover:bg-teal-50">+ Tambah Butir Soal</button>
                </section>
                <div class="mt-6 flex flex-wrap justify-end gap-2"><button type="submit" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold hover:bg-slate-50">Simpan Draf Sesi {{ $selectedSession }}</button></div>
            </form>

            <section class="mt-5"><h2 class="mb-3 font-extrabold">Matriks Kelayakan Publikasi</h2><div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">@foreach (range(1, $plannedSessions) as $number) @php($state = $sessionStates->get($number))<article class="rounded-xl border border-slate-200 bg-white p-4"><div class="flex items-center justify-between"><b class="text-sm">Sesi {{ $number }}{{ $state['content']?->topic ? ': '.$state['content']->topic : '' }}</b><span class="text-xs {{ $state['ready'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $state['ready'] ? 'Siap' : 'Belum lengkap' }}</span></div><div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100"><span class="block h-full {{ $state['ready'] ? 'bg-teal-700' : 'bg-amber-400' }}" style="width:{{ $state['ready'] ? 100 : (($state['material'] ? 25 : 0) + ($state['question_count'] >= 3 ? 25 : min(20, $state['question_count'] * 6)) + ($state['content']?->topic ? 25 : 0) + ($state['content']?->learning_target ? 25 : 0)) }}%"></span></div><p class="mt-2 text-[10px] text-slate-500">Modul {{ $state['material'] ? 'siap' : '0%' }} &bull; Kuis {{ $state['question_count'] }}/3 soal</p></article>@endforeach</div></section>

            <section class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border-2 border-[#102d3f] bg-white p-4 shadow-[2px_3px_0_#102d3f]"><div><p class="font-bold">Validasi Publikasi Otomatis</p><p class="mt-1 text-xs text-slate-600">Setiap pertemuan harus memiliki topik, target belajar, modul, dan minimal 3 soal.</p></div><div class="flex flex-wrap gap-2"><a href="{{ route('tutor.classes') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold">Simpan dan Kembali</a><form method="POST" action="{{ route('tutor.classes.materials.publish', $package->package_id) }}">@csrf<button type="submit" @disabled(!$allReady) class="rounded-xl bg-amber-400 px-5 py-3 text-sm font-extrabold text-[#102d3f] hover:bg-amber-300 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">Publikasikan Kelas ke Katalog Siswa</button></form></div></section>
        </main>
    </div>
    <script>
        (() => {
            const list = document.getElementById('new-questions');
            let index = 0;
            document.getElementById('add-question')?.addEventListener('click', () => {
                const card = document.createElement('article');
                card.className = 'rounded-xl border border-slate-200 bg-slate-100 p-4';
                const i = index++;
                card.innerHTML = `<div class="flex items-center justify-between"><b class="text-sm">Soal baru</b><button type="button" class="text-xs font-bold text-rose-700">Hapus</button></div><textarea required name="questions[${i}][question_text]" maxlength="5000" rows="3" placeholder="Tuliskan pertanyaan diagnostik" class="mt-3 w-full rounded-lg border border-slate-200 bg-white p-3 text-sm"></textarea><div class="mt-2 grid gap-2 sm:grid-cols-2">${['a','b','c','d'].map(letter => `<label class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs"><input type="radio" required name="questions[${i}][answer_key]" value="${letter}"><b>${letter.toUpperCase()}.</b><input required maxlength="1000" name="questions[${i}][option_${letter}]" placeholder="Pilihan ${letter.toUpperCase()}" class="min-w-0 flex-1 outline-none"></label>`).join('')}</div>`;
                card.querySelector('button').addEventListener('click', () => card.remove());
                list.appendChild(card);
            });
        })();
    </script>
</body>
</html>
