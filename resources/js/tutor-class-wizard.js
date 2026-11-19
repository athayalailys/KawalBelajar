const bootTutorClassWizard = () => {
    const form = document.getElementById('create-class-form');
    if (!form) return;

    const panels = [...form.querySelectorAll('[data-step-panel]')];
    const indicators = [...document.querySelectorAll('[data-wizard-step]')];
    const grade = form.querySelector('#grade-level');
    const method = form.querySelector('#learning-method');
    const packageType = form.querySelector('#package-type');
    const subject = form.querySelector('#subject-name');
    const tariffData = window.kbTutorClassDraftConfig || {};
    const tariffs = tariffData.tariffs || {};
    const draftKey = form.dataset.draftKey;
    const clearKey = form.dataset.clearKey;
    const status = document.getElementById('draft-status');
    const selectedSlots = () => [...form.querySelectorAll('[name="availability_slot_ids[]"]:checked')];
    let step = 1;
    let serverQueue = null;
    let syncing = false;
    let syncTimer = null;

    const canonicalMethod = (value) => value === 'online' ? 'online_zoom' : value;
    const displayMethod = (value) => canonicalMethod(value) === 'online_zoom' ? 'online' : value;
    const pretty = (value) => value?.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()) || '—';
    const price = () => tariffs[`${(grade.value || '').toUpperCase()}|${canonicalMethod(method.value)}`] || null;
    const formatPrice = (value) => value
        ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
        : 'Tarif belum tersedia';
    const packageTotal = () => price() ? price() * (packageType.value === 'monthly' ? 4 : 1) : null;
    const syncCapacityOptions = () => {
        const select = form.elements.target_capacity;
        const maximum = canonicalMethod(method.value) === 'home_visit' ? 5 : 20;
        [...select.options].forEach((option) => {
            if (!option.value) return;
            option.disabled = Number(option.value) > maximum;
        });
        if (Number(select.value) > maximum) select.value = String(maximum);
    };

    const setChoice = (selector, value, dataKey, normalize = (item) => item) => {
        document.querySelectorAll(selector).forEach((button) => {
            const active = normalize(button.dataset[dataKey]) === normalize(value);
            button.classList.toggle('border-slate-900', active);
            button.classList.toggle('border-slate-200', !active);
            button.classList.toggle('bg-amber-50', active);
            button.classList.toggle('bg-white', !active);
            button.setAttribute('aria-pressed', String(active));
        });
    };

    const refreshSubjects = (level) => {
        document.querySelectorAll('[data-subject-choice]').forEach((button) => {
            const visible = button.dataset.subjectLevel.toLowerCase() === (level || '').toLowerCase();
            button.classList.toggle('hidden', !visible);
            if (!visible && button.dataset.subjectChoice === subject.value) subject.value = '';
        });
        setChoice('.subject-choice', subject.value, 'subjectChoice');
    };

    const filterAvailability = () => {
        const selectedMethod = canonicalMethod(method.value);
        document.querySelectorAll('[data-availability-card]').forEach((card) => {
            const slotMethod = canonicalMethod(card.dataset.method);
            const allowed = slotMethod === 'all' || !slotMethod || slotMethod === selectedMethod;
            card.classList.toggle('hidden', !allowed);
            const checkbox = card.querySelector('input[type="checkbox"]');
            if (!allowed && checkbox) checkbox.checked = false;
        });
    };

    const updateMethodFields = () => {
        const online = canonicalMethod(method.value) === 'online_zoom';
        syncCapacityOptions();
        document.getElementById('location-field')?.classList.toggle('hidden', online || !method.value);
        document.getElementById('zoom-url-field')?.classList.toggle('hidden', !online);
        filterAvailability();
        document.getElementById('tariff-display').textContent = `${formatPrice(price())} / sesi / siswa`;
        document.getElementById('package-estimate').textContent = `Estimasi nilai paket per siswa: ${formatPrice(packageTotal())}`;
    };

    const refreshReview = () => {
        form.querySelectorAll('[data-review]').forEach((node) => {
            const key = node.dataset.review;
            let value = form.elements[key]?.value || '—';
            if (key === 'grade_level') value = grade.value || '—';
            if (key === 'learning_method') value = pretty(canonicalMethod(method.value));
            if (key === 'package_type') value = packageType.value === 'monthly' ? 'Paket 4 sesi' : '1 sesi instan';
            node.textContent = value;
        });
        const location = method.value === 'home_visit' ? form.elements.default_location?.value : form.elements.zoom_meeting_url?.value;
        document.getElementById('review-mode-detail').textContent = location || 'Belum diisi';
        document.getElementById('review-tariff').textContent = `${formatPrice(price())} / sesi / siswa`;
        document.getElementById('package-estimate').textContent = `Estimasi nilai paket per siswa: ${formatPrice(packageTotal())}`;
        document.getElementById('review-slots').textContent = selectedSlots()
            .map((input) => input.closest('label')?.querySelector('b')?.textContent)
            .filter(Boolean).join(' · ') || 'Belum dipilih';
    };

    const render = (scroll = true) => {
        panels.forEach((panel) => panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step));
        indicators.forEach((item) => {
            const active = Number(item.dataset.wizardStep) <= step;
            item.classList.toggle('bg-teal-600', active);
            item.classList.toggle('bg-slate-200', !active);
            item.classList.toggle('text-white', active);
            item.classList.toggle('text-slate-600', !active);
        });
        document.getElementById('wizard-back').classList.toggle('hidden', step === 1);
        document.getElementById('wizard-next').classList.toggle('hidden', step === 4);
        document.getElementById('wizard-submit').classList.toggle('hidden', step !== 4);
        if (step === 4) refreshReview();
        if (scroll) window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const valuesFromForm = () => {
        const values = {};
        [...form.elements].forEach((element) => {
            if (!element.name || element.name === '_token' || element.disabled || ['button', 'submit', 'file'].includes(element.type)) return;
            const name = element.name === 'availability_slot_ids[]' ? 'availability_slot_ids' : element.name;
            if (element.type === 'checkbox') {
                values[name] ??= [];
                if (element.checked) values[name].push(element.value);
            } else if (element.type !== 'radio') {
                values[name] = element.value;
            }
        });
        values.availability_slot_ids = selectedSlots().map((input) => input.value);
        return values;
    };

    const makeDraft = () => ({
        version: 1,
        step,
        savedAt: Date.now(),
        values: valuesFromForm(),
    });

    const saveLocally = (draft) => {
        try {
            localStorage.removeItem(clearKey);
            localStorage.setItem(draftKey, JSON.stringify(draft));
            status.textContent = 'Draft tersimpan di perangkat.';
            status.className = 'text-xs font-semibold text-emerald-700';
            return true;
        } catch {
            status.textContent = 'Penyimpanan browser tidak tersedia; draft server akan dicoba.';
            status.className = 'text-xs font-semibold text-amber-700';
            return false;
        }
    };

    const syncLatestDraft = async () => {
        if (syncing || !navigator.onLine || !serverQueue) return;
        syncing = true;
        while (serverQueue && navigator.onLine) {
            const current = serverQueue;
            serverQueue = null;
            try {
                const response = await fetch(form.dataset.draftSaveUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || '',
                    },
                    body: JSON.stringify({ draft: current }),
                });
                if (!response.ok) throw new Error('Draft belum tersinkron ke server.');
                const result = await response.json();
                current.savedAt = result.savedAt || current.savedAt;
                saveLocally(current);
                status.textContent = 'Draft tersimpan otomatis di server.';
                status.className = 'text-xs font-semibold text-emerald-700';
            } catch {
                serverQueue = current;
                status.textContent = navigator.onLine ? 'Draft tersimpan di perangkat; sinkronisasi server akan dicoba lagi.' : 'Tersimpan di perangkat (offline).';
                status.className = 'text-xs font-semibold text-amber-700';
                if (navigator.onLine) syncTimer = window.setTimeout(syncLatestDraft, 5000);
                break;
            }
        }
        syncing = false;
    };

    const persistDraft = (immediateSync = false) => {
        const draft = makeDraft();
        if (!saveLocally(draft)) return;
        serverQueue = draft;
        window.clearTimeout(syncTimer);
        if (immediateSync) syncLatestDraft();
        else syncTimer = window.setTimeout(syncLatestDraft, 400);
    };

    const deleteServerDraft = async () => {
        if (!navigator.onLine) return false;
        try {
            const response = await fetch(form.dataset.draftDeleteUrl, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || '' },
            });
            if (!response.ok) return false;
            localStorage.removeItem(clearKey);
            return true;
        } catch {
            return false;
        }
    };

    const clearForm = () => {
        form.reset();
        grade.value = '';
        method.value = '';
        subject.value = '';
        form.elements.package_name.value = '';
        form.elements.target_learning.value = '';
        form.elements.target_capacity.value = '';
        form.elements.default_location.value = '';
        form.elements.zoom_meeting_url.value = '';
        form.querySelectorAll('[name="availability_slot_ids[]"]').forEach((input) => { input.checked = false; });
        setChoice('.grade-choice', '', 'gradeChoice');
        setChoice('.subject-choice', '', 'subjectChoice');
        setChoice('.method-choice', '', 'methodChoice', displayMethod);
        setChoice('.package-choice', packageType.value, 'packageChoice');
        refreshSubjects('');
        updateMethodFields();
        step = 1;
        render(false);
    };

    const restoreDraft = (draft) => {
        if (!draft || draft.version !== 1 || !draft.values || typeof draft.values !== 'object') return false;
        const values = draft.values;
        Object.entries(values).forEach(([name, value]) => {
            const controls = [...form.elements].filter((element) => element.name === name);
            if (!controls.length) return;
            controls.forEach((element) => {
                if (element.type === 'checkbox') {
                    element.checked = Array.isArray(value) && value.includes(element.value);
                } else if (!['button', 'submit', 'file', 'radio'].includes(element.type)) {
                    element.value = value ?? '';
                }
            });
        });

        const gradeButton = [...document.querySelectorAll('[data-grade-choice]')]
            .find((button) => button.dataset.gradeChoice.toLowerCase() === (grade.value || '').toLowerCase());
        gradeButton?.click();
        const subjectButton = [...document.querySelectorAll('[data-subject-choice]')]
            .find((button) => button.dataset.subjectChoice.toLowerCase() === (subject.value || '').toLowerCase()
                && button.dataset.subjectLevel.toLowerCase() === (grade.value || '').toLowerCase());
        subjectButton?.click();
        [...document.querySelectorAll('[data-method-choice]')]
            .find((button) => canonicalMethod(button.dataset.methodChoice) === canonicalMethod(method.value))?.click();
        [...document.querySelectorAll('[data-package-choice]')]
            .find((button) => button.dataset.packageChoice === packageType.value)?.click();
        const ids = values['availability_slot_ids[]'] || values.availability_slot_ids || [];
        form.querySelectorAll('[name="availability_slot_ids[]"]').forEach((input) => {
            input.checked = Array.isArray(ids) && ids.includes(input.value);
        });
        step = Number(draft.step) >= 1 && Number(draft.step) <= 4 ? Number(draft.step) : 1;
        return true;
    };

    document.querySelectorAll('[data-subject-choice]').forEach((button) => button.addEventListener('click', () => {
        subject.value = button.dataset.subjectChoice;
        setChoice('.subject-choice', subject.value, 'subjectChoice');
        persistDraft();
    }));
    document.querySelectorAll('[data-grade-choice]').forEach((button) => button.addEventListener('click', () => {
        grade.value = button.dataset.gradeChoice;
        setChoice('.grade-choice', grade.value, 'gradeChoice');
        refreshSubjects(grade.value);
        persistDraft();
    }));
    document.querySelectorAll('[data-method-choice]').forEach((button) => button.addEventListener('click', () => {
        method.value = canonicalMethod(button.dataset.methodChoice);
        setChoice('.method-choice', method.value, 'methodChoice', canonicalMethod);
        updateMethodFields();
        persistDraft();
    }));
    document.querySelectorAll('[data-package-choice]').forEach((button) => button.addEventListener('click', () => {
        packageType.value = button.dataset.packageChoice;
        setChoice('.package-choice', packageType.value, 'packageChoice');
        document.getElementById('package-estimate').textContent = `Estimasi nilai paket per siswa: ${formatPrice(packageTotal())}`;
        persistDraft();
    }));

    form.addEventListener('input', () => persistDraft());
    form.addEventListener('change', () => persistDraft());
    form.addEventListener('submit', () => persistDraft(true));

    document.getElementById('wizard-next').addEventListener('click', () => {
        if (step === 1 && !document.querySelector('[data-availability-summary] [data-slot-summary]')) {
            return alert('Tambahkan minimal satu slot aktif di Master Availability sebelum melanjutkan.');
        }
        if (step === 2 && (!grade.value || !subject.value.trim() || !form.elements.package_name.value.trim() || !form.elements.target_learning.value.trim())) {
            return alert('Lengkapi jenjang, mata pelajaran, identitas kelas, dan target capaian.');
        }
        if (step === 2 && (!method.value || !packageType.value || !form.elements.target_capacity.value || !price())) {
            return alert('Pilih moda belajar, kuota, paket, serta tarif yang tersedia.');
        }
        if (step === 2 && method.value === 'home_visit' && !form.elements.default_location.value.trim()) {
            return alert('Isi area atau catatan lokasi Home Visit.');
        }
        if (step === 3 && !selectedSlots().length) return alert('Pilih minimal satu slot jadwal.');
        step = Math.min(4, step + 1);
        render();
        persistDraft(true);
    });
    document.getElementById('wizard-back').addEventListener('click', () => {
        step = Math.max(1, step - 1);
        render();
        persistDraft(true);
    });

    document.getElementById('reset-class-draft').addEventListener('click', async () => {
        if (!window.confirm('Hapus semua isian draft pembuatan kelas ini?')) return;
        try {
            localStorage.removeItem(draftKey);
            localStorage.setItem(clearKey, String(Date.now()));
        } catch {}
        serverQueue = null;
        clearForm();
        if (await deleteServerDraft()) {
            status.textContent = 'Draft dihapus dari perangkat dan server.';
            status.className = 'text-xs font-semibold text-emerald-700';
        } else {
            status.textContent = 'Draft dihapus dari perangkat; penghapusan server menunggu koneksi.';
            status.className = 'text-xs font-semibold text-amber-700';
        }
    });

    window.addEventListener('online', async () => {
        if (localStorage.getItem(clearKey)) {
            if (await deleteServerDraft()) status.textContent = 'Draft dihapus dari perangkat dan server.';
            return;
        }
        serverQueue = makeDraft();
        syncLatestDraft();
    });

    const boot = tariffData;
    let localDraft = null;
    let clearPending = false;
    try {
        clearPending = Boolean(localStorage.getItem(clearKey));
        localDraft = JSON.parse(localStorage.getItem(draftKey) || 'null');
    } catch {}

    if (clearPending) {
        clearForm();
        deleteServerDraft();
    } else {
        const serverDraft = boot.serverDraft;
        const restored = localDraft && Number(localDraft.savedAt) > Number(serverDraft?.savedAt || 0)
            ? restoreDraft(localDraft)
            : restoreDraft(serverDraft);
        if (!restored && boot.oldValues) {
            const old = boot.oldValues;
            if (old.grade_level) document.querySelector(`[data-grade-choice="${CSS.escape(old.grade_level)}"]`)?.click();
            if (old.subject_name) [...document.querySelectorAll('[data-subject-choice]')]
                .find((button) => button.dataset.subjectChoice === old.subject_name && button.dataset.subjectLevel === grade.value)?.click();
            if (old.learning_method) document.querySelector(`[data-method-choice="${displayMethod(old.learning_method)}"]`)?.click();
            if (old.package_type) document.querySelector(`[data-package-choice="${old.package_type}"]`)?.click();
        }
        if (boot.stepOverride) step = Number(boot.initialStep) || 1;
        if (restored) {
            status.textContent = 'Draft terakhir dipulihkan.';
            status.className = 'text-xs font-semibold text-cyan-800';
        }
    }

    updateMethodFields();
    document.getElementById('tariff-display').textContent = `${formatPrice(price())} / sesi / siswa`;
    document.getElementById('package-estimate').textContent = `Estimasi nilai paket per siswa: ${formatPrice(packageTotal())}`;
    render(false);
    if (!clearPending && (localDraft || boot.serverDraft)) persistDraft(true);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTutorClassWizard, { once: true });
} else {
    bootTutorClassWizard();
}
