<x-app-layout>
    <div data-booking-tutors
        data-selected-tutor="{{ $selectedTutorId }}"
        data-next-url="{{ $nextStepUrl }}"
        data-previous-url="{{ $previousStepUrl }}"
        class="mx-auto -mt-5 max-w-5xl overflow-hidden rounded-[28px] border-2 border-[#102B38] bg-white text-[#102B38] shadow-[5px_5px_0_#102B38]">
        <header class="flex items-center justify-between gap-4 border-b-2 border-[#102B38] px-5 py-4 sm:px-7">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="KawalBelajar">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border-2 border-[#102B38] bg-[#159EAD] font-heading text-xl font-black text-white" aria-hidden="true">{{ $brandInitial }}</span>
                <span><span class="block font-heading text-base font-bold sm:text-lg">{{ $brandName }}</span><span class="hidden text-xs text-slate-600 sm:block">{{ $brandSubtitle }}</span></span>
            </a>
            <span class="shrink-0 rounded-full border-2 border-[#102B38] bg-[#FFB52E] px-3 py-2 text-[10px] font-extrabold uppercase sm:px-4 sm:text-xs">{{ $stepCounterLabel }}</span>
        </header>

        <nav aria-label="{{ $progressAriaLabel }}" class="grid grid-cols-2 gap-2 border-b border-slate-200 bg-slate-50 px-4 py-4 sm:grid-cols-5 sm:px-6">
            @foreach($steps as $index => $stepLabel)
                @php($completedOrCurrent = $index < $currentStepIndex)
                <div aria-current="{{ $index === $currentStepIndex ? 'step' : 'false' }}" class="flex items-center justify-center gap-2 rounded-xl border px-2 py-2.5 text-center text-[11px] font-bold {{ $index === $currentStepIndex ? 'border-[#102B38] bg-[#159EAD] text-white shadow-[2px_2px_0_#102B38]' : ($completedOrCurrent ? 'border-slate-300 bg-white text-[#102B38]' : 'border-slate-200 bg-white text-slate-400') }}">
                    <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full text-[10px] {{ $index === $currentStepIndex ? 'bg-white text-[#159EAD]' : ($completedOrCurrent ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500') }}">{{ $index < $currentStepIndex ? '✓' : $index + 1 }}</span>
                    <span>{{ $stepLabel }}</span>
                </div>
            @endforeach
        </nav>

        <main class="space-y-5 p-5 sm:space-y-6 sm:p-8">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="font-heading text-2xl font-black tracking-tight sm:text-3xl">{{ $pageTitle }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ $pageDescription }}</p>
                </div>
                <p data-preference-badge class="w-fit rounded-xl border-2 border-[#102B38] bg-white px-3 py-2 text-xs font-bold shadow-[2px_2px_0_#102B38]"><span class="mr-1 text-emerald-500">●</span><span data-preference-label>{{ $preferenceSummary }}</span></p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_16rem]">
                <label class="relative block">
                    <span class="sr-only">{{ $searchLabel }}</span>
                    <input data-search type="search" value="{{ $searchQuery }}" placeholder="{{ $searchPlaceholder }}" class="w-full rounded-xl border-2 border-slate-300 bg-white px-4 py-3 pr-11 text-sm font-medium outline-none transition focus:border-[#159EAD] focus:ring-2 focus:ring-[#159EAD]/20">
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-lg" aria-hidden="true">⌕</span>
                </label>
                <label>
                    <span class="sr-only">{{ $sortLabel }}</span>
                    <select data-sort class="w-full rounded-xl border-2 border-slate-300 bg-white px-4 py-3 text-sm font-medium outline-none transition focus:border-[#159EAD] focus:ring-2 focus:ring-[#159EAD]/20">
                        @foreach($sortOptions as $option)
                            <option value="{{ $option->value }}" {{ $selectedSort === $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <section data-tutor-list class="space-y-3" aria-label="{{ $tutorListLabel }}">
                @foreach($tutors as $tutor)
                    @php($selected = (string) $selectedTutorId === (string) $tutor->teacher_id)
                    <article data-tutor-card
                        data-id="{{ $tutor->teacher_id }}"
                        data-name="{{ $tutor->display_name }}"
                        data-rating="{{ $tutor->rating }}"
                        data-hours="{{ $tutor->teaching_hours }}"
                        data-price="{{ $tutor->price_per_student }}"
                        class="tutor-card {{ $selected ? 'is-selected' : '' }} relative rounded-2xl border-2 border-slate-300 bg-white p-4 transition sm:p-5">
                        @if($tutor->is_featured)
                            <span class="absolute -top-3 right-5 rounded-full border-2 border-[#102B38] bg-[#FFB52E] px-3 py-1 text-[10px] font-extrabold uppercase shadow-[2px_2px_0_#102B38]">{{ $featuredTutorLabel }}</span>
                        @endif
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="flex min-w-0 flex-1 items-start gap-3">
                                @if($tutor->avatar_url)
                                    <img src="{{ $tutor->avatar_url }}" alt="" class="h-[4.5rem] w-[4.5rem] shrink-0 rounded-2xl border-2 border-[#102B38] object-cover">
                                @else
                                    <span class="grid h-[4.5rem] w-[4.5rem] shrink-0 place-items-center rounded-2xl border-2 border-[#102B38] bg-[#E9F6F8] text-lg font-bold">{{ $tutor->initials }}</span>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 data-name-label class="font-heading text-base font-bold sm:text-lg">{{ $tutor->display_name }}</h2>
                                        @if($tutor->is_verified)<span class="rounded-md border border-emerald-400 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{{ $verifiedLabel }}</span>@endif
                                    </div>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $tutor->university }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-bold">
                                        <span class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1 text-amber-800">⭐ {{ number_format($tutor->rating, 1) }}<span class="font-normal text-slate-400">/{{ $maximumRating }}</span></span>
                                        <span>{{ $teachingHoursIcon }} {{ number_format($tutor->teaching_hours) }}+ {{ $teachingHoursLabel }}</span>
                                    </div>
                                    <p class="mt-2 max-w-3xl text-xs leading-relaxed text-slate-600">{{ $tutor->description }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center justify-between gap-4 border-t border-slate-100 pt-3 sm:w-36 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                                <div class="sm:text-right">
                                    <span class="block text-[10px] font-semibold text-slate-500">{{ $priceLabel }}</span>
                                    <strong class="block text-lg font-extrabold {{ $selected ? 'text-[#159EAD]' : '' }}">{{ $currencyPrefix }} {{ number_format($tutor->price_per_student, 0, ',', '.') }}</strong>
                                    <span class="block text-[10px] text-slate-400">{{ $priceUnitLabel }}</span>
                                </div>
                                <button type="button" data-select-tutor="{{ $tutor->teacher_id }}" aria-pressed="{{ $selected ? 'true' : 'false' }}" class="tutor-select rounded-xl border-2 border-[#102B38] px-4 py-2 text-xs font-bold transition {{ $selected ? 'bg-[#159EAD] text-white shadow-[2px_2px_0_#102B38]' : 'bg-white hover:bg-slate-50' }}">{{ $selected ? $selectedButtonLabel : $chooseButtonLabel }}</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <p data-empty-state @if($tutors->isNotEmpty()) hidden @endif class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-600">{{ $emptyStateLabel }}</p>

            <section data-selected-summary @if(!$selectedTutor) hidden @endif class="flex flex-col gap-3 rounded-2xl border-2 border-[#102B38] bg-emerald-50 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-500 text-lg text-white" aria-hidden="true">✓</span>
                    <div><p class="text-sm font-bold">{{ $selectedTutorLabel }} <span data-selected-name class="underline underline-offset-2">{{ $selectedTutor?->display_name }}</span></p><p class="text-xs font-medium text-emerald-800">{{ $selectedTutorVerificationSummary }}</p></div>
                </div>
                <span class="w-fit rounded-lg border-2 border-[#102B38] bg-white px-3 py-1.5 text-[11px] font-bold text-emerald-700">{{ $guaranteeLabel }}</span>
            </section>
        </main>

        <footer class="mx-5 border-t-2 border-[#102B38]/20 py-5 sm:mx-8">
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a data-back-link href="{{ $previousStepUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#102B38] bg-white px-5 py-3 text-sm font-bold shadow-[2px_2px_0_#102B38] transition hover:bg-slate-50">{{ $backButtonLabel }}</a>
                <a data-next-link href="{{ $nextStepUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#102B38] bg-[#FFB52E] px-5 py-3 text-center text-sm font-extrabold shadow-[3px_3px_0_#102B38] transition hover:-translate-y-0.5 hover:bg-amber-400">{{ $nextButtonLabel }}</a>
            </div>
        </footer>
    </div>
</x-app-layout>

<style>
    [data-booking-tutors] .tutor-card.is-selected{border-color:#159ead;box-shadow:0 0 0 2px #159ead,3px 3px 0 #102b38}
</style>

<script>
(() => {
    const root = document.querySelector('[data-booking-tutors]');
    if (!root) return;
    const cards = [...root.querySelectorAll('[data-tutor-card]')];
    const search = root.querySelector('[data-search]');
    const sort = root.querySelector('[data-sort]');
    const emptyState = root.querySelector('[data-empty-state]');
    const selectedSummary = root.querySelector('[data-selected-summary]');
    let selectedId = root.dataset.selectedTutor || '';

    function updateLinks() {
        const params = new URLSearchParams(window.location.search);
        const nextUrl = new URL(root.dataset.nextUrl, window.location.origin);
        params.forEach((value,key) => nextUrl.searchParams.set(key,value));
        nextUrl.searchParams.set('tutor_id',selectedId);
        root.querySelector('[data-next-link]').href = nextUrl.pathname + nextUrl.search;
        const backUrl = new URL(root.dataset.previousUrl, window.location.origin);
        params.forEach((value,key) => backUrl.searchParams.set(key,value));
        root.querySelector('[data-back-link]').href = backUrl.pathname + backUrl.search;
    }

    function render() {
        const query = search.value.trim().toLocaleLowerCase();
        const visibleCards = cards.filter(card => card.dataset.name.toLocaleLowerCase().includes(query));
        const sortBy = sort.value;
        visibleCards.sort((a,b) => {
            const left = Number(a.dataset[sortBy]);
            const right = Number(b.dataset[sortBy]);
            return right - left;
        });
        visibleCards.forEach(card => root.querySelector('[data-tutor-list]').appendChild(card));
        cards.forEach(card => {
            card.hidden = !visibleCards.includes(card);
            const selected = card.dataset.id === selectedId;
            card.classList.toggle('is-selected',selected);
            const button = card.querySelector('[data-select-tutor]');
            button.setAttribute('aria-pressed',String(selected));
            button.textContent = selected ? @json($selectedButtonLabel) : @json($chooseButtonLabel);
            button.classList.toggle('bg-[#159EAD]',selected);
            button.classList.toggle('text-white',selected);
            button.classList.toggle('shadow-[2px_2px_0_#102B38]',selected);
            button.classList.toggle('bg-white',!selected);
        });
        emptyState.hidden = visibleCards.length > 0;
        const selectedCard = cards.find(card => card.dataset.id === selectedId);
        selectedSummary.hidden = !selectedCard;
        if (selectedCard) root.querySelector('[data-selected-name]').textContent = selectedCard.dataset.name;
        updateLinks();
    }

    root.addEventListener('click',event => {
        const button = event.target.closest('[data-select-tutor]');
        if (!button) return;
        selectedId = button.dataset.selectTutor;
        render();
    });
    search.addEventListener('input',render);
    sort.addEventListener('change',render);
    render();
})();
</script>
