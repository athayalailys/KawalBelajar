<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Squad;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TutorClassDraft;
use App\Models\TutorPackageRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TutorClassesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        $tutor = Teacher::query()
            ->with('user')
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        return view('pages.tutor.kelola-kelas', $this->pageData($tutor) + [
            'showCreateModal' => false,
            'viewMode' => in_array($request->query('view'), ['cards', 'table'], true) ? $request->query('view') : 'cards',
            'classFilter' => in_array($request->query('filter'), ['all', 'active', 'draft'], true) ? $request->query('filter') : 'all',
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);
        $tutor = Teacher::query()->with('user')->where('user_id', $user->user_id)->firstOrFail();

        // Master tariff berlaku berdasarkan jenjang pembelajar dan moda, bukan level tutor.
        // Kolom teacher_level pada data lama kadang menyimpan jenjang, jadi grade dibaca
        // dari kolom resmi terlebih dahulu lalu memakai relasi paket sebagai fallback.
        $tariffs = [];
        foreach (MasterTariff::query()->with('package')->orderByDesc('created_at')->get() as $tariff) {
            $grade = $this->normalizeGrade($tariff->grade_level ?: $tariff->package?->grade_level ?: $tariff->teacher_level);
            $method = $this->normalizeMethod($tariff->learning_method ?: $tariff->method ?: $tariff->package?->learning_method);
            $rate = (float) ($tariff->category_rate ?: $tariff->base_rate ?: 0);

            if ($grade && $method && $rate > 0) {
                $tariffs[$grade.'|'.$method] ??= $rate;
            }
        }
        foreach ((array) config('booking.default_tariffs', []) as $grade => $rates) {
            foreach ($rates as $method => $rate) {
                $tariffs[$this->normalizeGrade($grade).'|'.$this->normalizeMethod($method)] ??= (float) $rate;
            }
        }

        $savedDraft = TutorClassDraft::query()->where('teacher_id', $tutor->teacher_id)->first();
        $serverDraft = $savedDraft?->draft_data;
        if (is_array($serverDraft)) {
            $serverDraft['savedAt'] = ($savedDraft->updated_at?->getTimestamp() ?? 0) * 1000;
        }

        return view('pages.tutor.create-class', [
            'tutor' => $tutor,
            'initialStep' => max(1, min(4, (int) $request->query('step', 1))),
            'serverDraft' => $serverDraft,
            'approvedLevels' => $tutor->approved_levels ?? $tutor->verified_levels ?? [],
            'subjectsByLevel' => $this->subjectOptions(),
            'defaultLocation' => $tutor->default_location,
            'tariffs' => $tariffs,
            'days' => ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'],
            'availabilitySlots' => $tutor->availabilitySlots()->where('is_active', true)->where('is_available', true)->orderBy('day_of_week')->orderBy('start_time')->get(),
        ]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $tutor = $this->tutorFor($request);
        $validated = $request->validate([
            'draft' => ['required', 'array'],
            'draft.version' => ['required', 'integer', 'in:1'],
            'draft.step' => ['required', 'integer', 'between:1,4'],
            'draft.savedAt' => ['nullable', 'integer', 'min:0'],
            'draft.values' => ['required', 'array'],
            'draft.values.package_type' => ['nullable', Rule::in(['monthly', 'instant'])],
            'draft.values.grade_level' => ['nullable', 'string', 'max:20'],
            'draft.values.subject_name' => ['nullable', 'string', 'max:100'],
            'draft.values.package_name' => ['nullable', 'string', 'max:255'],
            'draft.values.target_learning' => ['nullable', 'string', 'max:3000'],
            'draft.values.learning_method' => ['nullable', Rule::in(['online', 'online_zoom', 'home_visit'])],
            'draft.values.target_capacity' => ['nullable', 'integer', 'between:1,20'],
            'draft.values.default_location' => ['nullable', 'string', 'max:1000'],
            'draft.values.zoom_meeting_url' => ['nullable', 'url', 'max:255'],
            'draft.values.availability_slot_ids' => ['nullable', 'array', 'max:64'],
            'draft.values.availability_slot_ids.*' => ['string', 'uuid'],
        ]);

        $values = collect($validated['draft']['values'])->only([
            'package_type', 'grade_level', 'subject_name', 'package_name', 'target_learning',
            'learning_method', 'target_capacity', 'default_location', 'zoom_meeting_url', 'availability_slot_ids',
        ])->all();
        $values['availability_slot_ids'] = array_values(array_unique($values['availability_slot_ids'] ?? []));
        $draft = [
            'version' => 1,
            'step' => (int) $validated['draft']['step'],
            'values' => $values,
            'savedAt' => now()->getTimestamp() * 1000,
        ];

        TutorClassDraft::query()->updateOrCreate(
            ['teacher_id' => $tutor->teacher_id],
            ['draft_data' => $draft],
        );

        return response()->json(['success' => true, 'savedAt' => $draft['savedAt']]);
    }

    public function deleteDraft(Request $request): JsonResponse
    {
        $tutor = $this->tutorFor($request);
        TutorClassDraft::query()->where('teacher_id', $tutor->teacher_id)->delete();

        return response()->json(['success' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->input('learning_method') === 'online') {
            $request->merge(['learning_method' => 'online_zoom']);
        }
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);
        $tutor = Teacher::query()->where('user_id', $user->user_id)->firstOrFail();
        $approvedLevels = array_map(fn ($level) => $this->normalizeGrade($level), $tutor->approved_levels ?: $tutor->verified_levels ?: []);

        $validated = $request->validate([
            'package_type' => ['required', Rule::in(['monthly', 'instant'])],
            'grade_level' => ['required', 'string', 'max:50', function ($attribute, $value, $fail) use ($approvedLevels) {
                if (!in_array($this->normalizeGrade($value), $approvedLevels, true)) {
                    $fail('Jenjang yang dipilih belum diverifikasi untuk akun tutor ini.');
                }
            }],
            'package_name' => ['required', 'string', 'max:255'],
            'subject_name' => ['required', 'string', 'max:100'],
            'target_learning' => ['required', 'string', 'max:3000'],
            'learning_method' => ['required', Rule::in(['online_zoom', 'home_visit'])],
            'zoom_meeting_url' => ['nullable', 'url', 'max:255'],
            'target_capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'default_location' => ['required_if:learning_method,home_visit', 'nullable', 'string', 'max:1000'],
            'availability_slot_ids' => ['required', 'array', 'min:1'],
            'availability_slot_ids.*' => [
                'required', 'uuid',
                Rule::exists('teacher_availability_slots', 'slot_id')->where(fn ($query) => $query
                    ->where('teacher_id', $tutor->teacher_id)
                    ->where('is_active', true)
                    ->where('is_available', true)),
            ],
        ]);
        $validated['grade_level'] = $this->normalizeGrade($validated['grade_level']);

        $approvedLevel = collect($approvedLevels)->first(fn ($level) => mb_strtolower(trim((string) $level)) === mb_strtolower(trim($validated['grade_level'])));
        if ($approvedLevel === null) {
            return back()->withInput()->withErrors(['grade_level' => 'Jenjang ini belum disetujui untuk akun tutor.']);
        }
        $validated['grade_level'] = mb_strtoupper(trim($validated['grade_level']));

        $configuredSubjects = Subject::query()->whereRaw('LOWER(TRIM(grade_level)) = ?', [mb_strtolower($validated['grade_level'])])->get();
        if ($configuredSubjects->isNotEmpty() && ! $configuredSubjects->contains(fn (Subject $subject) => $subject->is_active && mb_strtolower(trim($subject->name)) === mb_strtolower(trim($validated['subject_name'])))) {
            return back()->withInput()->withErrors(['subject_name' => 'Mata pelajaran ini tidak aktif untuk jenjang yang dipilih.']);
        }

        $maximumCapacity = $validated['learning_method'] === 'online_zoom' ? 20 : 5;
        if ($validated['target_capacity'] > $maximumCapacity) {
            return back()->withInput()->withErrors(['target_capacity' => "Kapasitas maksimal untuk metode ini adalah {$maximumCapacity} siswa."]);
        }

        $compatibleSlotCount = $tutor->availabilitySlots()
            ->whereIn('slot_id', $validated['availability_slot_ids'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(fn ($query) => $query->whereNull('learning_method')->orWhere('learning_method', $validated['learning_method']))
            ->distinct('slot_id')->count('slot_id');
        if ($compatibleSlotCount !== count(array_unique($validated['availability_slot_ids']))) {
            return back()->withInput()->withErrors(['availability_slot_ids' => 'Pastikan semua slot aktif dan cocok dengan moda belajar yang dipilih.']);
        }

        $slotUsedByAnotherClass = DB::table('module_package_availability_slots as package_slots')
            ->join('module_packages as packages', 'packages.package_id', '=', 'package_slots.package_id')
            ->where('packages.teacher_id', $tutor->teacher_id)
            ->whereIn('package_slots.slot_id', $validated['availability_slot_ids'])
            ->exists();
        if ($slotUsedByAnotherClass) {
            return back()->withInput()->withErrors([
                'availability_slot_ids' => 'Slot jam tersebut sudah terpakai di kelas lain. Pilih slot berbeda.',
            ]);
        }

        $tariff = MasterTariff::query()->with('package')->orderByDesc('created_at')->get()
            ->first(fn (MasterTariff $candidate) =>
                $this->normalizeGrade($candidate->grade_level ?: $candidate->package?->grade_level ?: $candidate->teacher_level) === $validated['grade_level']
                && $this->normalizeMethod($candidate->learning_method ?: $candidate->method ?: $candidate->package?->learning_method) === $this->normalizeMethod($validated['learning_method'])
                && (float) ($candidate->category_rate ?: $candidate->base_rate ?: 0) > 0
            );

        $basePrice = (float) ($tariff?->category_rate ?: $tariff?->base_rate ?: config(
            'booking.default_tariffs.'.$validated['grade_level'].'.'.$this->normalizeMethod($validated['learning_method']), 0
        ));
        if ($basePrice <= 0) {
            return back()->withInput()->withErrors(['tariff' => 'Tarif resmi untuk jenjang dan metode ini belum tersedia. Hubungi admin untuk mengaturnya.']);
        }
        $newPackageId = null;

        DB::transaction(function () use ($validated, $tutor, $basePrice, &$newPackageId) {
                $sessionPackage = $validated['package_type'] === 'monthly' ? 4 : 1;
                $package = ModulePackage::create([
                    'teacher_id' => $tutor->teacher_id,
                    'package_name' => $validated['package_name'],
                    'grade_level' => $validated['grade_level'],
                    'subject_name' => $validated['subject_name'],
                    'learning_method' => $validated['learning_method'],
                    'zoom_meeting_url' => $validated['zoom_meeting_url'] ?? null,
                    'home_visit_area' => $validated['learning_method'] === 'home_visit' ? ($validated['default_location'] ?? null) : null,
                    'max_capacity' => $validated['target_capacity'],
                    'description' => $validated['target_learning'],
                    'target_learning' => $validated['target_learning'],
                    'supported_session_packages' => (string) $sessionPackage,
                    'base_price' => $basePrice,
                    'is_active' => true,
                    'is_published' => false,
                    'is_open' => false,
                ]);
                $newPackageId = $package->package_id;

                TutorPackageRate::create([
                    'package_id' => $package->package_id,
                    'teacher_level' => $tutor->teacher_level,
                    'learning_method' => $validated['learning_method'],
                    'category_rate' => $basePrice,
                ]);

                DB::table('module_package_availability_slots')->insert(collect($validated['availability_slot_ids'])
                    ->unique()->map(fn ($slotId) => ['package_id' => $package->package_id, 'slot_id' => $slotId])->all());

                if ($validated['learning_method'] === 'home_visit') {
                    $tutor->update(['default_location' => mb_substr($validated['default_location'], 0, 150)]);
                }
        });

        TutorClassDraft::query()->updateOrCreate(
            ['teacher_id' => $tutor->teacher_id],
            [
                'package_id' => $newPackageId,
                'draft_data' => [
                    'version' => 1,
                    'step' => 4,
                    'savedAt' => now()->getTimestamp() * 1000,
                    'values' => [
                        'package_type' => $validated['package_type'],
                        'grade_level' => $validated['grade_level'],
                        'subject_name' => $validated['subject_name'],
                        'package_name' => $validated['package_name'],
                        'target_learning' => $validated['target_learning'],
                        'learning_method' => $validated['learning_method'],
                        'target_capacity' => $validated['target_capacity'],
                        'default_location' => $validated['default_location'] ?? '',
                        'zoom_meeting_url' => $validated['zoom_meeting_url'] ?? '',
                        'availability_slot_ids' => array_values(array_unique($validated['availability_slot_ids'])),
                    ],
                ],
            ],
        );

        return redirect()->route('tutor.classes.materials', ['packageId' => $newPackageId, 'session' => 1])
            ->with('status', 'Draf kelas berhasil dibuat. Lengkapi materi dan soal setiap pertemuan sebelum membuka pendaftaran.');
    }

    private function pageData(Teacher $tutor): array
    {
        $classes = ModulePackage::query()
            ->where('teacher_id', $tutor->teacher_id)
            ->with([
                'learningMaterials' => fn ($query) => $query->orderByDesc('created_at'),
                'questionBanks',
                'availabilitySlots',
                'tutorPackageRates' => fn ($query) => $query->where('teacher_level', $tutor->teacher_level),
                'orders' => fn ($query) => $query->whereIn('payment_status', ['settled', 'paid'])
                    ->with([
                        'squad.classMembers' => fn ($members) => $members->where('membership_status', 'active'),
                        'selectedSlot',
                        'learningSessions.evaluationScores',
                    ]),
            ])
            ->orderByDesc('is_active')
            ->orderBy('package_name')
            ->get()
            ->each(function (ModulePackage $package) {
                $studentIds = $package->orders->flatMap(function ($order) {
                    return collect([$order->student_id])
                        ->merge($order->squad?->classMembers?->pluck('student_id') ?? collect());
                })->filter()->unique()->values();
                $scores = $package->orders->flatMap(fn ($order) => $order->learningSessions->flatMap->evaluationScores);
                $pairedScores = $scores->filter(fn ($score) => $score->pretest_score !== null && $score->posttest_score !== null);
                $capacity = max(0, (int) ($package->max_capacity ?? 0));
                $package->enrolled_student_count = $studentIds->count();
                $package->enrolled_student_ids = $studentIds;
                $package->capacity_count = $capacity;
                $package->occupancy_percentage = $capacity > 0 ? min(100, (int) round(($studentIds->count() / $capacity) * 100)) : 0;
                $package->average_pretest_score = $scores->whereNotNull('pretest_score')->avg('pretest_score');
                $package->average_posttest_score = $scores->whereNotNull('posttest_score')->avg('posttest_score');
                $package->diagnostic_change = $pairedScores->isNotEmpty()
                    ? round($pairedScores->avg(fn ($score) => $score->posttest_score - $score->pretest_score), 1)
                    : null;
                $package->quiz_question_count = $package->questionBanks->count();
                $package->incomplete_session_numbers = $package->incompleteSessionNumbers();
                $package->has_required_content = $package->incomplete_session_numbers === [];
                $package->class_status = (! $package->is_active || ! $package->is_published || ! $package->has_required_content)
                    ? 'draft'
                    : (($capacity > 0 && $studentIds->count() >= $capacity) ? 'full' : (! $package->is_open ? 'closed' : 'active'));
                $package->setRelation('representativeSlot', $package->orders->pluck('selectedSlot')->filter()->first());
            });

        $shareSquad = Squad::query()
            ->where('teacher_id', $tutor->teacher_id)
            ->where('status', 'open')
            ->with(['package', 'members.order'])
            ->orderByDesc('created_at')
            ->get()
            ->first(function (Squad $squad) {
                $activeMembers = $squad->members->filter(fn ($member) => in_array($member->order?->payment_status, ['settled', 'paid', 'pending'], true)
                    && ($member->order?->payment_status !== 'pending' || $member->order?->expires_at?->isFuture()))->count();
                $squad->active_member_count = $activeMembers;
                $squad->remaining_seats = max(0, (int) ($squad->target_capacity ?? $squad->target_quota ?? 0) - $activeMembers);

                return $squad->remaining_seats > 0;
            });

        $diagnosticScores = $classes->flatMap(fn ($package) => $package->orders->flatMap(fn ($order) => $order->learningSessions->flatMap->evaluationScores));
        $allStudents = $classes->flatMap(fn ($package) => $package->enrolled_student_ids)->unique();
        $totalCapacity = $classes->where('class_status', '!=', 'draft')->sum('capacity_count');
        $activeEnrollment = $classes->where('class_status', '!=', 'draft')->sum('enrolled_student_count');

        return [
            'tutor' => $tutor,
            'classes' => $classes,
            'classStats' => [
                'total' => $classes->count(),
                'active' => $classes->where('class_status', '!=', 'draft')->count(),
                'draft' => $classes->where('class_status', 'draft')->count(),
                'full' => $classes->where('class_status', 'full')->count(),
                'students' => $allStudents->count(),
                'enrolled' => $activeEnrollment,
                'capacity' => $totalCapacity,
                'occupancy' => $totalCapacity > 0 ? (int) round(($activeEnrollment / $totalCapacity) * 100) : null,
                'average_pretest' => $diagnosticScores->whereNotNull('pretest_score')->avg('pretest_score'),
                'average_change' => $diagnosticScores->filter(fn ($score) => $score->pretest_score !== null && $score->posttest_score !== null)->avg(fn ($score) => $score->posttest_score - $score->pretest_score),
            ],
            'masterSlots' => $tutor->availabilitySlots()->where('is_active', true)->where('is_available', true)->orderBy('day_of_week')->orderBy('start_time')->get(),
            'shareSquad' => $shareSquad,
        ];
    }

    private function normalizeGrade(?string $grade): string
    {
        return strtoupper(trim((string) $grade));
    }

    private function normalizeMethod(?string $method): string
    {
        return match (strtolower(trim((string) $method))) {
            'online', 'zoom', 'online_zoom' => 'online_zoom',
            'home visit', 'home_visit' => 'home_visit',
            default => strtolower(trim((string) $method)),
        };
    }

    private function subjectOptions(): array
    {
        $defaults = [
            'SD' => ['Tematik Terpadu SD', 'Matematika Dasar', 'Calistung', 'IPA SD', 'Bahasa Inggris Anak'],
            'SMP' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'],
            'SMA' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'],
            'UMUM' => ['UI/UX Design', 'Web Coding', 'Bahasa Asing', 'Data Analytics'],
        ];

        foreach ($defaults as $level => $names) {
            $configured = Subject::query()->whereRaw('LOWER(TRIM(grade_level)) = ?', [mb_strtolower($level)])->get();
            $defaults[$level] = $configured->isEmpty()
                ? array_map(fn (string $name) => ['name' => $name, 'active' => true], $names)
                : $configured->map(fn (Subject $subject) => ['name' => $subject->name, 'active' => $subject->is_active])->all();
        }

        return $defaults;
    }

    private function tutorFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        return Teacher::query()->where('user_id', $user->user_id)->firstOrFail();
    }
}
