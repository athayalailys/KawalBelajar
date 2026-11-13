<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Kelas - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    @php
        $name = $tutor->user?->full_name ?? 'Tutor';
        $days = ['Monday' => 'Sen', 'Tuesday' => 'Sel', 'Wednesday' => 'Rab', 'Thursday' => 'Kam', 'Friday' => 'Jum', 'Saturday' => 'Sab', 'Sunday' => 'Min'];
        $visibleClasses = $classes->filter(fn ($class) => $classFilter === 'all' || ($classFilter === 'draft' ? $class->class_status === 'draft' : $class->class_status !== 'draft'))->values();
        $methodLabel = fn ($class) => match ($class->learning_method) { 'online_zoom' => 'Online Zoom', 'home_visit' => 'Home Visit', default => 'Moda belum ditentukan' };
        $classSlot = function ($class) use ($masterSlots) {
            return $class->representativeSlot
                ?? $class->availabilitySlots->first(fn ($slot) => in_array($slot->learning_method, [null, $class->learning_method], true))
                ?? $masterSlots->first(fn ($slot) => in_array($slot->learning_method, [null, $class->learning_method], true));
        };
    @endphp
    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="min-w-0 px-5 sm:px-8 lg:px-10 flex-1">
            <x-tutor.topbar title="Manajemen Kelas dan Siswa" />
            <x-tutor.mobile-nav />
            <div class="space-y-6 pb-8">
                @if (session('status'))<div role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
                @if ($errors->any())<div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

                <section class="rounded-[26px] border-2 border-slate-300 bg-white p-6 sm:p-8" aria-labelledby="management-title">
                    <div class="flex flex-wrap items-start justify-between gap-4"><div><h1 id="management-title" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Manajemen Kelas &amp; Siswa</h1><p class="mt-1 text-sm text-slate-600">Kelola materi, penerimaan siswa, rekam capaian diagnostik, dan atur presensi sesi bimbingan.</p></div><a href="{{ route('tutor.classes.create') }}" class="rounded-xl bg-teal-700 px-4 py-3 text-sm font-bold text-white hover:bg-teal-800"> &nbsp;Buat Kelas Baru</a></div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-500">Total Kelas</p><p class="mt-2 text-2xl font-extrabold">{{ $classStats['total'] }} Kelas</p><p class="mt-1 text-xs"><span class="text-emerald-600"> {{ $classStats['active'] }} Aktif</span><span class="px-1 text-slate-300"></span><span class="text-amber-700">{{ $classStats['draft'] }} Draf</span></p></article>
                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-500">Siswa Terdaftar</p><p class="mt-2 text-2xl font-extrabold">{{ $classStats['students'] }} Siswa</p><p class="mt-1 text-xs text-emerald-600">{{ $classStats['students'] > 0 ? ' Data pendaftaran terhubung' : 'Belum ada pendaftaran' }}</p></article>
                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-500">Tingkat Keterisian</p><p class="mt-2 text-2xl font-extrabold">{{ $classStats['occupancy'] !== null ? $classStats['occupancy'].'%' : '' }} <span class="text-xs font-normal text-slate-500">{{ $classStats['capacity'] ? $classStats['enrolled'].'/'.$classStats['capacity'].' Kursi' : 'Kapasitas belum diatur' }}</span></p><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200"><span class="block h-full rounded-full bg-teal-700" style="width:{{ $classStats['occupancy'] ?? 0 }}%"></span></div></article>
                        <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-semibold text-slate-500">Rata-rata Diagnostik</p><p class="mt-2 text-2xl font-extrabold">{{ $classStats['average_pretest'] !== null ? number_format($classStats['average_pretest'], 1) : '' }}<span class="ml-1 text-xs font-medium text-slate-500">/100</span></p><p class="mt-1 text-xs {{ ($classStats['average_change'] ?? 0) >= 0 ? 'text-cyan-700' : 'text-rose-600' }}">{{ $classStats['average_change'] !== null ? (($classStats['average_change'] > 0 ? '+' : '').number_format($classStats['average_change'], 1).'% Post-Test') : 'Belum ada hasil diagnostik' }}</p></article>
                    </div>
                </section>

                <section aria-labelledby="classes-title">
                    <div class="mb-4 flex flex-wrap items-end justify-between gap-4"><div><h2 id="classes-title" class="text-lg font-extrabold">Daftar Pelajaran &amp; Kelas Tutor</h2><p class="text-xs text-slate-600">Pilih kelas untuk meninjau status penerimaan dan roster siswa terkait.</p></div><div class="flex flex-wrap items-center gap-2">
                        <nav class="flex rounded-xl border border-slate-200 bg-slate-100 p-1 text-xs font-semibold" aria-label="Filter kelas">
                            @foreach (['all' => 'Semua ('.$classStats['total'].')', 'active' => 'Aktif ('.$classStats['active'].')', 'draft' => 'Draf ('.$classStats['draft'].')'] as $filter => $label)
                                <a href="{{ route('tutor.classes', ['view' => $viewMode, 'filter' => $filter]) }}" @if ($classFilter === $filter) aria-current="page" @endif class="rounded-lg px-3 py-2 {{ $classFilter === $filter ? 'bg-white text-[#102d3f] shadow-sm' : 'text-slate-500 hover:text-[#102d3f]' }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                        <nav class="flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold" aria-label="Pilih tampilan">
                            <a href="{{ route('tutor.classes', ['view' => 'cards', 'filter' => $classFilter]) }}" @if ($viewMode === 'cards') aria-current="page" @endif class="rounded-lg px-3 py-2 {{ $viewMode === 'cards' ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Kartu</a>
                            <a href="{{ route('tutor.classes', ['view' => 'table', 'filter' => $classFilter]) }}" @if ($viewMode === 'table') aria-current="page" @endif class="rounded-lg px-3 py-2 {{ $viewMode === 'table' ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Tabel</a>
                        </nav>
                        <label class="relative block"><span class="sr-only">Cari kelas</span><input id="class-search" type="search" placeholder="Cari topik atau jenjang..." class="w-56 max-w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-cyan-600"></label>
                    </div></div>

                    @if ($viewMode === 'cards')
                        <div class="grid gap-4 md:grid-cols-2">
                            @forelse ($visibleClasses as $class)
                                @php
                                    $slot = $classSlot($class);
                                    $statusLabel = match ($class->class_status) { 'draft' => 'Draf', 'full' => 'Penuh', 'closed' => 'Katalog Ditutup', default => 'Katalog Aktif' };
                                    $statusClass = match ($class->class_status) { 'draft' => 'bg-amber-100 text-amber-800', 'full' => 'bg-rose-100 text-rose-700', 'closed' => 'bg-slate-100 text-slate-600', default => 'bg-emerald-100 text-emerald-700' };
                                @endphp
                                <article data-class-row data-class-text="{{ mb_strtolower($class->package_name.' '.$class->subject_name.' '.$class->grade_level) }}" class="flex min-h-60 flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex items-center justify-between gap-2"><span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase {{ $statusClass }}">{{ $statusLabel }}</span><span class="font-mono text-[10px] font-bold tracking-wider text-slate-500">#{{ strtoupper(substr(str_replace('-', '', $class->package_id), 0, 8)) }}</span></div>
                                    <h3 class="mt-3 text-base font-extrabold">{{ $class->package_name }}</h3>
                                    <p class="mt-1 text-sm"><b class="text-teal-700">{{ $class->subject_name ?? 'Mata pelajaran' }}</b><span class="px-1 text-slate-300"></span>{{ $class->grade_level ?? 'Jenjang belum diatur' }} ({{ $methodLabel($class) }})</p>
                                    @if ($class->class_status === 'draft')
                                        <p class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-600">Lengkapi topik, target belajar, modul, dan minimal 3 soal pada setiap pertemuan.</p>
                                    @else
                                        <div class="mt-3 rounded-lg bg-slate-100 p-2.5"><div class="flex justify-between gap-2 text-xs font-semibold"><span>Kapasitas</span><span>{{ $class->capacity_count ? $class->enrolled_student_count.' / '.$class->capacity_count.' Siswa' : $class->enrolled_student_count.' Siswa' }} @if ($class->capacity_count) ({{ $class->occupancy_percentage }}%)@endif</span></div><div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-200"><span class="block h-full {{ $class->class_status === 'full' ? 'bg-rose-500' : 'bg-teal-700' }}" style="width:{{ $class->occupancy_percentage }}%"></span></div></div>
                                        <p class="mt-2 flex justify-between border-t border-slate-100 pt-2 text-xs"><span class="text-slate-600"> @if ($slot) {{ $days[$slot->day_of_week] ?? $slot->day_of_week }} {{ substr($slot->start_time, 0, 5) }} WITA @else Atur jadwal di Master Availability @endif</span><span class="font-bold {{ $class->class_status === 'active' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $class->class_status === 'full' ? 'TUTUP (PENUH)' : ($class->class_status === 'active' ? 'BUKA (ON)' : 'TUTUP') }}</span></p>
                                    @endif
                                    <div class="mt-auto grid grid-cols-2 gap-2 pt-3">
                                        @if ($class->class_status === 'draft')
                                            <a href="{{ route('tutor.classes.materials', ['packageId' => $class->package_id, 'session' => 1]) }}" class="col-span-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-center text-xs font-bold text-amber-800 hover:bg-amber-100">Lengkapi Materi dan Soal</a>
                                        @else
                                            <a href="{{ route('tutor.classes.materials', ['packageId' => $class->package_id, 'session' => 1]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold hover:bg-slate-50">Materi &amp; Soal</a>
                                            <a href="{{ route('tutor.classes.roster', $class->package_id) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-center text-xs font-bold hover:bg-slate-200">Buka Roster </a>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="col-span-full rounded-2xl border-2 border-dashed border-slate-300 bg-white px-5 py-12 text-center"><h3 class="font-bold">{{ $classFilter === 'draft' ? 'Tidak ada kelas draf' : 'Belum ada kelas' }}</h3><p class="mt-1 text-sm text-slate-500">Buat kelas baru untuk mulai menerima siswa.</p><a href="{{ route('tutor.classes.create') }}" class="mt-4 inline-flex rounded-lg bg-amber-400 px-4 py-2 text-sm font-bold">Buat Kelas</a></div>
                            @endforelse
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <table class="w-full min-w-[1000px] text-left text-xs">
                                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Mata Pelajaran &amp; Topik</th><th class="px-4 py-4">Jenjang &amp; Moda</th><th class="px-4 py-4">Jadwal Rutin</th><th class="px-4 py-4">Kapasitas Kelas</th><th class="px-4 py-4">Penerimaan Siswa</th><th class="px-4 py-4">Aksi</th></tr></thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($visibleClasses as $class)
                                        @php($slot = $classSlot($class))
                                        <tr data-class-row data-class-text="{{ mb_strtolower($class->package_name.' '.$class->subject_name.' '.$class->grade_level) }}" class="{{ $class->class_status === 'draft' ? 'bg-slate-50/70 text-slate-500' : '' }}">
                                            <td class="px-5 py-4"><div class="flex flex-wrap items-center gap-2"><span class="rounded bg-slate-100 px-2 py-1 font-mono text-[9px] font-bold">#{{ strtoupper(substr(str_replace('-', '', $class->package_id), 0, 8)) }}</span><span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $class->class_status === 'draft' ? 'bg-amber-100 text-amber-800' : ($class->class_status === 'full' ? 'bg-rose-100 text-rose-700' : ($class->class_status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-700')) }}">{{ $class->class_status === 'draft' ? ($class->is_published ? 'Materi Belum Lengkap' : 'Draf Belum Terbit') : ($class->class_status === 'full' ? 'Kapasitas Penuh' : ($class->class_status === 'closed' ? 'Katalog Ditutup' : 'Katalog Aktif')) }}</span></div><b class="mt-2 block text-sm text-[#102d3f]">{{ $class->package_name }}</b><span class="mt-1 block text-[10px] text-slate-500">{{ $class->subject_name ?? 'Mata pelajaran belum diatur' }} | {{ $class->description ?? $class->target_learning ?? 'Topik belum ditentukan' }}</span></td>
                                            <td class="px-4 py-4"><b>{{ $class->grade_level ?? 'Jenjang belum diatur' }}</b><span class="mt-1 block text-slate-500">{{ $methodLabel($class) }}</span></td>
                                            <td class="px-4 py-4">@if ($slot)<b>{{ $days[$slot->day_of_week] ?? $slot->day_of_week }} {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</b><span class="mt-1 block text-slate-500">WITA ({{ \Illuminate\Support\Carbon::parse($slot->start_time)->diffInMinutes(\Illuminate\Support\Carbon::parse($slot->end_time)) }} Menit)</span>@else<span class="text-slate-400">Rencanakan di Master Availability</span>@endif</td>
                                            <td class="px-4 py-4">@if ($class->capacity_count)<div class="flex justify-between gap-3 font-semibold"><span>{{ $class->enrolled_student_count }}/{{ $class->capacity_count }} Kursi</span><span>{{ $class->occupancy_percentage }}%</span></div><div class="mt-2 h-1.5 w-32 overflow-hidden rounded-full bg-slate-200"><span class="block h-full {{ $class->class_status === 'full' ? 'bg-rose-500' : 'bg-cyan-600' }}" style="width:{{ $class->occupancy_percentage }}%"></span></div>@else<span>Kapasitas belum diatur</span>@endif</td>
                                            <td class="px-4 py-4"><span class="rounded-full px-3 py-2 text-[10px] font-bold {{ $class->class_status === 'full' ? 'bg-rose-50 text-rose-700' : ($class->class_status === 'draft' || $class->class_status === 'closed' ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-700') }}">{{ $class->class_status === 'full' ? 'Penerimaan Tutup (Penuh)' : ($class->class_status === 'draft' ? 'Draf Internal' : ($class->class_status === 'closed' ? 'Penerimaan Ditutup' : 'Penerimaan Buka')) }}</span></td>
                                            <td class="px-4 py-4"><div class="flex items-center gap-2">@if ($class->class_status === 'draft')<a href="{{ route('tutor.classes.materials', ['packageId' => $class->package_id, 'session' => 1]) }}" class="rounded-lg border border-amber-300 px-3 py-2 font-semibold text-amber-800">Lengkapi Materi dan Soal</a>@else
                                                <a href="{{ route('tutor.classes.materials', ['packageId' => $class->package_id, 'session' => 1]) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold">Materi &amp; Soal</a><a href="{{ route('tutor.classes.roster', $class->package_id) }}" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold">Kelola Roster</a>@endif</div></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">Belum ada kelas pada filter ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
                <aside class="rounded-2xl border border-cyan-600 bg-cyan-50 px-4 py-3 text-xs leading-relaxed text-slate-600"><b class="text-[#102d3f]"> Prosedur pengelolaan kelas</b><p class="mt-1">Status kapasitas dan penerimaan mengikuti pendaftaran berbayar. Ketersediaan waktu kelas menggunakan Master Availability Pool; slot yang dipesan akan terkunci lintas mata pelajaran.</p></aside>
            </div>
            <footer class="border-t border-slate-200 py-5 text-[11px] text-slate-500"><b class="text-[#102d3f]">KawalBelajar</b> &nbsp; {{ now()->year }} Yayasan Kawal Pendidikan Nusantara. Semua hak cipta dilindungi.</footer>
        </main>
    </div>
    @include('pages.tutor.create-class-modal')
    <script>
        document.getElementById('class-search')?.addEventListener('input', (event) => {
            const query = event.target.value.trim().toLocaleLowerCase('id');
            document.querySelectorAll('[data-class-row]').forEach((row) => {
                row.classList.toggle('hidden', !row.dataset.classText.includes(query));
            });
        });
    </script>
</body>
</html>

