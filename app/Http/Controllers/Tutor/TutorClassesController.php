<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Squad;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TutorPackageRate;
use Illuminate\Http\RedirectResponse;
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

        $tariffs = MasterTariff::query()
            ->with('package')
            ->where(function ($query) use ($tutor) {
                $query->where('teacher_level', $tutor->teacher_level)->orWhereNull('teacher_level');
            })
            ->get()
            ->mapWithKeys(function (MasterTariff $tariff) {
                $grade = in_array(strtoupper((string) $tariff->grade_level), ['SD', 'SMP', 'SMA', 'UMUM'], true)
                    ? $tariff->grade_level
                    : $tariff->package?->grade_level;
                $method = $tariff->learning_method ?? $tariff->method ?? $tariff->package?->learning_method;
                $rate = $tariff->category_rate ?? $tariff->base_rate;

                return $grade && $method && $rate
                    ? [strtoupper($grade).'|'.$method => (float) $rate]
                    : [];
            });

        return view('pages.tutor.create-class', [
            'tutor' => $tutor,
            'approvedLevels' => $tutor->approved_levels ?? $tutor->verified_levels ?? [],
            'subjectsByLevel' => $this->subjectOptions(),
            'defaultLocation' => $tutor->default_location,
            'tariffs' => $tariffs,
            'days' => ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'],
            'availabilitySlots' => $tutor->availabilitySlots()->where('is_active', true)->where('is_available', true)->orderBy('day_of_week')->orderBy('start_time')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);
        $tutor = Teacher::query()->where('user_id', $user->user_id)->firstOrFail();
        $approvedLevels = $tutor->approved_levels ?? $tutor->verified_levels ?? [];

        $validated = $request->validate([
            'package_type' => ['required', Rule::in(['monthly', 'instant'])],
            'grade_level' => ['required', Rule::in($approvedLevels)],
            'package_name' => ['required', 'string', 'max:255'],
            'subject_name' => ['required', 'string', 'max:100'],
            'target_learning' => ['required', 'string', 'max:3000'],
            'learning_method' => ['required', Rule::in(['online_zoom', 'home_visit'])],
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

        $configuredSubjects = Subject::query()->where('grade_level', $validated['grade_level'])->get();
        if ($configuredSubjects->isNotEmpty() && ! $configuredSubjects->contains(fn (Subject $subject) => $subject->is_active && $subject->name === $validated['subject_name'])) {
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

        $tariff = MasterTariff::query()
            ->with('package')
            ->where(function ($query) use ($tutor) {
                $query->where('teacher_level', $tutor->teacher_level)->orWhereNull('teacher_level');
            })
            ->get()
            ->first(function (MasterTariff $candidate) use ($validated) {
                $grade = in_array(strtoupper((string) $candidate->grade_level), ['SD', 'SMP', 'SMA', 'UMUM'], true)
                    ? $candidate->grade_level
                    : $candidate->package?->grade_level;
                $method = $candidate->learning_method ?? $candidate->method ?? $candidate->package?->learning_method;
                return strtoupper((string) $grade) === strtoupper($validated['grade_level'])
                    && $method === $validated['learning_method']
                    && (float) ($candidate->category_rate ?? $candidate->base_rate) > 0;
            });

        if (! $tariff) {
            return back()->withInput()->withErrors(['tariff' => 'Tarif resmi untuk jenjang dan metode ini belum tersedia. Hubungi admin untuk mengaturnya.']);
        }

        $basePrice = (float) ($tariff->category_rate ?? $tariff->base_rate);
        $newPackageId = null;

        DB::transaction(function () use ($validated, $tutor, $basePrice, &$newPackageId) {
                $sessionPackage = $validated['package_type'] === 'monthly' ? 4 : 1;
                $package = ModulePackage::create([
                    'teacher_id' => $tutor->teacher_id,
                    'package_name' => $validated['package_name'],
                    'grade_level' => $validated['grade_level'],
                    'subject_name' => $validated['subject_name'],
                    'learning_method' => $validated['learning_method'],
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
                    $tutor->update(['default_location' => $validated['default_location']]);
                }
        });

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

    private function subjectOptions(): array
    {
        $defaults = [
            'SD' => ['Tematik Terpadu SD', 'Matematika Dasar', 'Calistung', 'IPA SD', 'Bahasa Inggris Anak'],
            'SMP' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'],
            'SMA' => ['Fisika', 'Matematika', 'Kimia', 'Biologi', 'Bahasa Inggris', 'UTBK SNBT'],
            'UMUM' => ['UI/UX Design', 'Web Coding', 'Bahasa Asing', 'Data Analytics'],
        ];

        foreach ($defaults as $level => $names) {
            $configured = Subject::query()->where('grade_level', $level)->get();
            $defaults[$level] = $configured->isEmpty()
                ? array_map(fn (string $name) => ['name' => $name, 'active' => true], $names)
                : $configured->map(fn (Subject $subject) => ['name' => $subject->name, 'active' => $subject->is_active])->all();
        }

        return $defaults;
    }
}
