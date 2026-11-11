<div id="tutor-agenda-modal" class="{{ $showAgendaModal ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true" aria-labelledby="agenda-modal-title">
    <section class="relative max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[30px] border-4 border-[#57a5b4] bg-[#f7fbfc] p-5 shadow-2xl sm:p-7 lg:p-8">
        <a href="{{ route('tutor.dashboard') }}" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white text-2xl text-slate-500 shadow hover:bg-slate-100" aria-label="Tutup agenda">&times;</a>

        @if (session('status'))
            <div role="status" class="mb-5 rounded-xl border border-green-200 bg-green-50 px-5 py-3 pr-12 text-sm font-semibold text-green-800">{{ session('status') }}</div>
        @endif

        <section id="agenda-hari-ini" aria-labelledby="agenda-modal-title">
            <div class="flex flex-wrap items-start justify-between gap-3 pr-10">
                <div>
                    <h2 id="agenda-modal-title" class="text-xl font-extrabold tracking-tight sm:text-2xl">Agenda Sesi Mengajar Hari Ini</h2>
                    <p class="mt-1 text-lg text-slate-500">{{ $agendaDate }}</p>
                </div>
                <span class="rounded-full bg-cyan-100 px-6 py-2 text-sm font-bold text-cyan-700">{{ $todaySessions->count() }} sesi terjadwal</span>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($todaySessions as $session)
                    <article class="grid gap-4 rounded-2xl border-2 {{ $session->is_current ? 'border-green-500' : 'border-[#d9e9ed]' }} bg-white p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                                <span>{{ $session->starts_at?->format('H.i') }} - {{ $session->ends_at?->format('H.i') }} WIB</span>
                                <span class="rounded-full px-4 py-1 text-xs font-bold {{ $session->learning_method === 'online_zoom' ? 'bg-green-500 text-white' : 'bg-[#102d3f] text-white' }}">{{ $session->learning_method_label ?? 'Metode belum ditentukan' }}</span>
                            </div>
                            <h3 class="mt-2 text-lg font-extrabold sm:text-xl">{{ $session->title ?? 'Kelas' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                @if ($session->group_name)
                                    Host: {{ $session->student_name ?? 'Siswa' }} | Kelompok {{ $session->group_size ?? '' }} Siswa
                                @else
                                    Siswa: {{ $session->student_name ?? 'Informasi siswa belum tersedia' }} | Individu
                                @endif
                            </p>
                            <p class="text-sm text-slate-500">Materi: {{ $session->subject ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            @if ($session->is_current)
                                @if (Route::has('tutor.sessions.evaluation') && $session->student_id)
                                    <a href="{{ route('tutor.sessions.evaluation', $session->session_id) }}" class="rounded-full bg-emerald-100 px-5 py-3 text-center text-sm font-bold leading-tight text-emerald-900 hover:bg-emerald-200">Lihat Skor<br>Post-test Siswa</a>
                                @else
                                    <button type="button" disabled title="Fitur skor belum tersedia" class="cursor-not-allowed rounded-full bg-emerald-100 px-5 py-3 text-center text-sm font-bold leading-tight text-emerald-900 opacity-70">Lihat Skor<br>Post-test Siswa</button>
                                @endif
                                <form method="POST" action="{{ route('tutor.sessions.complete', $session->session_id) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600">Selesai</button>
                                </form>
                            @else
                                @if ($session->maps_url)
                                    <a href="{{ $session->maps_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-amber-100 px-6 py-3 text-center text-sm font-bold text-amber-900 hover:bg-amber-200">Buka Maps</a>
                                @endif
                                <span class="rounded-full bg-slate-200 px-6 py-3 text-center text-sm font-bold text-slate-600">Mulai {{ $session->starts_at?->format('H.i') }}</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-[#b8d7de] bg-white/70 px-5 py-8 text-center text-sm text-slate-500">Tidak ada sesi mengajar tersisa hari ini.</div>
                @endforelse
            </div>
        </section>

        <section id="agenda-mendatang" class="mt-7" aria-labelledby="upcoming-title">
            <h2 id="upcoming-title" class="text-xl font-extrabold tracking-tight sm:text-2xl">Agenda Mendatang</h2>
            <div class="mt-4 space-y-3">
                @forelse ($upcomingSessions as $session)
                    <article class="grid gap-4 rounded-2xl border-2 border-[#d9e9ed] bg-white p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-6">
                        <div class="min-w-0">
                            <div class="grid gap-1 text-sm font-medium text-slate-500 sm:grid-cols-2 sm:gap-5">
                                <span>{{ $session->starts_at?->translatedFormat('l, j F') }}</span>
                                <span>{{ $session->starts_at?->format('H.i') }} - {{ $session->ends_at?->format('H.i') }} WIB</span>
                            </div>
                            <h3 class="mt-2 text-lg font-extrabold sm:text-xl">{{ $session->title ?? 'Kelas' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $session->learning_method_label ?? 'Metode belum ditentukan' }}{{ $session->group_name ? ' | Kelompok ' . ($session->group_size ?? '') . ' Siswa' : '' }}</p>
                            <p class="text-sm text-slate-500">Tutor: {{ $tutor->user?->full_name ?? 'Tutor' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                            @if ($session->maps_url)
                                <a href="{{ $session->maps_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-amber-100 px-6 py-3 text-center text-sm font-bold text-amber-900 hover:bg-amber-200">Buka Maps</a>
                            @endif
                            <span class="rounded-full bg-slate-200 px-6 py-3 text-center text-sm font-bold text-slate-600">Mendatang</span>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-[#b8d7de] bg-white/70 px-5 py-7 text-center text-sm text-slate-500">Belum ada sesi mengajar mendatang.</div>
                @endforelse
            </div>
        </section>
    </section>
</div>
