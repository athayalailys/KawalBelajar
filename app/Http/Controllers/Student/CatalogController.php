<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\Teacher;
use App\Services\Student\BookingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatalogController extends Controller
{
    public function subjects(Request $request): JsonResponse
    {
        $filters = Validator::make($request->query(), [
            'education_level' => ['required', 'string', 'in:SD,SMP,SMA,UMUM'],
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
        ])->validate();

        $level = $filters['education_level'];
        $method = $filters['learning_method'];
        $subjects = ModulePackage::query()
            ->where('is_active', true)
            ->where('grade_level', $level)
            ->whereHas('learningMaterials.teacher', function (Builder $teachers) use ($level, $method) {
                $teachers->where('is_active', true)
                    ->where('cv_status', 'approved')
                    ->whereJsonContains('approved_levels', $level)
                    ->whereHas('user', fn (Builder $user) => $user->where('is_active', true)->where('role', 'teacher'))
                    ->whereHas('availabilitySlots', fn (Builder $slots) => $slots->where('is_active', true)
                        ->where(fn (Builder $query) => $query->where('learning_method', $method)->orWhereNull('learning_method')))
                    ->whereHas('tutorPackageRates', fn (Builder $rates) => $rates->whereColumn('master_tariffs.package_id', 'learning_materials.package_id')
                        ->whereColumn('master_tariffs.teacher_level', 'teachers.teacher_level')
                        ->where(fn (Builder $query) => $query->where('learning_method', $method)->orWhereNull('learning_method')));
            })
            ->whereNotNull('subject_name')
            ->select('subject_name')
            ->distinct()
            ->orderBy('subject_name')
            ->pluck('subject_name')
            ->values();

        return response()->json(['success' => true, 'data' => $subjects]);
    }

    public function index(Request $request, BookingService $bookingService): JsonResponse
    {
        $filters = Validator::make($request->query(), [
            'education_level' => ['required', 'string', 'in:SD,SMP,SMA,UMUM'],
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
            'subject' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
        ])->validate();

        $packages = ModulePackage::query()
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('is_open', true)
            ->where('grade_level', $filters['education_level'])
            ->when(!empty($filters['subject']), fn (Builder $query) => $query->where('subject_name', $filters['subject']))
            ->with('learningMaterials')
            ->orderBy('package_name')
            ->get();
        $packages = $packages->filter(fn (ModulePackage $package) => $package->isReadyForEnrollment())->values();
        $packages->load('availabilitySlots');

        if ($packages->isEmpty()) {
            return response()->json([
                'success' => true,
                'filters' => [
                    'education_level' => $filters['education_level'],
                    'learning_method' => $filters['learning_method'],
                ],
                'data' => [],
            ]);
        }

        $packageIds = $packages->pluck('package_id');
        $method = $filters['learning_method'];

        $teacherQuery = Teacher::query()
            ->where('is_active', true)
            ->where('cv_status', 'approved')
            ->whereJsonContains('approved_levels', $filters['education_level'])
            ->whereHas('user', fn ($query) => $query->where('is_active', true)->where('role', 'teacher'))
            ->whereHas('availabilitySlots', function ($query) use ($method) {
                $query->where('is_active', true)
                    ->where(fn ($methodQuery) => $methodQuery->where('learning_method', $method)->orWhereNull('learning_method'));
            })
            ->whereHas('learningMaterials', fn ($query) => $query->whereIn('package_id', $packageIds))
            ->with([
                'user',
                'availabilitySlots' => fn ($query) => $query->where('is_active', true)
                    ->where(fn ($methodQuery) => $methodQuery->where('learning_method', $method)->orWhereNull('learning_method'))
                    ->orderBy('day_of_week')->orderBy('start_time'),
                'learningMaterials' => fn ($query) => $query->whereIn('package_id', $packageIds)->orderBy('title'),
                'tutorPackageRates' => fn ($query) => $query->whereIn('package_id', $packageIds),
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $teacherQuery->where(function ($query) use ($search) {
                $query->whereHas('user', fn ($userQuery) => $userQuery->where('full_name', 'like', "%{$search}%"))
                    ->orWhere('focus_subject', 'like', "%{$search}%");
            });
        }

        $teachers = $teacherQuery->withSum('workLogs as teaching_minutes', 'duration_minutes')->orderBy('teacher_id')->get()->map(function (Teacher $teacher) use ($packages, $method, $bookingService) {
            $teacherPackages = $packages->map(function (ModulePackage $package) use ($teacher, $method) {
                $rate = $teacher->tutorPackageRates->first(fn ($item) =>
                    $item->package_id === $package->package_id
                    && $item->teacher_level === $teacher->teacher_level
                    && $item->learning_method === $method
                );
                $rate ??= $teacher->tutorPackageRates->first(fn ($item) =>
                    $item->package_id === $package->package_id
                    && $item->teacher_level === $teacher->teacher_level
                    && $item->learning_method === null
                );

                if (!$rate || (float) $rate->category_rate <= 0) {
                    return null;
                }

                $materials = $teacher->learningMaterials
                    ->where('package_id', $package->package_id)
                    ->values()
                    ->map(fn ($material) => [
                        'material_id' => $material->material_id,
                        'title' => $material->title,
                        'material_type' => $material->material_type,
                        'file_or_url' => $material->file_or_url,
                        'video_duration_minutes' => $material->video_duration_minutes,
                    ]);

                $packageSlots = $package->availabilitySlots->isNotEmpty()
                    ? $package->availabilitySlots
                    : $teacher->availabilitySlots;

                return [
                    'package_id' => $package->package_id,
                    'package_name' => $package->package_name,
                    'grade_level' => $package->grade_level,
                    'description' => $package->description,
                    'base_price' => $package->base_price,
                    'teacher_rate_per_session' => $rate->category_rate,
                    'availability_slots' => $packageSlots
                        ->filter(fn ($slot) => $slot->is_active && $slot->is_available
                            && ($slot->learning_method === null || $slot->learning_method === $method))
                        ->map(fn ($slot) => ['slot_id' => $slot->slot_id, 'day_of_week' => $slot->day_of_week, 'start_time' => $slot->start_time, 'end_time' => $slot->end_time])
                        ->values(),
                    'materials' => $materials,
                ];
            })->filter()->values();

            if ($teacherPackages->isEmpty()) {
                return null;
            }

            $teacherSlots = collect($bookingService->getTeacherSlotsWithClashDetection($teacher->teacher_id, $method));
            $availableSlots = $teacherSlots
                ->filter(fn (array $slot) => $slot['is_available'])
                ->map(function (array $slot) {
                    $weekday = [
                        'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
                        'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
                    ][$slot['day_of_week']] ?? null;
                    if ($weekday === null) return null;

                    $date = Carbon::today();
                    $daysUntilSlot = ($weekday - $date->dayOfWeekIso + 7) % 7;
                    $date->addDays($daysUntilSlot)->setTimeFromTimeString($slot['start_time']);
                    if ($date->lte(now())) $date->addWeek();

                    return $slot + ['starts_at' => $date->format('Y-m-d H:i:s'), '_sort' => $date->timestamp];
                })
                ->filter()
                ->sortBy('_sort')
                ->first();

            return [
                'teacher_id' => $teacher->teacher_id,
                'full_name' => $teacher->user?->full_name,
                'teacher_level' => $teacher->teacher_level,
                'focus_subject' => $teacher->focus_subject,
                'university' => $teacher->university,
                'study_program' => $teacher->study_program,
                'total_teaching_hours' => round(((int) $teacher->teaching_minutes) / 60, 1),
                'rating' => null,
                'next_available_slot' => $availableSlots ? collect($availableSlots)->except('_sort') : null,
                'learning_method' => $method,
                'availability_slots' => $teacherSlots->values(),
                'packages' => $teacherPackages,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'filters' => [
                'education_level' => $filters['education_level'],
                'learning_method' => $filters['learning_method'],
            ],
            'data' => $teachers,
        ]);
    }
}
