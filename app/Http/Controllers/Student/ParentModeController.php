<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EvaluationScore;
use App\Models\StudentAttendance;
use App\Models\Student;
use App\Models\LearningSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentModeController extends Controller
{
    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate(['pin' => ['required', 'digits:6']]);
        $student = $request->user()?->student;

        $educationLevel = strtoupper(trim((string) $student?->education_level));
        abort_unless($student && in_array($educationLevel, ['SD', 'SMP', 'SMA'], true), 403);

        if (!$student->parent_pin_hash || !Hash::check($validated['pin'], $student->parent_pin_hash)) {
            return response()->json([
                'message' => 'PIN orang tua tidak valid.',
                'errors' => ['pin' => ['PIN yang dimasukkan salah.']],
            ], 422);
        }

        $request->session()->put('parent_mode_student_id', $student->student_id);

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi PIN berhasil.',
            'data' => $this->reportFor($student),
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $student = $request->user()?->student;
        abort_unless($student && $request->session()->get('parent_mode_student_id') === $student->student_id, 403);
        abort_unless(in_array(strtoupper(trim((string) $student->education_level)), ['SD', 'SMP', 'SMA'], true), 403);

        return response()->json(['success' => true, 'data' => $this->reportFor($student)]);
    }

    private function reportFor(Student $student): array
    {
        $sessions = LearningSession::query()
            ->where('student_id', $student->student_id)
            ->with(['teacher.user', 'material.package', 'order.package', 'schedule'])
            ->orderByDesc('scheduled_at')
            ->get();
        $evaluations = EvaluationScore::query()->where('student_id', $student->student_id)
            ->whereIn('session_id', $sessions->pluck('session_id'))->get()->keyBy('session_id');
        $attendances = StudentAttendance::query()->where('student_id', $student->student_id)
            ->whereIn('session_id', $sessions->pluck('session_id'))->get()->keyBy('session_id');

        $history = $sessions->map(function (LearningSession $session) use ($evaluations, $attendances) {
            $evaluation = $evaluations->get($session->session_id);
            $attendance = $attendances->get($session->session_id);
            $status = strtolower((string) $attendance?->status);
            $attendanceLabel = match ($status) {
                'present', 'attended', 'hadir' => 'Hadir',
                'permission', 'sick', 'izin', 'sakit' => 'Izin',
                'absent', 'alfa', 'alpha' => 'Alfa',
                default => 'Belum dicatat',
            };
            $package = $session->order?->package ?? $session->material?->package;

            return [
                'session_id' => $session->session_id,
                'date' => $session->scheduled_at?->toDateString() ?? $session->schedule?->start_time,
                'subject' => $package?->subject_name ?? $session->material?->title ?? 'Sesi pembelajaran',
                'tutor_name' => $session->teacher?->user?->full_name ?? 'Belum ditentukan',
                'pretest_score' => $evaluation?->pretest_score,
                'posttest_score' => $evaluation?->posttest_score,
                'attendance' => $attendanceLabel,
                'teacher_notes' => $evaluation?->teacher_notes,
            ];
        })->values();

        $pretests = $history->pluck('pretest_score')->filter(fn ($score) => $score !== null);
        $posttests = $history->pluck('posttest_score')->filter(fn ($score) => $score !== null);
        $gains = $history->filter(fn ($row) => $row['pretest_score'] !== null && $row['posttest_score'] !== null)
            ->map(fn ($row) => $row['posttest_score'] - $row['pretest_score']);
        $attendanceBreakdown = [
            'hadir' => $history->where('attendance', 'Hadir')->count(),
            'izin' => $history->where('attendance', 'Izin')->count(),
            'alfa' => $history->where('attendance', 'Alfa')->count(),
        ];
        $attendanceRecorded = array_sum($attendanceBreakdown);
        $latestPretest = $history->first(fn ($row) => $row['pretest_score'] !== null);
        $latestPosttest = $history->first(fn ($row) => $row['posttest_score'] !== null);

        return [
            'student_id' => $student->student_id,
            'student_name' => $student->user?->full_name,
            'pretest_score' => $latestPretest['pretest_score'] ?? null,
            'posttest_score' => $latestPosttest['posttest_score'] ?? null,
            'attendance_count' => $attendanceRecorded,
            'kpi' => [
                'average_pretest' => $pretests->isNotEmpty() ? round((float) $pretests->avg(), 1) : null,
                'average_posttest' => $posttests->isNotEmpty() ? round((float) $posttests->avg(), 1) : null,
                'average_improvement' => $gains->isNotEmpty() ? round((float) $gains->avg(), 1) : null,
                'attendance_percentage' => $attendanceRecorded > 0 ? round(($attendanceBreakdown['hadir'] / $attendanceRecorded) * 100, 1) : 0,
            ],
            'attendance_breakdown' => $attendanceBreakdown,
            'session_history' => $history->all(),
        ];
    }
}
