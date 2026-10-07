<x-app-layout>
    <div data-booking-preferences
        data-next-url="{{ $nextStepUrl }}"
        data-selected-level="{{ $selectedLevelId }}"
        data-selected-grade="{{ $selectedGradeId }}"
        data-selected-subject="{{ $selectedSubjectId }}"
        data-selected-method="{{ $selectedMethodId }}"
        class="mx-auto -mt-5 max-w-5xl overflow-hidden rounded-[28px] border-2 border-[#102B38] bg-white text-[#102B38] shadow-[5px_5px_0_#102B38]">
        <header class="flex items-center justify-between gap-4 border-b-2 border-[#102B38] px-5 py-4 sm:px-7">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="KawalBelajar">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border-2 border-[#102B38] bg-[#159EAD] text-xl text-white" aria-hidden="true">◇</span>
                <span>
                    <span class="flex flex-wrap items-center gap-2 font-heading text-base font-bold sm:text-lg">KawalBelajar <span class="rounded-full border border-[#102B38] bg-[#FFF8C5] px-2 py-0.5 font-sans text-[10px] font-bold">Katalog {{ $catalogVersion }}</span></span>
                    <span class="hidden text-xs text-slate-500 sm:block">Eksplorasi Kelas &amp; Pemesanan Terpadu</span>
                </span>
            </a>
            <span class="shrink-0 rounded-full border-2 border-[#102B38] bg-[#FFB52E] px-3 py-2 text-[10px] font-extrabold uppercase sm:px-4 sm:text-xs">Langkah 1 dari 5</span>
        </header>

        <nav aria-label="Progress pemesanan" class="grid grid-cols-2 gap-2 border-b-2 border-[#102B38] bg-slate-50 px-4 py-4 sm:grid-cols-5 sm:px-6">
            @foreach($steps as $index => $stepLabel)
                <div aria-current="{{ $index === 0 ? 'step' : 'false' }}" class="flex items-center justify-center gap-2 rounded-xl border px-2 py-2.5 text-center text-[11px] font-bold {{ $index === 0 ? 'border-[#102B38] bg-[#159EAD] text-white shadow-[2px_2px_0_#102B38]' : 'border-slate-200 bg-white text-slate-500' }}">
                    <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full text-[10px] {{ $index === 0 ? 'bg-white text-[#159EAD]' : 'bg-slate-100 text-slate-500' }}">{{ $index + 1 }}</span>
                    <span>{{ $stepLabel }}</span>
                </div>
            @endforeach
        </nav>

        <main class="space-y-8 p-5 sm:space-y-9 sm:p-8">
            <header class="max-w-3xl space-y-2">
                <h1 class="font-heading text-3xl font-black tracking-tight sm:text-4xl">{{ $pageTitle }}</h1>
                <p class="text-sm leading-relaxed text-slate-600 sm:text-base">{{ $pageDescription }}</p>
            </header>

            <section class="space-y-3" aria-labelledby="pref-level-heading">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="pref-level-heading" class="flex items-center gap-2 text-sm font-extrabold uppercase tracking-wide"><span class="h-2.5 w-2.5 rounded-full bg-[#159EAD]"></span>{{ $levelSectionTitle }}</h2>
                    <span class="hidden text-xs text-slate-500 sm:block">{{ $levels->count() }} {{ $levelCountLabel }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    @foreach($levels as $level)
                        @php($selected = (string) $selectedLevelId === (string) $level->level_id)
                        <button type="button" data-level="{{ $level->level_id }}" data-label="{{ $level->summary_label }}" data-eyebrow="{{ $level->eyebrow }}" aria-pressed="{{ $selected ? 'true' : 'false' }}"
                            class="pref-card {{ $selected ? 'is-selected bg-[#FFF8C5]' : 'bg-white' }} relative min-h-28 rounded-2xl border-2 border-[#102B38] p-4 text-left transition hover:-translate-y-0.5 hover:shadow-md">
                            <span class="pref-eyebrow block text-[11px] font-bold uppercase tracking-wide {{ $selected ? 'text-[#159EAD]' : 'text-slate-500' }}">{{ $selected ? $selectedLabel : $level->eyebrow }}</span>
                            <span class="mt-1 block font-heading text-base font-bold">{{ $level->title }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $level->description }}</span>
                            @if($selected)<span class="pref-check" aria-hidden="true">✓</span>@endif
                        </button>
                    @endforeach
                </div>
                <div data-grade-section class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="mr-1 text-xs font-bold text-slate-600">{{ $gradePickerLabel }}</span>
                    <div data-grades class="flex flex-wrap gap-2">
                        @foreach($levels as $level)
                            @foreach($level->grades as $grade)
                                @php($gradeSelected = (string) $selectedLevelId === (string) $level->level_id && (string) $selectedGradeId === (string) $grade->grade_id)
                                <button type="button" data-grade="{{ $grade->grade_id }}" data-level-id="{{ $level->level_id }}" data-label="{{ $grade->label }}" aria-pressed="{{ $gradeSelected ? 'true' : 'false' }}"
                                    class="pref-grade min-w-10 rounded-full border px-3 py-1.5 text-xs font-bold transition {{ $gradeSelected ? 'border-[#102B38] bg-[#102B38] text-white' : 'border-slate-300 bg-white text-slate-600' }}"
                                    {{ (string) $selectedLevelId !== (string) $level->level_id ? 'hidden' : '' }}>{{ $grade->label }}</button>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="space-y-3" aria-labelledby="pref-subject-heading">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="pref-subject-heading" class="flex items-center gap-2 text-sm font-extrabold uppercase tracking-wide"><span class="h-2.5 w-2.5 rounded-full bg-[#159EAD]"></span>{{ $subjectSectionTitle }}</h2>
                    <span class="rounded-lg border border-[#159EAD] px-2.5 py-1 text-[11px] font-semibold text-[#159EAD]">{{ $curriculumBadge }}</span>
                </div>
                <div data-subjects class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach($subjects as $subject)
                        @php($subjectSelected = (string) $selectedSubjectId === (string) $subject->subject_id)
                        <button type="button" data-subject="{{ $subject->subject_id }}" data-level-id="{{ $subject->level_id }}" data-label="{{ $subject->name }}" aria-pressed="{{ $subjectSelected ? 'true' : 'false' }}"
                            class="pref-subject {{ $subjectSelected ? 'is-selected' : '' }} min-h-24 rounded-2xl border-2 border-[#102B38] p-3 text-left transition hover:-translate-y-0.5 hover:shadow-md"
                            {{ $subject->level_id && (string) $subject->level_id !== (string) $selectedLevelId ? 'hidden' : '' }}>
                            <span class="mb-2 grid h-8 w-8 place-items-center rounded-lg border border-[#102B38]/30 bg-slate-50 text-lg" aria-hidden="true">{{ $subject->icon }}</span>
                            <span class="block text-sm font-bold leading-tight">{{ $subject->name }}</span>
                            <span class="mt-1 block text-[11px] text-slate-500">{{ $subject->description }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <section class="space-y-3" aria-labelledby="pref-method-heading">
                <div class="flex items-center justify-between gap-3">
                    <h2 id="pref-method-heading" class="flex items-center gap-2 text-sm font-extrabold uppercase tracking-wide"><span class="h-2.5 w-2.5 rounded-full bg-[#159EAD]"></span>{{ $methodSectionTitle }}</h2>
                    <span class="hidden text-xs text-slate-500 sm:block">{{ $methodHint }}</span>
                </div>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    @foreach($methods as $method)
                        @php($methodSelected = (string) $selectedMethodId === (string) $method->method_id)
                        <button type="button" data-method="{{ $method->method_id }}" data-label="{{ $method->summary_label }}" aria-pressed="{{ $methodSelected ? 'true' : 'false' }}"
                            class="pref-method {{ $methodSelected ? 'is-selected border-[#159EAD]' : 'border-[#102B38]' }} relative rounded-2xl border-2 bg-white p-4 text-left transition hover:shadow-md sm:p-5">
                            @if($methodSelected)<span class="pref-check" aria-hidden="true">✓</span>@endif
                            <span class="flex items-start gap-3">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl border-2 border-[#102B38] bg-slate-100 text-2xl" aria-hidden="true">{{ $method->icon }}</span>
                                <span>
                                    <span class="block font-heading text-base font-bold">{{ $method->title }}</span>
                                    <span class="mt-1 block text-xs leading-relaxed text-slate-600">{{ $method->description }}</span>
                                    <span class="mt-3 flex flex-wrap gap-2">
                                        @foreach($method->features as $feature)
                                            <span class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-600">{{ $feature->label }}</span>
                                        @endforeach
                                    </span>
                                </span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        </main>

        <footer class="border-t-2 border-[#102B38]/20 bg-slate-50 px-5 py-5 sm:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex flex-wrap items-center gap-2 text-xs text-slate-600"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $summaryLabel }}<strong data-summary class="rounded-full border-2 border-[#102B38] bg-white px-3 py-1.5 text-[#102B38]"></strong></p>
                <a data-next-link href="{{ $nextStepUrl }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-[#102B38] bg-[#FFB52E] px-6 py-3.5 text-center text-sm font-extrabold text-[#102B38] shadow-[3px_3px_0_#102B38] transition hover:-translate-y-0.5 hover:bg-amber-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#159EAD]">{{ $nextStepLabel }}</a>
            </div>
        </footer>
    </div>
    <p class="mx-auto mt-3 w-fit rounded-full border border-slate-200 bg-white/80 px-4 py-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $securityNotice }}</p>
</x-app-layout>

<style>
    [data-booking-preferences] .pref-card.is-selected,[data-booking-preferences] .pref-subject.is-selected{background:#fff8c5;box-shadow:3px 3px 0 #102b38}
    [data-booking-preferences] .pref-check{position:absolute;top:-9px;right:-9px;display:grid;width:25px;height:25px;place-items:center;border:2px solid #102b38;border-radius:9999px;background:#159ead;color:white;font-size:13px;font-weight:900}
</style>

<script>
(() => {
    const root = document.querySelector('[data-booking-preferences]');
    if (!root) return;
    const state = {
        level: root.dataset.selectedLevel,
        grade: root.dataset.selectedGrade,
        subject: root.dataset.selectedSubject,
        method: root.dataset.selectedMethod
    };
    const summary = root.querySelector('[data-summary]');

    function render() {
        root.querySelectorAll('[data-level]').forEach(button => {
            const active = button.dataset.level === state.level;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-selected', active);
            button.querySelector('.pref-eyebrow').textContent = active ? @json($selectedLabel) : button.dataset.eyebrow;
            button.classList.toggle('bg-[#FFF8C5]', active);
            button.classList.toggle('bg-white', !active);
            button.querySelector('.pref-check')?.remove();
            if (active) button.insertAdjacentHTML('beforeend','<span class="pref-check" aria-hidden="true">✓</span>');
        });

        const gradeButtons = [...root.querySelectorAll('[data-grade]')];
        gradeButtons.forEach(button => {
            const visible = button.dataset.levelId === state.level;
            button.hidden = !visible;
            const active = visible && button.dataset.grade === state.grade;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('border-[#102B38]', active);
            button.classList.toggle('bg-[#102B38]', active);
            button.classList.toggle('text-white', active);
            if (visible && !state.grade) state.grade = button.dataset.grade;
        });

        const visibleGrades = gradeButtons.filter(button => button.dataset.levelId === state.level);
        root.querySelector('[data-grade-section]').hidden = visibleGrades.length === 0;
        if (!visibleGrades.some(button => button.dataset.grade === state.grade)) state.grade = visibleGrades[0]?.dataset.grade || '';
        gradeButtons.forEach(button => {
            const active = !button.hidden && button.dataset.grade === state.grade;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('border-[#102B38]', active);
            button.classList.toggle('bg-[#102B38]', active);
            button.classList.toggle('text-white', active);
        });

        const subjectButtons = [...root.querySelectorAll('[data-subject]')];
        subjectButtons.forEach(button => {
            button.hidden = Boolean(button.dataset.levelId) && button.dataset.levelId !== state.level;
            button.setAttribute('aria-pressed','false');
        });
        const availableSubjects = subjectButtons.filter(button => !button.hidden);
        if (!availableSubjects.some(button => button.dataset.subject === state.subject)) state.subject = availableSubjects[0]?.dataset.subject || '';
        availableSubjects.forEach(button => {
            const active = button.dataset.subject === state.subject;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-selected', active);
        });

        root.querySelectorAll('[data-method]').forEach(button => {
            const active = button.dataset.method === state.method;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('is-selected', active);
            button.classList.toggle('border-[#159EAD]', active);
            button.classList.toggle('border-[#102B38]', !active);
            button.querySelector('.pref-check')?.remove();
            if (active) button.insertAdjacentHTML('afterbegin','<span class="pref-check" aria-hidden="true">✓</span>');
        });

        const label = selector => root.querySelector(selector + '[aria-pressed="true"]')?.dataset.label || '';
        summary.textContent = [label('[data-level]'), label('[data-grade]'), label('[data-subject]'), label('[data-method]')].filter(Boolean).join(' • ');
        const url = new URL(root.dataset.nextUrl, window.location.origin);
        url.searchParams.set('jenjang', state.level);
        if (state.grade) url.searchParams.set('kelas', state.grade);
        else url.searchParams.delete('kelas');
        url.searchParams.set('mapel', state.subject);
        url.searchParams.set('metode', state.method);
        root.querySelector('[data-next-link]').href = url.pathname + url.search;
    }

    root.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button || !root.contains(button)) return;
        if (button.dataset.level) state.level = button.dataset.level;
        else if (button.dataset.grade) state.grade = button.dataset.grade;
        else if (button.dataset.subject) state.subject = button.dataset.subject;
        else if (button.dataset.method) state.method = button.dataset.method;
        else return;
        render();
    });
    render();
})();
</script>
