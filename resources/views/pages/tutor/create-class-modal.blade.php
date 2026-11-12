@if ($showCreateModal ?? false)
    @php
        $createClassSteps = [
            'Pilih Paket & Jenjang',
            'Detail Kelas',
            'Modul PDF',
            'Soal Diagnostik',
            'Jadwal Mengajar',
        ];
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm sm:p-6" role="dialog" aria-modal="true" aria-labelledby="create-class-title">
        <section class="relative flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-[28px] border-2 border-cyan-500 bg-white shadow-2xl">
            <header class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-7">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-cyan-700">Pembuatan Kelas</p>
                    <h2 id="create-class-title" class="text-lg font-extrabold text-[#102d3f]">Buat Kelas Baru</h2>
                </div>
                <a href="{{ route('tutor.classes') }}" aria-label="Tutup popup" class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-2xl text-slate-600 hover:bg-slate-200">&times;</a>
            </header>

            <div class="shrink-0 overflow-x-auto border-b border-slate-100 px-5 py-3 sm:px-7">
                <ol class="flex min-w-max items-center gap-2" aria-label="Tahapan pembuatan kelas">
                    @foreach ($createClassSteps as $index => $stepLabel)
                        <li data-step-indicator="{{ $index + 1 }}" class="flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $index === 0 ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            <span>{{ $index + 1 }}</span><span>{{ $stepLabel }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>

            <form id="create-class-form" method="POST" action="{{ route('tutor.classes.store') }}" enctype="multipart/form-data" class="min-h-0 flex-1 overflow-y-auto">
                @csrf
                @if ($errors->any())
                    <div class="mx-5 mt-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 sm:mx-7">
                        <p class="font-bold">Periksa kembali data yang dimasukkan:</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <input type="hidden" name="package_type" id="package-type" value="{{ old('package_type', 'monthly') }}">
                <input type="hidden" name="grade_level" id="grade-level" value="{{ old('grade_level') }}">
                <input type="hidden" name="learning_method" id="learning-method" value="{{ old('learning_method') }}">

                <div class="space-y-5 p-5 sm:p-7">
                    {{-- Tahap 1 --}}
                    <section data-step-panel="1" class="space-y-5">
                        <div class="grid gap-3 rounded-2xl border-2 border-cyan-500 p-3 sm:grid-cols-2">
                            <button type="button" data-package-choice="monthly" aria-pressed="true" class="package-choice rounded-full border border-[#102d3f] bg-cyan-600 px-5 py-3 text-sm font-bold text-white">Paket 1 Bulan (4+ Sesi)</button>
                            <button type="button" data-package-choice="instant" aria-pressed="false" class="package-choice rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-cyan-700">1 Sesi Pembelajaran Instan</button>
                        </div>

                        <div class="max-w-2xl rounded-[24px] border-2 border-cyan-500 bg-[#e7f5f9] p-5 sm:p-7">
                            <h3 class="font-extrabold">Pilih Jenjang mengajar</h3>
                            <p class="mt-1 text-xs text-slate-500">Pilihan sesuai dengan verifikasi ijazah dan kompetensimu</p>
                            @if (count($approvedLevels ?? []))
                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($approvedLevels as $level)
                                        <button type="button" data-grade-choice="{{ $level }}" aria-pressed="false" class="grade-choice min-w-20 rounded-full bg-white px-5 py-2 text-sm font-bold text-slate-600 hover:bg-cyan-100">{{ $level }}</button>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4 rounded-xl bg-white p-3 text-sm text-amber-800">Jenjang mengajar belum diverifikasi, sehingga kelas belum dapat dibuat.</p>
                            @endif
                        </div>

                        <div class="flex justify-end">
                            <button type="button" data-next class="rounded-full bg-[#102d3f] px-7 py-3 text-sm font-bold text-white hover:bg-slate-800">Lanjut ke Detail Kelas →</button>
                        </div>
                    </section>

                    {{-- Tahap 2 --}}
                    <section data-step-panel="2" class="hidden space-y-5">
                        <div class="rounded-[24px] border-2 border-cyan-500 bg-[#e7f5f9] p-5 sm:p-7">
                            <h3 class="font-extrabold">Detail Kelas &amp; Format Bimbingan</h3>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label class="block text-sm font-semibold">Nama Program / Kelas
                                    <input name="package_name" value="{{ old('package_name') }}" maxlength="255" class="mt-2 w-full rounded-full border-2 border-cyan-100 bg-white px-4 py-2.5 text-sm font-normal outline-none focus:border-cyan-500" placeholder="Contoh: Pendalaman Rumus Newton">
                                </label>
                                <label class="block text-sm font-semibold">Mata Pelajaran
                                    <input name="subject_name" value="{{ old('subject_name') }}" maxlength="100" class="mt-2 w-full rounded-full border-2 border-cyan-100 bg-white px-4 py-2.5 text-sm font-normal outline-none focus:border-cyan-500" placeholder="Contoh: Fisika">
                                </label>
                            </div>

                            <fieldset class="mt-5">
                                <legend class="text-sm font-bold">Metode Belajar &amp; Kapasitas Kuota</legend>
                                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                    <button type="button" data-method-choice="online_zoom" aria-pressed="false" class="method-choice rounded-2xl border-2 border-cyan-100 bg-white p-4 text-left hover:border-cyan-500">
                                        <span class="font-bold">Online Zoom (1-on-1)</span>
                                        <span class="mt-1 block text-xs text-slate-500">Maksimal 1 siswa (fokus virtual interaktif)</span>
                                    </button>
                                    <button type="button" data-method-choice="home_visit" aria-pressed="false" class="method-choice rounded-2xl border-2 border-cyan-100 bg-white p-4 text-left hover:border-cyan-500">
                                        <span class="font-bold">Home Visit (Tatap Muka)</span>
                                        <span class="mt-1 block text-xs text-slate-500">Mendukung kelas Squad (maksimal 5 siswa)</span>
                                    </button>
                                </div>
                            </fieldset>

                            <div id="home-visit-fields" class="mt-4 hidden gap-4 rounded-2xl border border-cyan-300 bg-white/60 p-4 sm:grid-cols-2">
                                <label class="block text-sm font-semibold">Kapasitas Maksimal Siswa
                                    <select name="target_capacity" class="mt-2 w-full rounded-full border border-cyan-100 bg-white px-4 py-2.5 text-sm font-normal">
                                        <option value="">Pilih kapasitas</option>
                                        @foreach (range(2, 5) as $capacity)
                                            <option value="{{ $capacity }}" @selected(old('target_capacity') == $capacity)>{{ $capacity }} Siswa</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block text-sm font-semibold">Titik Kumpul / Alamat Default Tutor
                                    <input name="default_location" value="{{ old('default_location', $defaultLocation ?? '') }}" maxlength="1000" class="mt-2 w-full rounded-full border border-cyan-100 bg-white px-4 py-2.5 text-sm font-normal" placeholder="Masukkan alamat tutor">
                                </label>
                            </div>

                            <label class="mt-4 block text-sm font-semibold">Tarif Dasar per Sesi (60 Menit)
                                <div class="mt-2 flex items-center rounded-full border-2 border-cyan-100 bg-white px-4">
                                    <span class="text-sm text-slate-500">Rp</span>
                                    <input name="base_price" id="base-price" type="number" min="1" step="1000" value="{{ old('base_price') }}" class="w-full rounded-full bg-transparent px-3 py-2 text-sm outline-none" placeholder="Tarif sesuai jenjang dan metode">
                                </div>
                            </label>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" data-back class="rounded-full px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100">← Kembali</button>
                            <button type="button" data-next class="rounded-full bg-[#102d3f] px-7 py-3 text-sm font-bold text-white hover:bg-slate-800">Lanjut →</button>
                        </div>
                    </section>

                    {{-- Tahap 3 --}}
                    <section data-step-panel="3" class="hidden space-y-5">
                        <div class="rounded-[24px] border-2 border-cyan-200 bg-white p-5 sm:p-7">
                            <h3 class="font-extrabold">Modul Materi Pembelajaran (PDF / PPT)</h3>
                            <label class="mt-5 flex min-h-56 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-cyan-500 bg-cyan-50/60 p-6 text-center hover:bg-cyan-50">
                                <span class="text-4xl" aria-hidden="true">📄</span>
                                <span id="module-file-label" class="mt-3 rounded-full bg-cyan-600 px-6 py-3 text-sm font-bold text-white">Pilih File Modul PDF</span>
                                <input id="module-pdf" name="module_pdf" type="file" accept="application/pdf,.pdf,.ppt,.pptx" class="sr-only">
                                <span class="mt-3 text-sm text-slate-500">PDF atau PPT, maksimal 20 MB. Modul dapat dilengkapi setelah draf kelas dibuat.</span>
                            </label>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" data-back class="rounded-full px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100">← Kembali</button>
                            <button type="button" data-next class="rounded-full bg-[#102d3f] px-7 py-3 text-sm font-bold text-white hover:bg-slate-800">Lanjut →</button>
                        </div>
                    </section>

                    {{-- Tahap 4 --}}
                    <section data-step-panel="4" class="hidden space-y-5">
                        <div class="rounded-[24px] border-2 border-[#102d3f] bg-white p-5 sm:p-7">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-extrabold">Susun Soal Kuis Diagnostik (Pre-Test &amp; Post-Test)</h3>
                                    <p class="mt-1 text-sm text-slate-500">Soal akan disajikan sebelum dan sesudah sesi mengajar.</p>
                                </div>
                                <span id="question-count" class="rounded-full bg-cyan-100 px-5 py-2 text-sm font-bold text-cyan-700">0 Soal Tersedia</span>
                            </div>
                            <div id="question-list" class="mt-4 space-y-3"></div>
                            <div class="mt-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5">
                                <h4 class="text-sm font-extrabold">+ Tambah Butir Soal Baru</h4>
                                <p class="mt-1 text-xs text-slate-500">Setiap soal akan digunakan untuk Pre-Test dan Post-Test.</p>
                                <button type="button" id="add-question" class="mt-3 rounded-full bg-[#102d3f] px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-800">+ Tambah Soal</button>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" data-back class="rounded-full px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100">← Kembali</button>
                            <button type="button" data-next class="rounded-full bg-[#102d3f] px-7 py-3 text-sm font-bold text-white hover:bg-slate-800">Lanjut →</button>
                        </div>
                    </section>

                    {{-- Tahap 5 --}}
                    <section data-step-panel="5" class="hidden space-y-5">
                        <div class="rounded-[24px] border-2 border-cyan-200 bg-white p-5 sm:p-7">
                            <h3 class="font-extrabold">Ketersediaan Jadwal Mengajar</h3>
                            <p class="mt-2 text-sm text-slate-600">Semua program menggunakan Master Availability Pool yang sama. Atur slot mingguan sekali saja; slot yang sudah dipilih siswa akan dikunci otomatis di seluruh mata pelajaran.</p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse (($availabilitySlots ?? collect()) as $slot)
                                    <span class="rounded-full border border-emerald-300 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">{{ $slot->day_of_week }}, {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</span>
                                @empty
                                    <span class="rounded-full border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900">Belum ada slot tersedia. Kelas dapat dibuat, lalu tambahkan slot di Master Availability agar siswa dapat memilih jadwal.</span>
                                @endforelse
                            </div>
                            <a href="{{ route('tutor.availability') }}" class="mt-4 inline-flex rounded-full border border-[#102d3f] px-4 py-2 text-xs font-bold hover:bg-slate-50">Kelola Master Availability →</a>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="button" data-back class="rounded-full px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100">← Kembali</button>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('tutor.classes') }}" class="rounded-full px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-100">Batal</a>
                                <button type="submit" class="rounded-full bg-amber-400 px-7 py-3 text-sm font-extrabold text-[#102d3f] shadow hover:bg-amber-300">Simpan Draf &amp; Lengkapi Materi &rarr;</button>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </section>
    </div>

    <script>
        (() => {
            const form = document.getElementById('create-class-form');
            if (!form) return;

            const panels = [...form.querySelectorAll('[data-step-panel]')];
            const indicators = [...document.querySelectorAll('[data-step-indicator]')];
            const packageInput = document.getElementById('package-type');
            const gradeInput = document.getElementById('grade-level');
            const methodInput = document.getElementById('learning-method');
            const priceInput = document.getElementById('base-price');
            const tariffs = @json($tariffs ?? []);
            let step = 1;
            let questionIndex = 0;

            const showStep = (nextStep) => {
                step = Math.max(1, Math.min(5, nextStep));
                panels.forEach(panel => panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step));
                indicators.forEach(item => {
                    const active = Number(item.dataset.stepIndicator) === step;
                    item.classList.toggle('bg-cyan-600', active);
                    item.classList.toggle('text-white', active);
                    item.classList.toggle('bg-slate-100', !active);
                    item.classList.toggle('text-slate-500', !active);
                });
                form.querySelector('.overflow-y-auto').scrollTop = 0;
            };

            const showError = (message) => window.alert(message);
            const methodIsHomeVisit = () => methodInput.value === 'home_visit';

            const refreshTariff = () => {
                const key = `${(gradeInput.value || '').toUpperCase()}|${methodInput.value}`;
                const rate = tariffs[key];
                if (rate) {
                    priceInput.value = rate;
                    priceInput.readOnly = true;
                    priceInput.classList.add('text-slate-700');
                } else {
                    priceInput.readOnly = false;
                }
            };

            document.querySelectorAll('[data-package-choice]').forEach(button => button.addEventListener('click', () => {
                packageInput.value = button.dataset.packageChoice;
                document.querySelectorAll('[data-package-choice]').forEach(choice => {
                    const active = choice === button;
                    choice.setAttribute('aria-pressed', active);
                    choice.classList.toggle('bg-cyan-600', active);
                    choice.classList.toggle('text-white', active);
                    choice.classList.toggle('bg-white', !active);
                    choice.classList.toggle('text-cyan-700', !active);
                });
            }));

            document.querySelectorAll('[data-grade-choice]').forEach(button => button.addEventListener('click', () => {
                gradeInput.value = button.dataset.gradeChoice;
                document.querySelectorAll('[data-grade-choice]').forEach(choice => {
                    const active = choice === button;
                    choice.setAttribute('aria-pressed', active);
                    choice.classList.toggle('bg-cyan-600', active);
                    choice.classList.toggle('text-white', active);
                    choice.classList.toggle('bg-white', !active);
                    choice.classList.toggle('text-slate-600', !active);
                });
                refreshTariff();
            }));

            document.querySelectorAll('[data-method-choice]').forEach(button => button.addEventListener('click', () => {
                methodInput.value = button.dataset.methodChoice;
                document.querySelectorAll('[data-method-choice]').forEach(choice => {
                    const active = choice === button;
                    choice.setAttribute('aria-pressed', active);
                    choice.classList.toggle('border-[#102d3f]', active);
                    choice.classList.toggle('bg-amber-50', active);
                    choice.classList.toggle('border-cyan-100', !active);
                });
                document.getElementById('home-visit-fields').classList.toggle('hidden', !methodIsHomeVisit());
                document.getElementById('home-visit-fields').classList.toggle('grid', methodIsHomeVisit());
                refreshTariff();
            }));

            const validateStep = () => {
                if (step === 1 && (!packageInput.value || !gradeInput.value)) {
                    showError('Pilih jenis paket dan jenjang mengajar terlebih dahulu.');
                    return false;
                }
                if (step === 2) {
                    if (!form.elements.package_name.value.trim() || !form.elements.subject_name.value.trim() || !methodInput.value || !priceInput.value) {
                        showError('Lengkapi nama kelas, mata pelajaran, metode belajar, dan tarif.');
                        return false;
                    }
                    if (methodIsHomeVisit() && (!form.elements.target_capacity.value || !form.elements.default_location.value.trim())) {
                        showError('Isi kapasitas siswa dan alamat default tutor untuk Home Visit.');
                        return false;
                    }
                }
                if (step === 3) {
                    const file = document.getElementById('module-pdf').files[0];
                    if (file && (!/\.(pdf|ppt|pptx)$/i.test(file.name) || file.size > 20 * 1024 * 1024)) {
                        showError('Gunakan file PDF atau PPT dengan ukuran maksimal 20 MB. Modul dapat ditambahkan nanti pada editor materi.');
                        return false;
                    }
                }
                if (step === 4) {
                    const questions = [...document.querySelectorAll('[data-question-card]')];
                    if (questions.some(question => !question.querySelector('[data-question-text]').value.trim()
                        || [...question.querySelectorAll('[data-option]')].some(option => !option.value.trim())
                        || !question.querySelector('input[type="radio"]:checked'))) {
                        showError('Lengkapi empat pilihan dan tandai kunci jawaban untuk setiap soal yang mulai diisi.');
                        return false;
                    }
                }
                return true;
            };

            form.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', () => {
                if (validateStep()) showStep(step + 1);
            }));
            form.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => showStep(step - 1)));

            document.getElementById('module-pdf').addEventListener('change', event => {
                document.getElementById('module-file-label').textContent = event.target.files[0]?.name || 'Pilih File Modul PDF';
            });

            const questionList = document.getElementById('question-list');
            const updateQuestionCount = () => {
                const count = questionList.querySelectorAll('[data-question-card]').length;
                document.getElementById('question-count').textContent = `${count} Soal Tersedia`;
            };

            document.getElementById('add-question').addEventListener('click', () => {
                const index = questionIndex++;
                const card = document.createElement('article');
                card.dataset.questionCard = '';
                card.className = 'rounded-2xl border border-slate-200 bg-slate-50 p-4';
                card.innerHTML = `
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="font-bold">Soal ${index + 1}</h4>
                        <button type="button" data-remove-question class="text-sm font-bold text-rose-600 hover:text-rose-800">Hapus</button>
                    </div>
                    <textarea data-question-text name="questions[${index}][question_text]" rows="2" maxlength="5000" placeholder="Tuliskan pertanyaan / soal di sini..." class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"></textarea>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        ${['a', 'b', 'c', 'd'].map(letter => `<label class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm"><span class="font-bold uppercase">${letter}</span><input data-option name="questions[${index}][option_${letter}]" maxlength="1000" placeholder="Pilihan ${letter.toUpperCase()}" class="min-w-0 flex-1 outline-none"><input type="radio" name="questions[${index}][answer_key]" value="${letter}" aria-label="Tandai pilihan ${letter.toUpperCase()} sebagai kunci"></label>`).join('')}
                    </div>`;
                card.querySelector('[data-remove-question]').addEventListener('click', () => {
                    card.remove();
                    updateQuestionCount();
                });
                questionList.appendChild(card);
                updateQuestionCount();
            });

            form.addEventListener('submit', event => {
                if (step !== 5) {
                    event.preventDefault();
                    return;
                }
            });

            const oldPackage = @json(old('package_type'));
            if (oldPackage) [...document.querySelectorAll('[data-package-choice]')].find(button => button.dataset.packageChoice === oldPackage)?.click();
            const oldGrade = @json(old('grade_level'));
            if (oldGrade) [...document.querySelectorAll('[data-grade-choice]')].find(button => button.dataset.gradeChoice === oldGrade)?.click();
            const oldMethod = @json(old('learning_method'));
            if (oldMethod) [...document.querySelectorAll('[data-method-choice]')].find(button => button.dataset.methodChoice === oldMethod)?.click();
            const oldBasePrice = @json(old('base_price'));
            if (oldBasePrice) {
                priceInput.value = oldBasePrice;
                priceInput.readOnly = false;
            }
        })();
    </script>
@endif
