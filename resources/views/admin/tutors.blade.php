<x-admin-layout title="Tutor & Verifikasi - KawalBelajar" heading="Tutor & Verifikasi">
    <div class="space-y-6" x-data="adminTutorActions()">
        @if (session()->has('approved_credentials'))
            @php($credentials = session('approved_credentials'))
            <section class="rounded-2xl border-2 border-emerald-700 bg-emerald-50 p-5" role="status" data-approved-credentials>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-emerald-950">Tutor berhasil disetujui</h2>
                        <p class="mt-1 text-sm text-emerald-900">
                            {{ $credentials['mail_sent'] ? 'Email kredensial telah diterima server email. Jika belum muncul, periksa folder Spam atau hubungi pengelola email.' : ($credentials['mail_warning'] ?? 'Email belum dikirim. Sampaikan kredensial ini kepada tutor secara aman.') }}
                        </p>
                    </div>
                    <button type="button" @click="copyCredentials()" class="rounded-xl border-2 border-slate-900 bg-white px-4 py-2 text-sm font-bold text-slate-900">
                        Salin kredensial
                    </button>
                </div>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="font-bold text-slate-600">Nama tutor</dt><dd class="font-semibold text-slate-950">{{ $credentials['name'] }}</dd></div>
                    <div><dt class="font-bold text-slate-600">Email login</dt><dd class="font-semibold text-slate-950">{{ $credentials['email'] }}</dd></div>
                    <div><dt class="font-bold text-slate-600">Kata sandi sementara</dt><dd class="select-all font-mono font-bold text-slate-950">{{ $credentials['password'] }}</dd></div>
                    <div><dt class="font-bold text-slate-600">Jenjang disetujui</dt><dd class="font-semibold text-slate-950">{{ implode(', ', $credentials['verified_levels']) }}</dd></div>
                </dl>
                <p class="mt-3 text-xs text-slate-700">Kotak ini hanya tampil satu kali setelah persetujuan. Minta tutor mengganti kata sandi setelah masuk.</p>
            </section>
        @endif
        <section class="rounded-2xl border-2 border-slate-900 bg-white p-5">
            <h1 class="text-xl font-black">Verifikasi berkas tutor</h1>
            <p class="mt-1 text-sm text-slate-600">Periksa dokumen, tetapkan jenjang yang disetujui, lalu setujui atau tolak pengajuan.</p>
            <div class="mt-4 space-y-4">
                @forelse($teachers->where('cv_status', 'pending') as $teacher)
                    <article class="rounded-2xl border-2 border-slate-900 bg-slate-50 p-4" data-teacher="{{ $teacher->teacher_id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="font-extrabold">{{ $teacher->user?->full_name }}</h2><p class="text-sm text-slate-600">{{ $teacher->user?->email }} · {{ $teacher->university }} · {{ $teacher->focus_subject }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold">Menunggu verifikasi</span></div>
                        <div class="mt-3 flex flex-wrap gap-2 text-sm">@foreach(['cv' => 'CV', 'identity' => 'KTP/KTM', 'transcript' => 'Transkrip', 'certificate' => 'Sertifikat'] as $type => $label)@if($teacher->{match($type) {'cv' => 'cv_url', 'identity' => 'identity_document_url', 'transcript' => 'transcript_url', 'certificate' => 'certificate_url'}})<a class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-semibold text-teal-800" href="{{ route('admin.tutors.document', ['teacher' => $teacher->teacher_id, 'type' => $type]) }}">Lihat {{ $label }}</a>@endif @endforeach</div>
                        <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="submit($event)">
                            <input type="hidden" name="action" value="approve">
                            <fieldset><legend class="mb-1 text-xs font-bold">Jenjang disetujui</legend><div class="flex flex-wrap gap-3">@foreach(['SD','SMP','SMA','UMUM'] as $level)<label class="text-sm"><input type="checkbox" name="approved_levels[]" value="{{ $level }}" @checked(in_array($level, $teacher->requested_levels ?? [], true))> {{ $level }}</label>@endforeach</div></fieldset>
                            <button class="rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white">Setujui &amp; kirim kredensial</button>
                            <button type="button" @click="reject('{{ route('admin.tutors.reject', $teacher->teacher_id) }}')" class="rounded-xl border-2 border-slate-900 bg-rose-50 px-4 py-2 font-bold text-rose-800">Tolak</button>
                            <input type="hidden" name="url" value="{{ route('admin.tutors.approve', $teacher->teacher_id) }}">
                        </form>
                    </article>
                @empty <p class="rounded-xl bg-slate-50 p-4 text-slate-600">Belum ada berkas tutor yang menunggu verifikasi.</p> @endforelse
            </div>
        </section>
        <section class="rounded-2xl border-2 border-slate-900 bg-white p-5">
            <h2 class="text-xl font-black">Antrean upgrade jenjang</h2>
            <div class="mt-4 divide-y divide-slate-200">@forelse($levelRequests as $levelRequest)<div class="flex flex-wrap items-center justify-between gap-3 py-4"><div><strong>{{ $levelRequest->teacher?->user?->full_name }}</strong><p class="text-sm text-slate-600">Pengajuan jenjang {{ $levelRequest->requested_level }}</p>@if($levelRequest->supporting_document_path)<a class="text-sm font-bold text-teal-700" href="{{ asset('storage/'.$levelRequest->supporting_document_path) }}">Lihat sertifikat PDF</a>@endif</div><div class="flex gap-2"><button @click="post('{{ route('admin.level-requests.approve', $levelRequest->request_id) }}', {})" class="rounded-xl bg-emerald-600 px-4 py-2 font-bold text-white">Setujui upgrade</button><button @click="reject('{{ route('admin.level-requests.reject', $levelRequest->request_id) }}')" class="rounded-xl border border-slate-300 px-4 py-2 font-bold">Tolak</button></div></div>@empty<p class="py-4 text-slate-600">Belum ada pengajuan upgrade yang menunggu.</p>@endforelse</div>
        </section>
        <section class="rounded-2xl border-2 border-slate-900 bg-white p-5">
            <h2 class="text-xl font-black">Direktori tutor</h2>
            <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead><tr class="border-b-2 border-slate-900"><th class="p-3">Nama</th><th class="p-3">Jenjang</th><th class="p-3">Status</th><th class="p-3">Tindakan</th></tr></thead><tbody>@foreach($teachers->where('cv_status', 'approved') as $teacher)<tr class="border-b border-slate-200"><td class="p-3 font-bold">{{ $teacher->user?->full_name }}</td><td class="p-3">{{ implode(', ', $teacher->approved_levels ?? []) }}</td><td class="p-3">{{ $teacher->user?->is_active ? 'Aktif' : 'Dibekukan' }}</td><td class="p-3">@if($teacher->user?->is_active)<button @click="suspend('{{ route('admin.tutors.suspend', $teacher->teacher_id) }}')" class="rounded-lg bg-rose-100 px-3 py-1.5 font-bold text-rose-800">Bekukan akun</button>@else<button @click="post('{{ route('admin.tutors.reinstate', $teacher->teacher_id) }}', {})" class="rounded-lg bg-emerald-100 px-3 py-1.5 font-bold text-emerald-800">Aktifkan kembali</button>@endif</td></tr>@endforeach</tbody></table></div>
        </section>
    </div>
    @push('scripts')
    <script>
        function adminTutorActions() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            return {
                async copyCredentials() {
                    const credentials = document.querySelector('[data-approved-credentials]');
                    const text = Array.from(credentials.querySelectorAll('dd')).map((item) => item.textContent.trim()).join('\n');
                    try {
                        await navigator.clipboard.writeText(text);
                        alert('Kredensial berhasil disalin. Simpan dengan aman.');
                    } catch (error) {
                        alert('Penyalinan otomatis tidak tersedia. Pilih dan salin kredensial dari kotak ini.');
                    }
                },
                async post(url, body) {
                    const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
                    const json = await response.json();
                    if (!response.ok) { alert(json.message || Object.values(json.errors || {}).flat().join('\n') || 'Permintaan gagal.'); return; }
                    alert(json.message || 'Berhasil.'); location.reload();
                },
                async submit(event) {
                    const form = event.target;
                    const body = Object.fromEntries(new FormData(form));
                    body.approved_levels = new FormData(form).getAll('approved_levels[]');
                    await this.post(form.querySelector('[name="url"]').value, body);
                },
                reject(url) { const reason = prompt('Masukkan alasan penolakan:'); if (reason?.trim()) this.post(url, { reason }); },
                suspend(url) { const reason = prompt('Masukkan alasan pembekuan akun:'); if (reason?.trim()) this.post(url, { reason }); }
            };
        }
    </script>
    @endpush
</x-admin-layout>
