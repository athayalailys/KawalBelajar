<div x-show="step === 1" x-cloak class="space-y-6">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Temukan bimbingan yang sesuai</h2>
        <p class="text-xs text-slate-500 mt-1">Pilih jenjang, mata pelajaran, dan cara belajar untuk melihat tutor yang tersedia.</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <span class="rounded-full border border-cyan-200 bg-cyan-100 px-4 py-2 text-xs font-bold text-slate-800">Terdaftar sebagai: <span x-text="educationLevel"></span></span>
        <button type="button" @click="showLevelChoices = !showLevelChoices" class="text-xs font-bold text-teal-800 underline">Ganti jenjang</button>
    </div>
    <div x-show="showLevelChoices" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <template x-for="level in levels" :key="level.id">
            <button type="button" @click="changeEducationLevel(level.id); showLevelChoices = false" :class="educationLevel === level.id ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-3 rounded-2xl text-left transition">
                <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="level.tag"></span><span class="block font-bold text-sm text-slate-900 mt-1" x-text="level.id"></span>
            </button>
        </template>
    </div>

    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Pilih mata pelajaran atau modul</h3>
        <p x-show="loading && subjects.length === 0" class="text-sm text-slate-500">Memuat pilihan yang tersedia…</p>
        <p x-show="!loading && subjects.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Belum ada mata pelajaran dengan tutor aktif untuk pilihan ini.</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <template x-for="subjectOption in subjects" :key="subjectOption.name">
                <button type="button" @click="subject = subjectOption.name" :class="subject === subjectOption.name ? 'bg-amber-100 border-2 border-slate-900' : 'bg-white border border-slate-300'" class="p-3 rounded-2xl text-left transition flex items-center gap-3">
                    <span class="p-2 bg-slate-100 rounded-xl text-base" x-text="subjectOption.icon"></span><span><span class="block font-bold text-xs text-slate-900" x-text="subjectOption.name"></span><span class="text-[10px] text-slate-400" x-text="subjectOption.sub"></span></span>
                </button>
            </template>
        </div>
    </div>

    <div>
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Pilih cara belajar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button type="button" @click="changeMethod('home_visit')" :class="method === 'home_visit' ? 'bg-white border-2 border-teal-600 ring-2 ring-teal-600/20' : 'bg-white border border-slate-300'" class="p-5 rounded-2xl text-left transition">
                <span class="font-bold text-sm text-slate-900">🏠 Home Visit</span><span class="block text-xs text-slate-500 mt-1">Tutor datang ke rumah pembelajar.</span>
            </button>
            <button type="button" @click="changeMethod('online_zoom')" :class="method === 'online_zoom' ? 'bg-white border-2 border-teal-600 ring-2 ring-teal-600/20' : 'bg-white border border-slate-300'" class="p-5 rounded-2xl text-left transition">
                <span class="font-bold text-sm text-slate-900">💻 Online Zoom</span><span class="block text-xs text-slate-500 mt-1">Belajar interaktif dari mana saja.</span>
            </button>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-slate-200 gap-4">
        <div class="text-xs font-semibold px-4 py-2 bg-slate-100 border border-slate-900 rounded-full text-slate-800">Pilihan: <span x-text="[educationLevel, subject, method === 'home_visit' ? 'Home Visit' : 'Online Zoom'].filter(Boolean).join(' · ')"></span></div>
        <button type="button" @click="nextStep(2)" :disabled="loading || !subject" class="w-full sm:w-auto bg-amber-400 hover:bg-amber-500 text-slate-900 border-2 border-slate-900 font-bold px-8 py-3 rounded-full flex items-center justify-center gap-2 shadow-sm transition disabled:opacity-50">Lihat Tutor Tersedia ➔</button>
    </div>
</div>
