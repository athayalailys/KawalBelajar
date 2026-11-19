<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\Teacher;
use App\Services\Student\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function subjects(Request $request): JsonResponse
    {
        $query = $request->query();
        $query['education_level'] = mb_strtoupper(trim((string) ($query['education_level'] ?? '')));
        $query['learning_method'] = $this->normalizeMethod((string) ($query['learning_method'] ?? ''));
        $filters = Validator::make($query, [
            'education_level' => ['required', 'string', 'in:SD,SMP,SMA,UMUM'],
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
        ])->validate();

        $subjects = $this->eligiblePackages($filters['education_level'], $filters['learning_method'])
            ->pluck('subject_name')->filter()->unique(fn ($name) => mb_strtolower($name))->sort()->values();

        return response()->json(['success' => true, 'data' => $subjects]);
    }

    public function index(Request $request, BookingService $bookingService): JsonResponse
    {
        $query = $request->query();
        $query['education_level'] = mb_strtoupper(trim((string) ($query['education_level'] ?? '')));
        $query['learning_method'] = $this->normalizeMethod((string) ($query['learning_method'] ?? ''));
        $filters = Validator::make($query, [
            'education_level' => ['required', 'string', 'in:SD,SMP,SMA,UMUM'],
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
            'subject' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
        ])->validate();

        $method = $this->normalizeMethod($filters['learning_method']);
        $packages = $this->eligiblePackages($filters['education_level'], $method)
            ->when(filled($filters['subject'] ?? null), fn ($items) => $items->filter(
                fn (ModulePackage $package) => mb_strtolower((string) $package->subject_name) === mb_strtolower($filters['subject'])
            ))
            ->values();

        $teacherIds = $packages->pluck('teacher_id')->unique()->filter();
        $teachers = Teacher::query()->with(['user', 'availabilitySlots', 'workLogs'])
            ->whereIn('teacher_id', $teacherIds)->get()
            ->filter(fn (Teacher $teacher) => $this->teacherCanTeach($teacher, $filters['education_level']))
            ->when(filled($filters['search'] ?? null), function ($items) use ($filters) {
                $search = mb_strtolower($filters['search']);
                return $items->filter(fn (Teacher $teacher) =>
                    str_contains(mb_strtolower((string) $teacher->user?->full_name), $search)
                    || str_contains(mb_strtolower((string) $teacher->focus_subject), $search)
                );
            });

        $data = $teachers->map(function (Teacher $teacher) use ($packages, $method, $bookingService) {
            $teacherPackages = $packages->where('teacher_id', $teacher->teacher_id)->map(function (ModulePackage $package) use ($teacher, $method) {
                $rate = $package->tutorPackageRates->first(fn ($item) =>
                    mb_strtolower((string) $item->teacher_level) === mb_strtolower((string) $teacher->teacher_level)
                    && ($item->learning_method === null || $this->normalizeMethod($item->learning_method) === $method)
                );
                if (!$rate || (float) $rate->category_rate <= 0) return null;

                $slots = $teacher->availabilitySlots
                    ->filter(fn ($slot) => $slot->is_active && $slot->is_available
                        && ($slot->learning_method === null || $this->normalizeMethod($slot->learning_method) === $method));
                $linkedSlotIds = $package->availabilitySlots->pluck('slot_id');
                if ($linkedSlotIds->isNotEmpty()) $slots = $slots->whereIn('slot_id', $linkedSlotIds);

                return [
                    'package_id' => $package->package_id,
                    'package_name' => $package->package_name,
                    'grade_level' => $package->grade_level,
                    'subject_name' => $package->subject_name,
                    'learning_method' => $method,
                    'home_visit_area' => $package->home_visit_area,
                    'description' => $package->description,
                    'base_price' => $package->base_price,
                    'teacher_rate_per_session' => $rate->category_rate,
                    'availability_slots' => $slots->map(fn ($slot) => [
                        'slot_id' => $slot->slot_id, 'day_of_week' => $slot->day_of_week,
                        'start_time' => $slot->start_time, 'end_time' => $slot->end_time,
                        'learning_method' => $slot->learning_method ?? 'both', 'is_available' => true,
                    ])->values(),
                    'materials' => $package->learningMaterials->map(fn ($material) => [
                        'material_id' => $material->material_id, 'title' => $material->title,
                        'material_type' => $material->material_type, 'file_or_url' => $material->file_or_url,
                        'video_duration_minutes' => $material->video_duration_minutes,
                    ])->values(),
                ];
            })->filter()->values();

            if ($teacherPackages->isEmpty()) return null;

            $availableSlots = collect($bookingService->getTeacherSlotsWithClashDetection($teacher->teacher_id, $method))
                ->filter(fn (array $slot) => $slot['is_available'])
                ->map(function (array $slot) {
                    $weekday = ['Monday'=>1,'Tuesday'=>2,'Wednesday'=>3,'Thursday'=>4,'Friday'=>5,'Saturday'=>6,'Sunday'=>7][$slot['day_of_week']] ?? null;
                    if ($weekday === null) return null;
                    $date = Carbon::today()->addDays(($weekday - Carbon::today()->dayOfWeekIso + 7) % 7)->setTimeFromTimeString($slot['start_time']);
                    if ($date->lte(now())) $date->addWeek();
                    return $slot + ['starts_at' => $date->format('Y-m-d H:i:s'), '_sort' => $date->timestamp];
                })->filter()->sortBy('_sort')->first();

            $teacherSlots = collect($bookingService->getTeacherSlotsWithClashDetection($teacher->teacher_id, $method));
            return [
                'teacher_id' => $teacher->teacher_id, 'full_name' => $teacher->user?->full_name,
                'teacher_level' => $teacher->teacher_level, 'focus_subject' => $teacher->focus_subject,
                'university' => $teacher->university, 'study_program' => $teacher->study_program,
                'total_teaching_hours' => round((float) $teacher->workLogs->sum('duration_minutes') / 60, 1),
                'rating' => null, 'next_available_slot' => $availableSlots ? collect($availableSlots)->except('_sort') : null,
                'learning_method' => $method, 'availability_slots' => $teacherSlots->values(), 'packages' => $teacherPackages,
            ];
        })->filter()->values();

        return response()->json(['success' => true, 'filters' => [
            'education_level' => strtoupper($filters['education_level']), 'learning_method' => $method,
        ], 'data' => $data]);
    }

    /** Return exactly the published, ready classes usable by both Step 1 and Step 2. */
    private function eligiblePackages(string $level, string $method)
    {
        $normalizedLevel = mb_strtolower(trim($level));
        $normalizedMethod = $this->normalizeMethod($method);

        return ModulePackage::query()->with(['teacher.user', 'tutorPackageRates', 'learningMaterials', 'availabilitySlots'])
            ->where('is_active', true)->where('is_published', true)->where('is_open', true)
            ->whereRaw('LOWER(TRIM(grade_level)) = ?', [$normalizedLevel])
            ->where(function ($query) use ($normalizedMethod) {
                $query->whereNull('learning_method')
                    ->orWhereRaw('LOWER(learning_method) = ?', [$normalizedMethod])
                    ->orWhere(fn ($alias) => $normalizedMethod === 'online_zoom'
                        ? $alias->whereRaw('LOWER(learning_method) = ?', ['online']) : $alias->whereRaw('1 = 0'));
            })
            ->whereNotNull('teacher_id')->orderBy('package_name')->get()
            ->filter(function (ModulePackage $package) use ($normalizedLevel, $normalizedMethod) {
                if (mb_strtolower(trim((string) $package->grade_level)) !== $normalizedLevel
                    || ($package->learning_method && $this->normalizeMethod($package->learning_method) !== $normalizedMethod)
                    || !$package->isReadyForEnrollment()) return false;

                $teacher = $package->teacher;
                if (!$teacher || !$this->teacherCanTeach($teacher, $normalizedLevel)) return false;

                $hasRate = $package->tutorPackageRates->contains(fn ($rate) =>
                    mb_strtolower((string) $rate->teacher_level) === mb_strtolower((string) $teacher->teacher_level)
                    && ($rate->learning_method === null || $this->normalizeMethod($rate->learning_method) === $normalizedMethod)
                    && (float) $rate->category_rate > 0
                );
                if (!$hasRate) return false;

                return !\App\Models\Subject::query()->whereRaw('LOWER(TRIM(grade_level)) = ?', [$normalizedLevel])
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim((string) $package->subject_name))])
                    ->where('is_active', false)->exists();
            })->values();
    }

    private function teacherCanTeach(Teacher $teacher, string $level): bool
    {
        if (!$teacher->is_active || strtolower((string) $teacher->cv_status) !== 'approved'
            || !$teacher->user?->is_active || !in_array($teacher->user->role, ['teacher', 'tutor'], true)) return false;

        $levels = array_merge((array) $teacher->approved_levels, (array) $teacher->verified_levels,
            (array) $teacher->getAttribute('verified_jenjang'));
        return in_array(mb_strtolower(trim($level)), array_map(fn ($item) => mb_strtolower(trim((string) $item)), $levels), true);
    }

    private function normalizeMethod(?string $method): string
    {
        return match (mb_strtolower(trim((string) $method))) {
            'online', 'zoom', 'online_zoom' => 'online_zoom',
            'home_visit', 'home visit' => 'home_visit',
            default => mb_strtolower(trim((string) $method)),
        };
    }
}
