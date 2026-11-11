<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\DigitalReport;
use App\Models\EvaluationScore;
use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TutorTeachingRoomController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->teacherFor($request);
        $classes = ModulePackage::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('is_active', true)
            ->with([
                'orders' => fn ($query) => $query->whereIn('payment_status', ['settled', 'paid'])
                    ->select(['order_id', 'package_id', 'student_id']),
            ])
            ->orderBy('package_name')
            ->get()
            ->each(function (ModulePackage $package) {
                $package->enrolled_students_count = $package->orders->pluck('student_id')->filter()->unique()->count();
            });

        return view('pages.tutor.ruang-mengajar', compact('teacher', 'classes'));
    }

    public function show(Request $request, string $packageId, ?string $studentId = null): View
    {
        $teacher = $this->teacherFor($request);
        $package = ModulePackage::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('is_active', true)
            ->whereKey($packageId)
            ->firstOrFail();

        $room = $this->roomData($teacher, $package);
        $selected = $studentId ? ($room['students']->get($studentId) ?? abort(404)) : null;
        $pretestAnswers = collect();
        $selectedEvaluation = null;
        $selectedAttendance = null;

        if ($selected) {
            $selectedEvaluation = $selected['session']->evaluationScores
                ->firstWhere('student_id', $studentId);
            $selectedAttendance = $selected['session']->studentAttendances
                ->firstWhere('student_id', $studentId);
            $pretestAnswers = $selected['session']->studentAnswers
                ->where('student_id', $studentId)
                ->filter(fn ($answer) => $answer->question?->exam_type === 'pre_test')
                ->values();
        }

        return view('pages.tutor.teaching-room', [
            'teacher' => $teacher,
            'package' => $package,
            'students' => $room['students'],
            'activeSession' => $room['activeSession'],
            'selectedStudent' => $selected['student'] ?? null,
            'selectedSession' => $selected['session'] ?? null,
            'roomSession' => $selected['session'] ?? $room['activeSession'],
            'selectedEvaluation' => $selectedEvaluation,
            'selectedAttendance' => $selectedAttendance,
            'pretestAnswers' => $pretestAnswers,
            'pretestCorrectCount' => $pretestAnswers->where('is_correct', true)->count(),
            'pretestWrongCount' => $pretestAnswers->where('is_correct', false)->count(),
        ]);
    }

    public function updateAttendance(Request $request, string $packageId, string $studentId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $validated = $request->validate(['status' => ['required', 'in:present,absent']]);
        $package = $this->packageForTeacher($teacher, $packageId);
        $room = $this->roomData($teacher, $package);
        $studentRow = $room['students']->get($studentId);
        abort_unless($studentRow, 404);

        StudentAttendance::updateOrCreate(
            ['session_id' => $studentRow['session']->session_id, 'student_id' => $studentId],
            ['attendance_time' => now(), 'status' => $validated['status']],
        );

        return redirect()
            ->route('tutor.classroom.show', $packageId)
            ->with('status', 'Kehadiran siswa berhasil diperbarui.');
    }

    public function saveEvaluation(Request $request, string $packageId, string $studentId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $validated = $request->validate([
            'posttest_score' => ['required', 'integer', 'min:0', 'max:100'],
            'teacher_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $package = $this->packageForTeacher($teacher, $packageId);
        $room = $this->roomData($teacher, $package);
        $studentRow = $room['students']->get($studentId);
        abort_unless($studentRow, 404);
        $session = $studentRow['session'];

        DB::transaction(function () use ($session, $studentId, $package, $teacher, $validated, $studentRow) {
            $evaluation = EvaluationScore::firstOrNew([
                'session_id' => $session->session_id,
                'student_id' => $studentId,
            ]);
            $evaluation->posttest_score = (int) $validated['posttest_score'];
            $evaluation->teacher_notes = $validated['teacher_notes'] ?? null;
            if ($evaluation->pretest_score !== null) {
                $evaluation->progress_percentage = $evaluation->posttest_score - $evaluation->pretest_score;
            }
            $evaluation->created_at ??= now();
            $evaluation->save();

            $sessionIds = collect($studentRow['sessions'])->pluck('session_id');
            $scores = EvaluationScore::query()
                ->where('student_id', $studentId)
                ->whereIn('session_id', $sessionIds)
                ->get();
            $pretestScores = $scores->pluck('pretest_score')->filter(fn ($score) => $score !== null);
            $posttestScores = $scores->pluck('posttest_score')->filter(fn ($score) => $score !== null);
            $progressScores = $scores->pluck('progress_percentage')->filter(fn ($score) => $score !== null);
            $attendances = StudentAttendance::query()
                ->where('student_id', $studentId)
                ->whereIn('session_id', $sessionIds)
                ->get();
            $attendancePercentage = $sessionIds->isEmpty()
                ? null
                : round(($attendances->where('status', 'present')->count() / $sessionIds->count()) * 100, 2);

            DigitalReport::updateOrCreate(
                ['student_id' => $studentId, 'package_id' => $package->package_id, 'teacher_id' => $teacher->teacher_id],
                [
                    'average_pretest_score' => $pretestScores->isNotEmpty() ? round($pretestScores->avg(), 2) : null,
                    'average_posttest_score' => $posttestScores->isNotEmpty() ? round($posttestScores->avg(), 2) : null,
                    'progress_percentage' => $progressScores->isNotEmpty() ? round($progressScores->avg(), 2) : null,
                    'attendance_percentage' => $attendancePercentage,
                    'teacher_notes' => $validated['teacher_notes'] ?? null,
                    'report_status' => 'published',
                    'generated_at' => now(),
                ],
            );
        });

        return redirect()
            ->route('tutor.classroom.student', [$packageId, $studentId])
            ->with('status', 'Evaluasi dan rapor digital berhasil disimpan.');
    }

    private function teacherFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'teacher', 403);

        return Teacher::query()->with('user')->where('user_id', $user->user_id)->firstOrFail();
    }

    private function packageForTeacher(Teacher $teacher, string $packageId): ModulePackage
    {
        return ModulePackage::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('is_active', true)
            ->whereKey($packageId)
            ->firstOrFail();
    }

    private function roomData(Teacher $teacher, ModulePackage $package): array
    {
        $sessions = LearningSession::query()
            ->with([
                'student.user',
                'order.student.user',
                'order.squad.members.student.user',
                'order.squad.hostStudent.user',
                'evaluationScores',
                'studentAttendances',
                'studentAnswers.question',
            ])
            ->where('teacher_id', $teacher->teacher_id)
            ->whereHas('order', fn ($query) => $query
                ->where('package_id', $package->package_id)
                ->whereIn('payment_status', ['settled', 'paid']))
            ->orderBy('scheduled_at')
            ->get();

        $studentRows = collect();
        foreach ($sessions as $session) {
            $participants = collect([$session->student, $session->order?->student, $session->order?->squad?->hostStudent])
                ->merge($session->order?->squad?->members->pluck('student') ?? collect())
                ->filter()
                ->unique('student_id');

            foreach ($participants as $student) {
                $studentRows->push([
                    'student' => $student,
                    'session' => $session,
                ]);
            }
        }

        $students = $studentRows
            ->groupBy(fn ($row) => $row['student']->student_id)
            ->map(function ($rows) {
                $ordered = $rows->sortBy(function ($row) {
                    $session = $row['session'];
                    if ($session->session_status === 'in_progress') return '0-0000000000';
                    if ($session->scheduled_at?->isFuture()) return '1-'.$session->scheduled_at->timestamp;
                    if ($session->scheduled_at?->isToday()) return '2-'.$session->scheduled_at->timestamp;
                    return '3-'.(PHP_INT_MAX - ($session->scheduled_at?->timestamp ?? 0));
                });
                $primary = $ordered->first();

                return [
                    'student' => $primary['student'],
                    'session' => $primary['session'],
                    'sessions' => $rows->pluck('session')->unique('session_id')->values(),
                ];
            });

        $activeSession = $sessions->first(fn ($session) => $session->session_status === 'in_progress')
            ?? $sessions->first(fn ($session) => $session->scheduled_at?->isFuture())
            ?? $sessions->first();

        return ['students' => $students, 'activeSession' => $activeSession];
    }
}
