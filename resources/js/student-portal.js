const jsonRequest = async (url, options = {}) => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = {
        Accept: 'application/json',
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        ...options.headers,
    };
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers,
    });
    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(payload.message || 'Permintaan belum dapat diproses.');
        error.errors = payload.errors || {};
        throw error;
    }

    return payload;
};

const readableError = (error) => {
    const fieldError = Object.values(error.errors || {}).flat()[0];
    return fieldError || error.message || 'Terjadi kesalahan. Silakan coba kembali.';
};

const studentCatalog = () => ({
    educationLevel: 'SMP',
    learningMethod: 'online_zoom',
    search: '',
    tutors: [],
    loading: false,
    error: '',
    initialized: false,

    init() {
        this.educationLevel = this.$el.dataset.educationLevel || this.educationLevel;
        this.load();
    },

    async load() {
        this.loading = true;
        this.error = '';
        const query = new URLSearchParams({
            education_level: this.educationLevel,
            learning_method: this.learningMethod,
            ...(this.search.trim() ? { search: this.search.trim() } : {}),
        });

        try {
            const result = await jsonRequest(`${this.$el.dataset.catalogUrl}?${query}`);
            this.tutors = result.data || [];
        } catch (error) {
            this.tutors = [];
            this.error = readableError(error);
        } finally {
            this.loading = false;
            this.initialized = true;
        }
    },

    bookingUrl(tutor, pack) {
        const target = new URL(this.$el.dataset.bookingUrl, window.location.origin);
        target.searchParams.set('education_level', this.educationLevel);
        target.searchParams.set('learning_method', this.learningMethod);
        target.searchParams.set('teacher_id', tutor.teacher_id);
        target.searchParams.set('package_id', pack.package_id);
        return target.toString();
    },

    rupiah(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
    },
});

