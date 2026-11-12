<div x-show="step === 2" x-cloak class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pilih tutor pendamping</h2>
            <p class="text-xs text-slate-500 mt-1">Tutor yang tersedia untuk <span x-text="subject"></span>, jenjang <span x-text="educationLevel"></span>.</p>
        </div>
        <span class="text-xs font-bold px-3 py-1.5 bg-teal-50 text-teal-800 rounded-full border border-teal-200">Hasil tutor aktif</span>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <input type="search" x-model="searchQuery" @keydown.enter.prevent="loadTutors()" placeholder="Cari nama tutor atau bidang keahlian" class="flex-1 px-4 py-2.5 rounded-full border border-slate-300 text-sm focus:outline-none focus:border-slate-900">
        <button type="button" @click="loadTutors()" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">Cari ulang</button>
    </div>

    <p x-show="loading" class="text-sm text-slate-500">Sedang mencari tutor...</p>
    <p x-show="initialized && !loading && tutors.length === 0" class="rounded-xl bg-slate-50 p-5 text-sm text-slate-600">Belum ada tutor dengan jadwal dan paket yang sesuai.</p>

    <div class="grid gap-4">
        <template x-for="tutor in tutors" :key="tutor.teacher_id">
            <article class="p-5 rounded-2xl border-2 border-slate-900 bg-white transition hover:shadow-md">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex gap-4">
                        <div class="w-14 h-14 shrink-0 bg-amber-200 rounded-2xl flex items-center justify-center font-bold text-xl border border-slate-900" x-text="(tutor.full_name || 'T').split(' ').map((part) => part[0]).slice(0, 2).join('').toUpperCase()"></div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-base text-slate-900" x-text="tutor.full_name"></h3><span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md">Terverifikasi</span></div>
                            <p class="text-xs text-slate-500 mt-1" x-text="[tutor.teacher_level, tutor.focus_subject].filter(Boolean).join(' · ')"></p>
                            <p class="text-xs text-slate-500 mt-1" x-text="tutor.university ? `Alumni ${tutor.university}` : 'Tutor KawalBelajar'"></p>
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs font-semibold text-slate-600">
                                <span x-show="tutor.rating !== null && tutor.rating !== undefined">⭐ <strong class="text-slate-900" x-text="Number(tutor.rating).toFixed(1)"></strong>/5.0</span>
                                <span x-show="tutor.rating === null || tutor.rating === undefined">Belum ada penilaian</span>
                                <span x-text="`${Number(tutor.total_teaching_hours || 0).toLocaleString('id-ID')} jam mengajar`"></span>
                            </div>
                            <p class="mt-3 inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-800">
                                🟢 Slot terdekat: <span class="ml-1" x-text="tutor.next_available_slot ? new Date(tutor.next_available_slot.starts_at.replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : 'Belum tersedia'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 lg:justify-end">
                        <button type="button" @click="openTutorProfile(tutor)" class="px-4 py-2 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">Lihat profil</button>
                        <button type="button" @click="openCheckoutDrawer(tutor.teacher_id)" class="bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-5 py-2.5 rounded-full text-xs">Pesan & pilih jam →</button>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <template x-for="pack in tutor.packages || []" :key="pack.package_id">
                        <span class="rounded-full bg-cyan-100 px-3 py-1 text-[11px] font-semibold text-slate-700" x-text="`${pack.package_name} · ${rupiah(pack.teacher_rate_per_session)}/sesi`"></span>
                    </template>
                </div>
            </article>
        </template>
    </div>

    <div class="flex items-center justify-between pt-6 border-t border-slate-200">
        <button type="button" @click="step = 1" class="px-6 py-2.5 rounded-full border border-slate-900 font-bold text-xs hover:bg-slate-100">← Kembali</button>
    </div>

    <div x-show="isProfileOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4" @keydown.escape.window="isProfileOpen = false" @click.self="isProfileOpen = false">
        <section class="w-full max-w-lg rounded-3xl border-2 border-slate-900 bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="tutor-profile-title">
            <div class="flex items-start justify-between gap-4"><div><h3 id="tutor-profile-title" class="text-xl font-bold text-slate-900" x-text="profileTutor?.full_name"></h3><p class="mt-1 text-sm text-slate-500" x-text="[profileTutor?.university, profileTutor?.study_program].filter(Boolean).join(' · ')"></p></div><button type="button" @click="isProfileOpen = false" class="rounded-full border border-slate-300 px-3 py-1 text-sm">Tutup</button></div>
            <div class="mt-5 space-y-2 text-sm text-slate-700"><p><strong>Jenjang:</strong> <span x-text="profileTutor?.teacher_level"></span></p><p><strong>Fokus:</strong> <span x-text="profileTutor?.focus_subject || 'Belum dicantumkan'"></span></p><p><strong>Pengalaman tercatat:</strong> <span x-text="`${Number(profileTutor?.total_teaching_hours || 0).toLocaleString('id-ID')} jam`"></span></p></div>
        </section>
    </div>
</div>
