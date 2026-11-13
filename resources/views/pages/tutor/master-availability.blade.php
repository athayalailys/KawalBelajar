<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Availability - KawalBelajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100/70 font-sans text-slate-800 antialiased">
    @php
        $name = $teacher->user?->full_name ?? 'Tutor';
        $slotsForDay = $slots->where('day_of_week', $selectedDay)->values();
    @endphp
    <div class="flex min-h-screen">
        <x-tutor.sidebar />

        <main class="min-w-0 px-5 sm:px-8 lg:px-10 flex-1">
            <x-tutor.topbar title="Master Availability" />
            <x-tutor.mobile-nav />
            <div class="space-y-6 pb-10">
                @if (session('status'))<div role="status" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
                @if ($errors->any())<div role="alert" class="rounded-xl border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif

                <section class="grid gap-5 rounded-[28px] border-2 border-sky-300 bg-white p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_335px] lg:items-center" aria-labelledby="availability-title">
                    <div><h1 id="availability-title" class="text-2xl font-extrabold tracking-tight sm:text-3xl">Master Availability Pool</h1><p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">Atur slot jam ketersediaan rutin mingguan Anda. Jadwal ini otomatis menjadi sumber ketersediaan untuk seluruh kelas yang Anda buka, bebas bentrok lintas mata pelajaran.</p></div>
                    <div class="grid grid-cols-3 items-center rounded-2xl border-2 border-slate-700 bg-slate-100 p-3 text-center text-xs"><div><span class="block text-slate-600">Total Slot Master</span><b class="mt-1 block text-lg">{{ $activeSlotsCount }}</b><small>Sesi</small></div><div class="border-x border-slate-300 px-1"><span class="block rounded-xl border border-emerald-300 bg-emerald-50 p-2 font-bold text-emerald-800">âœ“ SIAP DIPAKAI<br><b class="text-base">{{ $availableSlotsCount }} Slot</b></span></div><div class="px-1"><span class="block rounded-xl border border-rose-300 bg-rose-50 p-2 font-bold text-rose-800">â–£ TERISI KELAS<br><b class="text-base">{{ $bookedSlotsCount }} Slot</b></span></div></div>
                </section>

                <section class="rounded-[26px] border-2 border-teal-700 bg-[#eff9fa] p-5 sm:p-6" aria-labelledby="add-slot-title">
                    <h2 id="add-slot-title" class="flex items-center gap-2 text-base font-extrabold"><span class="rounded-lg bg-teal-700 px-2 py-1 text-white">ï¼‹</span> Tambah Slot Waktu Ketersediaan Baru</h2>
                    <form method="POST" action="{{ route('tutor.availability.store') }}" class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-[1.15fr_1fr_auto_1fr_auto] xl:items-end">
                        @csrf
                        <label class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Pilih Hari Rutin<select name="day_of_week" required class="mt-1 block h-12 w-full rounded-xl border-2 border-[#102d3f] bg-white px-3 text-sm font-medium normal-case">@foreach ($days as $dayValue => $dayLabel)<option value="{{ $dayValue }}" @selected(old('day_of_week', $selectedDay) === $dayValue)>{{ $dayLabel }}</option>@endforeach</select></label>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Jam Mulai<input type="time" name="start_time" value="{{ old('start_time', '18:00') }}" required class="mt-1 block h-12 w-full rounded-xl border-2 border-[#102d3f] bg-white px-3 text-sm font-medium normal-case"></label>
                        <span class="hidden pb-3 text-2xl xl:block">â†’</span>
                        <label class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Jam Selesai<input type="time" name="end_time" value="{{ old('end_time', '19:00') }}" required readonly class="mt-1 block h-12 w-full rounded-xl border-2 border-[#102d3f] bg-slate-50 px-3 text-sm font-medium normal-case"></label>
                        <button class="h-12 rounded-xl border-2 border-[#102d3f] bg-amber-400 px-4 text-sm font-bold shadow-[2px_3px_0_#102d3f] hover:bg-amber-300">ï¼‹ Simpan Slot ke Master</button>
                    </form>
                    <p class="mt-4 text-xs text-slate-600"><span class="font-bold text-cyan-700">â“˜</span> Durasi slot mengikuti waktu yang Anda tentukan. Slot berlaku rutin mingguan untuk semua mata pelajaran; sistem menolak jadwal yang beririsan.</p>
                </section>

                <section aria-label="Pilih hari ketersediaan">
                    <nav class="grid grid-cols-2 gap-1 rounded-2xl border-2 border-slate-300 bg-white p-1 sm:grid-cols-4 lg:grid-cols-7">
                        @foreach ($days as $dayValue => $dayLabel)
                            @php($dayCount = $slots->where('day_of_week', $dayValue)->where('is_active', true)->count())
                            <a href="{{ route('tutor.availability', ['day' => $dayValue]) }}" @if ($selectedDay === $dayValue) aria-current="page" @endif class="rounded-xl px-3 py-2.5 text-center text-xs font-bold {{ $selectedDay === $dayValue ? 'border-2 border-[#102d3f] bg-teal-700 text-white shadow-[2px_2px_0_#102d3f]' : 'text-slate-600 hover:bg-slate-100' }}">{{ $dayLabel }} <span class="{{ $selectedDay === $dayValue ? 'text-cyan-100' : 'text-slate-400' }}">({{ $dayCount }})</span></a>
                        @endforeach
                    </nav>
                </section>

                <section aria-labelledby="day-slots-title">
                    <h2 id="day-slots-title" class="mb-3 px-2 text-sm font-extrabold">Daftar Slot Rutin: {{ $days[$selectedDay] ?? $selectedDay }} <span class="ml-1 rounded bg-slate-200 px-2 py-1 text-[10px] text-slate-600">WITA (UTC+8)</span></h2>
                    <div class="space-y-3">
                        @forelse ($slotsForDay as $slot)
                            @php($isLocked = $slot->is_booked)
                            <article class="grid gap-4 rounded-2xl border {{ $isLocked ? 'border-slate-400 bg-slate-200/70' : ($slot->is_active ? 'border-[#102d3f] bg-white' : 'border-slate-300 bg-slate-100') }} p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
                                <div class="flex min-w-0 items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 {{ $isLocked ? 'border-slate-400 bg-slate-200' : 'border-[#102d3f] bg-cyan-50' }} text-xl">{{ $isLocked ? 'ðŸ”’' : 'â—·' }}</span><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-extrabold {{ $slot->is_active ? '' : 'text-slate-500 line-through' }}">{{ $slot->day_label }}, {{ $slot->start_label }}â€“{{ $slot->end_label }} WITA</h3>@if ($isLocked)<span class="rounded-full border border-amber-400 bg-amber-100 px-2 py-1 text-[9px] font-extrabold uppercase text-amber-900">Terisi di kelas</span>@elseif ($slot->is_active)<span class="rounded-full border border-emerald-300 bg-emerald-50 px-2 py-1 text-[9px] font-extrabold uppercase text-emerald-800">âœ“ Kosong &amp; siap dipakai</span>@else<span class="rounded-full bg-slate-200 px-2 py-1 text-[9px] font-extrabold uppercase text-slate-600">Nonaktif</span>@endif</div>
                                    @if ($isLocked)<p class="mt-1 text-xs text-amber-800">Terikat dengan program: {{ $slot->usage->package_name ?? 'Pemesanan siswa' }}{{ $slot->usage->subject_name ? ' ('.$slot->usage->subject_name.($slot->usage->grade_level ? ' '.$slot->usage->grade_level : '').')' : '' }}</p>@else<p class="mt-1 text-xs text-slate-500">Slot rutin mingguan â€¢ Sumber jadwal semua program tutor</p>@endif</div></div>
                                <div class="flex items-center justify-end gap-2">
                                    @if ($isLocked)<span class="rounded-lg border border-slate-400 px-3 py-2 text-xs text-slate-500">ðŸ”’ Terkunci (Sedang Aktif)</span>@else
                                        <form method="POST" action="{{ route('tutor.availability.toggle', $slot->slot_id) }}">@csrf @method('PATCH')<button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold hover:bg-slate-50">{{ $slot->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                                        <form method="POST" action="{{ route('tutor.availability.destroy', $slot->slot_id) }}" onsubmit="return confirm('Hapus slot ketersediaan ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">â™œ Hapus</button></form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm text-slate-500">Belum ada slot ketersediaan untuk hari {{ $days[$selectedDay] ?? $selectedDay }}.</div>
                        @endforelse
                    </div>
                </section>

                <aside class="rounded-2xl border border-cyan-600 bg-sky-100/70 p-4 text-xs leading-relaxed text-slate-600 shadow-[1px_2px_0_#102d3f]"><b class="text-[#102d3f]">â“˜ Sinkronisasi Otomatis Terhubung Global</b><p class="mt-1">Slot Master Availability menjadi sumber jadwal untuk katalog dan pemesanan siswa. Slot yang sudah terikat dengan pemesanan aktif dikunci agar tidak terhapus atau berbenturan dengan kelas lain.</p></aside>
            </div>
            <footer class="border-t border-slate-200 py-5 text-[11px] text-slate-500"><b class="text-[#102d3f]">KawalBelajar</b> &nbsp;Â© {{ now()->year }} Yayasan Kawal Pendidikan Nusantara. Semua hak cipta dilindungi.</footer>
        </main>
    </div>
    <script>
        const startTimeInput = document.querySelector('input[name="start_time"]');
        const endTimeInput = document.querySelector('input[name="end_time"]');
        startTimeInput?.addEventListener('input', () => {
            if (!startTimeInput.value) return;
            const [hours, minutes] = startTimeInput.value.split(':').map(Number);
            const end = (hours * 60 + minutes + 60) % (24 * 60);
            endTimeInput.value = `${String(Math.floor(end / 60)).padStart(2, '0')}:${String(end % 60).padStart(2, '0')}`;
        });
    </script>
</body>
</html>