const studentDashboard = () => ({
    data: null,
    loading: true,
    error: '',

    async init() {
        try {
            const result = await jsonRequest(this.$el.dataset.dashboardUrl);
            this.data = result.data || {};
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
    },

    get sessionCount() {
        return this.data?.weekly_agenda?.length || (this.data?.upcoming_session ? 1 : 0);
    },
});

const bookingWizard = () => ({
    step: 1,
    educationLevel: 'SMP',
    subject: 'Matematika',
    method: 'online_zoom',
    selectedTutorId: null,
    selectedPackageId: null,
    sessionPackage: 4,
    squadOption: 'solo',
    squadCode: '',
    targetCapacity: 3,
    selectedScheduleId: null,
    paymentMethod: 'xendit',
    searchQuery: '',
    tutors: [],
    slots: [],
    pricing: null,
    order: null,
    loading: false,
    slotsLoading: false,
    pricingLoading: false,
    submitting: false,
    error: '',
    initialized: false,
    slotNotice: '',
    levels: [
        { id: 'SD', tag: 'DASAR', title: 'Sekolah Dasar (SD)', desc: 'Kelas 1–6 · Calistung dan tematik' },
        { id: 'SMP', tag: 'MENENGAH PERTAMA', title: 'Sekolah Menengah Pertama (SMP)', desc: 'Kelas 7–9 · Kurikulum sekolah' },
        { id: 'SMA', tag: 'MENENGAH ATAS', title: 'Sekolah Menengah Atas (SMA)', desc: 'Kelas 10–12 · Persiapan ujian' },
        { id: 'UMUM', tag: 'PROFESIONAL', title: 'Umum', desc: 'Bahasa, keterampilan, dan karier' },
    ],
    subjects: [],
    isDrawerOpen: false,
    isPaymentOpen: false,
    isProfileOpen: false,
    profileTutor: null,
    squadMode: 'create',
    showLevelChoices: false,
    weekDays: [
        { key: 'Monday', label: 'Sen' }, { key: 'Tuesday', label: 'Sel' }, { key: 'Wednesday', label: 'Rab' },
        { key: 'Thursday', label: 'Kam' }, { key: 'Friday', label: 'Jum' }, { key: 'Saturday', label: 'Sab' },
        { key: 'Sunday', label: 'Min' },
    ],
    calendarMode: 'month',
    calendarMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
    selectedCalendarDate: '',

    get selectedTutor() {
        return this.tutors.find((tutor) => tutor.teacher_id === this.selectedTutorId) || null;
    },

    get selectedPackage() {
        return this.selectedTutor?.packages?.find((pack) => pack.package_id === this.selectedPackageId) || null;
    },

    get selectedSchedule() {
        return this.slots.find((slot) => slot.slot_id === this.selectedScheduleId) || null;
    },

    get format() {
        return { solo: 'solo', create_squad: 'host', join_squad: 'joiner' }[this.squadOption] || 'solo';
    },

    get maxCapacity() {
        return this.method === 'online_zoom' ? 20 : 5;
    },

    async init() {
        const query = new URLSearchParams(window.location.search);
        this.educationLevel = query.get('education_level') || this.$el.dataset.educationLevel || this.educationLevel;
        this.method = query.get('learning_method') || this.method;
        await this.loadSubjects();
        const preferredTutorId = query.get('teacher_id');
        if (preferredTutorId && this.subject) {
            await this.loadTutors(preferredTutorId, query.get('package_id'));
            if (this.selectedTutorId) this.openCheckoutDrawer(this.selectedTutorId);
        }
    },

    async loadSubjects() {
        const url = new URL(this.$el.dataset.subjectsUrl, window.location.origin);
        url.searchParams.set('education_level', this.educationLevel);
        url.searchParams.set('learning_method', this.method);
        try {
            const result = await jsonRequest(url);
            this.subjects = (result.data || []).map((name) => ({ name, icon: this.educationLevel === 'UMUM' ? '✦' : '◈', sub: this.educationLevel === 'UMUM' ? 'Modul keahlian' : 'Mata pelajaran akademik' }));
            if (!this.subjects.some((item) => item.name === this.subject)) this.subject = this.subjects[0]?.name || '';
            return this.subjects;
        } catch (error) {
            this.subjects = [];
            this.subject = '';
            this.error = readableError(error);
            return [];
        }
    },

    async changeEducationLevel(level) {
        this.educationLevel = level;
        this.selectedTutorId = null;
        this.selectedPackageId = null;
        this.tutors = [];
        this.slots = [];
        this.pricing = null;
        await this.loadSubjects();
    },

    async changeMethod(method) {
        this.method = method;
        this.selectedTutorId = null;
        this.selectedPackageId = null;
        this.selectedScheduleId = null;
        this.tutors = [];
        this.slots = [];
        this.pricing = null;
        await this.loadSubjects();
    },

    async nextStep(targetStep) {
        this.error = '';

        if (targetStep === 2) {
            if (!this.educationLevel || !this.subject || !this.method) {
                this.error = 'Pilih jenjang, mata pelajaran, dan metode belajar terlebih dahulu.';
                return;
            }
            const query = new URLSearchParams(window.location.search);
            const loaded = await this.loadTutors(query.get('teacher_id'), query.get('package_id'));
            if (loaded) this.step = targetStep;
            return;
        }

        if (targetStep === 3 && (!this.selectedTutorId || !this.selectedPackageId)) {
            this.error = 'Pilih tutor dan paket belajar terlebih dahulu.';
            return;
        }

        if (targetStep === 4) {
            if (!this.selectedTutorId || !this.selectedPackageId) {
                this.error = 'Pilih tutor dan paket belajar terlebih dahulu.';
                return;
            }
            if (this.squadOption === 'join_squad' && !this.squadCode.trim()) {
                this.error = 'Masukkan kode kelompok belajar terlebih dahulu.';
                return;
            }
            if (!(await this.calculatePricing())) return;
            if (!(await this.loadSlots())) return;
        }

        if (targetStep === 5) {
            if (!this.selectedScheduleId) {
                this.error = 'Pilih jadwal yang tersedia terlebih dahulu.';
                return;
            }
            if (!(await this.calculatePricing())) return;
        }

        this.step = targetStep;
    },

    async loadTutors(preferredTutorId = null, preferredPackageId = null) {
        this.loading = true;
        this.error = '';
        const query = new URLSearchParams({
            education_level: this.educationLevel,
            learning_method: this.method,
            subject: this.subject,
            ...(this.searchQuery.trim() ? { search: this.searchQuery.trim() } : {}),
        });

        try {
            const catalogUrl = this.$el?.getAttribute('data-catalog-url')
                || document.querySelector('[data-catalog-url]')?.getAttribute('data-catalog-url');
            if (!catalogUrl) throw new Error('Alamat katalog belum tersedia. Muat ulang halaman lalu coba kembali.');

            const endpoint = new URL(catalogUrl, window.location.origin);
            endpoint.search = query.toString();
            const result = await jsonRequest(endpoint);
            this.tutors = result.data || [];

            const requestedTutor = this.tutors.find((tutor) => tutor.teacher_id === preferredTutorId);
            const currentTutor = this.tutors.find((tutor) => tutor.teacher_id === this.selectedTutorId);
            const selectedTutor = requestedTutor || currentTutor || this.tutors[0] || null;
            this.selectedTutorId = selectedTutor?.teacher_id || null;

            const requestedPackage = selectedTutor?.packages?.find((pack) => pack.package_id === preferredPackageId);
            const currentPackage = selectedTutor?.packages?.find((pack) => pack.package_id === this.selectedPackageId);
            this.selectedPackageId = (requestedPackage || currentPackage || selectedTutor?.packages?.[0])?.package_id || null;
            this.selectedScheduleId = null;
            this.slots = [];
            this.pricing = null;
            return true;
        } catch (error) {
            this.tutors = [];
            this.selectedTutorId = null;
            this.selectedPackageId = null;
            this.error = readableError(error);
            return false;
        } finally {
            this.loading = false;
            this.initialized = true;
        }
    },

    selectTutor(tutor) {
        this.selectedTutorId = tutor.teacher_id;
        this.selectedPackageId = tutor.packages?.[0]?.package_id || null;
        this.selectedScheduleId = null;
        this.slots = [];
        this.pricing = null;
    },

    selectPackage(tutor, pack) {
        this.selectedTutorId = tutor.teacher_id;
        this.selectedPackageId = pack.package_id;
        this.selectedScheduleId = null;
        this.slots = [];
        this.pricing = null;
    },

    async openCheckoutDrawer(tutorId) {
        const tutor = this.tutors.find((item) => item.teacher_id === tutorId);
        if (!tutor) return;
        this.selectTutor(tutor);
        this.selectedPackageId = tutor.packages?.[0]?.package_id || null;
        this.paymentMethod = 'qris';
        this.slotNotice = '';
        this.slots = (tutor.availability_slots || []).filter((slot) => slot.is_available);
        this.step = 3;
        this.isDrawerOpen = false;
        this.isPaymentOpen = false;
        this.initializeBookingCalendar();
        if (this.slots.length === 0) await this.loadSlots();
        if (this.selectedPackageId) this.calculatePricing();
    },

    dayIndex(day) {
        const names = {
            sunday: 0, sun: 0, minggu: 0, min: 0,
            monday: 1, mon: 1, senin: 1, sen: 1,
            tuesday: 2, tue: 2, selasa: 2, sel: 2,
            wednesday: 3, wed: 3, rabu: 3, rab: 3,
            thursday: 4, thu: 4, kamis: 4, kam: 4,
            friday: 5, fri: 5, jumat: 5, jum: 5,
            saturday: 6, sat: 6, sabtu: 6, sab: 6,
        };
        return names[String(day || '').toLowerCase()] ?? -1;
    },

    dateKey(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },

    dateAvailable(date) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const candidate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        return candidate > today && this.slots.some((slot) =>
            this.dayIndex(slot.day_of_week) === candidate.getDay() && this.canSelectSlot(slot)
        );
    },

    calendarDays() {
        const year = this.calendarMonth.getFullYear();
        const month = this.calendarMonth.getMonth();
        const firstDayOffset = (new Date(year, month, 1).getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        return [
            ...Array(firstDayOffset).fill(null),
            ...Array.from({ length: daysInMonth }, (_, index) => {
                const date = new Date(year, month, index + 1);
                return { key: this.dateKey(date), day: index + 1, available: this.dateAvailable(date) };
            }),
        ];
    },

    initializeBookingCalendar() {
        this.selectedScheduleId = null;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        for (let offset = 0; offset < 90; offset++) {
            const date = new Date(today);
            date.setDate(today.getDate() + offset);
            if (this.dateAvailable(date)) {
                this.selectedCalendarDate = this.dateKey(date);
                this.calendarMonth = new Date(date.getFullYear(), date.getMonth(), 1);
                return;
            }
        }
        this.selectedCalendarDate = '';
        this.calendarMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    },

    moveCalendarMonth(amount) {
        this.calendarMonth = new Date(this.calendarMonth.getFullYear(), this.calendarMonth.getMonth() + amount, 1);
    },

    moveCalendarDate(direction) {
        const base = this.selectedCalendarDate
            ? (() => {
                const [year, month, day] = this.selectedCalendarDate.split('-').map(Number);
                return new Date(year, month - 1, day);
            })()
            : new Date();
        for (let offset = 1; offset <= 90; offset++) {
            const date = new Date(base);
            date.setDate(base.getDate() + (offset * direction));
            if (this.dateAvailable(date)) {
                this.selectCalendarDate(this.dateKey(date));
                this.calendarMonth = new Date(date.getFullYear(), date.getMonth(), 1);
                return;
            }
        }
    },

    selectCalendarDate(dateKey) {
        this.selectedCalendarDate = dateKey;
        this.selectedScheduleId = null;
    },

    get selectedCalendarDateLabel() {
        if (!this.selectedCalendarDate) return 'Pilih tanggal yang tersedia';
        const [year, month, day] = this.selectedCalendarDate.split('-').map(Number);
        return new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long' })
            .format(new Date(year, month - 1, day));
    },

    get slotsForSelectedDate() {
        if (!this.selectedCalendarDate) return [];
        const [year, month, day] = this.selectedCalendarDate.split('-').map(Number);
        const weekday = new Date(year, month - 1, day).getDay();
        return this.slots.filter((slot) => this.dayIndex(slot.day_of_week) === weekday && this.canSelectSlot(slot));
    },

    openPayment() {
        this.error = '';
        if (!this.selectedPackageId) {
            this.error = 'Pilih paket belajar terlebih dahulu.';
            return;
        }
        if (!this.selectedScheduleId) {
            this.error = 'Pilih jadwal yang tersedia terlebih dahulu.';
            return;
        }
        this.isPaymentOpen = true;
        if (!this.pricing && !this.pricingLoading) this.calculatePricing();
    },

    closePayment() {
        this.isPaymentOpen = false;
    },

    closeCheckoutDrawer() {
        this.isDrawerOpen = false;
    },

    openTutorProfile(tutor) {
        this.profileTutor = tutor;
        this.isProfileOpen = true;
    },

    bookingPayload() {
        const payload = {
            teacher_id: this.selectedTutorId,
            package_id: this.selectedPackageId,
            learning_method: this.method,
            session_package: Number(this.sessionPackage),
            format: this.format,
            payment_method: this.paymentMethod,
        };
        if (this.squadOption === 'create_squad') payload.target_capacity = Number(this.targetCapacity);
        if (this.squadOption === 'join_squad') payload.squad_code = this.squadCode.trim();
        return payload;
    },

    async calculatePricing() {
        if (!this.selectedTutorId || !this.selectedPackageId) {
            this.error = 'Pilih tutor dan paket belajar terlebih dahulu.';
            return false;
        }

        this.pricingLoading = true;
        this.error = '';
        try {
            const result = await jsonRequest(this.$el.dataset.calculateUrl, {
                method: 'POST',
                body: JSON.stringify(this.bookingPayload()),
            });
            this.pricing = result.data;
            return true;
        } catch (error) {
            this.pricing = null;
            this.error = readableError(error);
            return false;
        } finally {
            this.pricingLoading = false;
        }
    },

    async loadSlots() {
        if (!this.selectedTutorId) return false;
        this.slotsLoading = true;
        this.error = '';
        const template = this.$el.dataset.slotsUrl;

        try {
            const endpoint = new URL(template.replace('__TEACHER_ID__', encodeURIComponent(this.selectedTutorId)), window.location.origin);
            endpoint.searchParams.set('learning_method', this.method);
            const result = await jsonRequest(endpoint);
            this.slots = result.data || [];
            this.selectedScheduleId = null;
            this.slotNotice = '';
            if (!this.slots.some((slot) => this.canSelectSlot(slot))) {
                this.error = 'Belum ada jadwal yang tersedia untuk pilihan ini.';
                return false;
            }
            return true;
        } catch (error) {
            const catalogSlots = (this.selectedTutor?.availability_slots || []).filter((slot) => slot.is_available);
            this.slots = catalogSlots;
            this.selectedScheduleId = null;
            if (catalogSlots.length > 0) {
                this.slotNotice = 'Jadwal ini berasal dari katalog. Ketersediaannya akan diperiksa kembali saat pesanan dibuat.';
                this.error = '';
                return true;
            }
            this.error = readableError(error);
            return false;
        } finally {
            this.slotsLoading = false;
        }
    },

    canSelectSlot(slot) {
        return slot.is_available || (
            this.squadOption === 'join_squad'
            && (slot.available_squad_codes || []).includes(this.squadCode.trim())
        );
    },

    async placeOrder() {
        if (!this.selectedScheduleId) {
            this.error = 'Pilih jadwal yang tersedia terlebih dahulu.';
            return;
        }
        if (!this.pricing && !(await this.calculatePricing())) return;

        this.submitting = true;
        this.error = '';
        try {
            const result = await jsonRequest(this.$el.dataset.checkoutUrl, {
                method: 'POST',
                body: JSON.stringify({ ...this.bookingPayload(), selected_slot_id: this.selectedScheduleId }),
            });
            this.order = result.data;
            this.isPaymentOpen = false;
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.submitting = false;
        }
    },

    dayLabel(day) {
        return this.weekDays.find((item) => item.key === day)?.label || day;
    },

    rupiah(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
    },
});

const studentSchedule = () => ({
    sessions: [],
    loading: true,
    error: '',

    async init() {
        try {
            const result = await jsonRequest(this.$el.dataset.dashboardUrl);
            const data = result.data || {};
            const sessions = [...(data.weekly_agenda || [])];
            if (data.upcoming_session) sessions.push(data.upcoming_session);
            this.sessions = [...new Map(sessions.map((session) => [session.session_id, session])).values()]
                .sort((a, b) => new Date(a.scheduled_at || 0) - new Date(b.scheduled_at || 0));
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
    },

    date(value) {
        if (!value) return 'Waktu belum ditentukan';
        return new Intl.DateTimeFormat('id-ID', { dateStyle: 'full', timeStyle: 'short' }).format(new Date(value));
    },
});

const studentClassroom = () => ({
    sessions: [],
    selectedSession: null,
    room: null,
    exam: null,
    examType: null,
    preTestScore: null,
    remainingSeconds: null,
    timerId: null,
    answers: {},
    result: null,
    loading: true,
    submitting: false,
    error: '',
    notice: '',
    roomRefreshId: null,

    async init() {
        const query = new URLSearchParams(window.location.search);
        this.examType = query.get('exam_type');
        this.preTestScore = this.$el.dataset.pretestScore === '' ? null : Number(this.$el.dataset.pretestScore);
        const sessionId = query.get('session_id') || this.$el.dataset.sessionId;

        try {
            const result = await jsonRequest(this.$el.dataset.dashboardUrl);
            const data = result.data || {};
            const sessions = [...(data.weekly_agenda || [])];
            if (data.upcoming_session) sessions.push(data.upcoming_session);
            this.sessions = [...new Map(sessions.map((session) => [session.session_id, session])).values()]
                .sort((a, b) => new Date(a.scheduled_at || 0) - new Date(b.scheduled_at || 0));

            if (sessionId) {
                this.selectedSession = this.sessions.find((session) => session.session_id === sessionId) || { session_id: sessionId };
                await this.loadRoom();
                if (this.examType && ['pre_test', 'post_test'].includes(this.examType)) await this.loadExam();
            }
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
        this.roomRefreshId = window.setInterval(() => this.refreshRoomStatus(), 10000);
    },

    destroy() {
        this.stopTimer();
        if (this.roomRefreshId !== null) window.clearInterval(this.roomRefreshId);
    },

    sessionUrl(sessionId, options = {}) {
        const classroomUrl = this.$el?.dataset?.classroomUrl;
        const baseUrl = classroomUrl && classroomUrl !== 'undefined'
            ? classroomUrl
            : '/siswa/classroom';
        const target = new URL(baseUrl, window.location.origin);
        target.searchParams.set('session_id', sessionId);
        if (options.examType) target.searchParams.set('exam_type', options.examType);
        return target.toString();
    },

    async selectSession(session) {
        this.stopTimer();
        this.remainingSeconds = null;
        this.selectedSession = session;
        this.exam = null;
        this.result = null;
        this.examType = null;
        this.notice = '';
        window.history.replaceState({}, '', this.sessionUrl(session.session_id));
        await this.loadRoom();
    },

    async loadRoom() {
        if (!this.selectedSession) return;
        this.loading = true;
        this.error = '';
        const url = this.$el.dataset.roomAccessUrl.replace('__SESSION_ID__', encodeURIComponent(this.selectedSession.session_id));
        try {
            const result = await jsonRequest(url);
            this.room = result.data;
            this.preTestScore = result.data?.pretest_score === null || result.data?.pretest_score === undefined
                ? null
                : Number(result.data.pretest_score);
        } catch (error) {
            this.room = null;
            this.preTestScore = null;
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
    },

    async refreshRoomStatus() {
        if (!this.selectedSession) return;
        const url = this.$el.dataset.roomAccessUrl.replace('__SESSION_ID__', encodeURIComponent(this.selectedSession.session_id));
        try {
            const result = await jsonRequest(url);
            this.room = result.data;
            this.preTestScore = result.data?.pretest_score === null || result.data?.pretest_score === undefined
                ? null
                : Number(result.data.pretest_score);
        } catch (error) {
            // Keep the last known room state if a brief network error occurs.
        }
    },

    async openExam(type) {
        if (!this.selectedSession) return;
        this.stopTimer();
        this.examType = type;
        this.result = null;
        this.notice = '';
        this.exam = null;
        this.answers = {};
        window.history.replaceState({}, '', this.sessionUrl(this.selectedSession.session_id, { examType: type }));
        await this.loadExam();
    },

    async loadExam() {
        const url = this.$el.dataset.examUrl
            .replace('__SESSION_ID__', encodeURIComponent(this.selectedSession.session_id))
            .replace('__EXAM_TYPE__', encodeURIComponent(this.examType));
        this.loading = true;
        this.error = '';
        try {
            const result = await jsonRequest(url);
            this.exam = result.data;
            this.$nextTick(() => document.getElementById('classroom-exam-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            if (this.exam.already_submitted) {
                this.notice = `Tes sudah dikumpulkan. Nilai: ${this.exam.score ?? '—'}.`;
            } else if (this.examType === 'pre_test') {
                this.startTimer(Number(this.exam.duration_minutes || 5));
            }
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
    },

    startTimer(durationMinutes) {
        this.stopTimer();
        this.remainingSeconds = durationMinutes * 60;
        this.timerId = window.setInterval(() => {
            if (this.remainingSeconds <= 1) {
                this.remainingSeconds = 0;
                this.stopTimer();
                this.submitExam(true);
                return;
            }

            this.remainingSeconds -= 1;
        }, 1000);
    },

    stopTimer() {
        if (this.timerId !== null) window.clearInterval(this.timerId);
        this.timerId = null;
    },

    get timerLabel() {
        if (this.remainingSeconds === null) return '';
        const minutes = Math.floor(this.remainingSeconds / 60).toString().padStart(2, '0');
        const seconds = (this.remainingSeconds % 60).toString().padStart(2, '0');
        return `${minutes}:${seconds}`;
    },

    async submitExam(autoSubmit = false) {
        if (this.submitting || !this.exam || this.exam.already_submitted) return;
        if (!autoSubmit && this.exam.questions.some((question) => !this.answers[question.question_id])) {
            this.error = 'Pilih jawaban untuk semua soal sebelum mengirim.';
            return;
        }

        this.submitting = true;
        this.error = '';
        const url = this.$el.dataset.submitExamUrl.replace('__SESSION_ID__', encodeURIComponent(this.selectedSession.session_id));
        try {
            const result = await jsonRequest(url, {
                method: 'POST',
                body: JSON.stringify({
                    exam_type: this.examType,
                    answers: this.exam.questions
                        .map((question) => ({
                        question_id: question.question_id,
                        answer: this.answers[question.question_id] || '',
                    })),
                }),
            });
            this.result = result.data;
            this.stopTimer();
            this.remainingSeconds = null;
            this.exam.already_submitted = true;
            this.exam.score = result.data.score;
            if (this.examType === 'pre_test') this.preTestScore = result.data.score;
            this.notice = `Tes berhasil dikirim. Nilai: ${result.data.score}.`;
            await this.loadRoom();
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.stopTimer();
            this.submitting = false;
        }
    },

    date(value) {
        if (!value) return 'Waktu belum ditentukan';
        return new Intl.DateTimeFormat('id-ID', { dateStyle: 'full', timeStyle: 'short' }).format(new Date(value));
    },
});

const studentReport = () => ({
    data: null,
    loading: true,
    error: '',

    async init() {
        try {
            const result = await jsonRequest(this.$el.dataset.dashboardUrl);
            this.data = result.data || {};
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.loading = false;
        }
    },

    percent(value) {
        return `${Math.max(0, Math.min(100, Number(value || 0)))}%`;
    },
});

const studentParentMode = () => ({
    pin: '',
    data: null,
    loading: false,
    error: '',

    async verify() {
        this.loading = true;
        this.error = '';
        this.data = null;
        try {
            // Keep a valid endpoint even if an old/cached Blade view omits its data attribute.
            const verifyPinUrl = this.$el.dataset.verifyPinUrl || '/api/student/parent-mode/verify-pin';
            const result = await jsonRequest(verifyPinUrl, {
                method: 'POST',
                body: JSON.stringify({ pin: this.pin }),
            });
            this.data = result.data;
        } catch (error) {
            this.error = readableError(error);
        } finally {
            this.pin = '';
            this.loading = false;
        }
    },
});

export default function registerStudentPortal(Alpine) {
    Alpine.data('studentDashboard', studentDashboard);
    Alpine.data('studentCatalog', studentCatalog);
    Alpine.data('bookingWizard', bookingWizard);
    Alpine.data('studentSchedule', studentSchedule);
    Alpine.data('studentClassroom', studentClassroom);
    Alpine.data('studentReport', studentReport);
    Alpine.data('studentParentMode', studentParentMode);
}
