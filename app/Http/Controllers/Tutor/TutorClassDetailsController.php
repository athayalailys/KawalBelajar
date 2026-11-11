<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\DigitalReport;
use App\Models\EvaluationScore;
use App\Models\LearningSession;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorClassDetailsController extends Controller
{
    public function index(Request $request): View
    {
        $tutor = $this->tutorFor($request);
        $sessions = $tutor->learningSessions()
            ->with([
                'order.package',
                'order.student.user',
                'order.student.parentProfile.user',
                'order.selectedSlot',
                'order.squad.hostStudent.user',
                'material',
                'evaluationScores',
                'studentAttendances',
            ])
            ->whereNotNull('scheduled_at')
            ->whereHas('order', fn ($query) => $query->whereIn('payment_status', ['settled', 'paid']))
            ->where(fn ($query) => $query->whereNull('session_status')->orWhere('session_status', '!=', 'completed'))
            ->where(fn ($query) => $query->whereNull('status')->orWhere('status', '!=', 'completed'))
            ->orderBy('scheduled_at')
            ->get();

        $session = $sessions->first(fn ($item) => $item->session_status === 'in_progress' || $item->status === 'in_progress')
            ?? $sessions->first(fn ($item) => $item->scheduled_at?->isFuture())
            ?? $sessions->sortByDesc('scheduled_at')->first();
        $participants = collect();
        $package = $session?->order?->package;
        $slot = $session?->order?->selectedSlot;
        $registeredCount = 0;
        $address = null;
        $landmark = null;
        $mapsUrl = null;

        if ($session && $package) {
            $student = $session->student ?? $session->order?->student ?? $session->order?->squad?->hostStudent;
            if ($student) {
                $evaluation = $session->evaluationScores->firstWhere('student_id', $student->student_id);
                $attendance = $session->studentAttendances->firstWhere('student_id', $student->student_id);
                $participants->push([
                    'student' => $student,
                    'role' => 'Siswa Terdaftar',
                    'attendance' => $attendance?->status,
                    'pretest' => $evaluation?->pretest_score,
                    'posttest' => $evaluation?->posttest_score,
                    'progress' => $evaluation?->progress_percentage,
                ]);
            }

            $registeredCount = $participants->count();
            $address = $session->onsite_address ?? $session->student_address ?? $student?->saved_address ?? $student?->address;
            $landmark = $session->address_landmark ?? $student?->address_landmark ?? $student?->landmark;
            $mapsUrl = $session->latitude && $session->longitude
                ? 'https://www.google.com/maps/dir/?api=1&destination='.$session->latitude.','.$session->longitude
                : ($address ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address) : null);
        }

        return view('pages.tutor.teaching-session', [
            'tutor' => $tutor,
            'session' => $session,
            'package' => $package,
            'slot' => $slot,
            'participants' => $participants,
            'registeredCount' => $registeredCount,
            'address' => $address,
            'landmark' => $landmark,
            'mapsUrl' => $mapsUrl,
        ]);
    }

    public function saveEvaluation(Request $request, string $sessionId): RedirectResponse
    {
        $tutor = $this->tutorFor($request);
        $session = $tutor->learningSessions()->with(['order.package'])->whereKey($sessionId)->firstOrFail();
        abort_unless(in_array($session->order?->payment_status, ['settled', 'paid'], true), 404);
        $validated = $request->validate([
            'students' => ['required', 'array', 'min:1'],
            'students.*.attendance' => ['nullable', 'in:present,permission,absent'],
            'students.*.posttest_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'teacher_notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $session, $tutor) {
            foreach ($validated['students'] as $studentId => $row) {
                abort_unless($session->order?->student_id === $studentId, 422, 'Data siswa tidak sesuai dengan sesi ini.');

                if (!empty($row['attendance'])) {
                    StudentAttendance::updateOrCreate(
                        ['session_id' => $session->session_id, 'student_id' => $studentId],
                        ['attendance_time' => now(), 'status' => $row['attendance']],
                    );
                }

                $posttestScore = $row['posttest_score'] ?? null;
                if ($posttestScore !== null && $posttestScore !== '') {
                    $evaluation = EvaluationScore::firstOrNew([
                        'session_id' => $session->session_id,
                        'student_id' => $studentId,
                    ]);
                    $evaluation->posttest_score = (int) $posttestScore;
                    if ($evaluation->pretest_score !== null) {
                        $evaluation->progress_percentage = $evaluation->pretest_score === 0
                            ? null
                            : round((($evaluation->posttest_score - $evaluation->pretest_score) / $evaluation->pretest_score) * 100, 2);
                    }
                    $evaluation->teacher_notes = $validated['teacher_notes'];
                    $evaluation->created_at ??= now();
                    $evaluation->save();
                }

                $studentSessionIds = LearningSession::query()
                    ->where('student_id', $studentId)
                    ->whereHas('order', fn ($query) => $query->where('package_id', $session->order->package_id)->whereIn('payment_status', ['settled', 'paid']))
                    ->pluck('session_id');
                $scores = EvaluationScore::query()->where('student_id', $studentId)->whereIn('session_id', $studentSessionIds)->get();
                $attendanceRecords = StudentAttendance::query()->where('student_id', $studentId)->whereIn('session_id', $studentSessionIds)->get();
                $presentCount = $attendanceRecords->where('status', 'present')->count();

                DigitalReport::updateOrCreate(
                    ['student_id' => $studentId, 'package_id' => $session->order->package_id, 'teacher_id' => $tutor->teacher_id],
                    [
                        'average_pretest_score' => $scores->whereNotNull('pretest_score')->avg('pretest_score'),
                        'average_posttest_score' => $scores->whereNotNull('posttest_score')->avg('posttest_score'),
                        'progress_percentage' => $scores->whereNotNull('progress_percentage')->avg('progress_percentage'),
                        'attendance_percentage' => $studentSessionIds->isEmpty() ? null : round(($presentCount / $studentSessionIds->count()) * 100, 2),
                        'teacher_notes' => $validated['teacher_notes'],
                        'report_status' => 'published',
                        'generated_at' => now(),
                    ],
                );
            }

            $session->update([
                'absensi_keluar_guru' => $session->absensi_keluar_guru ?? now(),
                'teacher_clock_out' => $session->teacher_clock_out ?? now(),
                'session_status' => 'completed',
                'status' => 'completed',
            ]);
        });

        return redirect()->route('tutor.class-details')->with('status', 'Evaluasi tersimpan dan sesi mengajar telah ditutup.');
    }

    public function complete(Request $request, string $sessionId): RedirectResponse
    {
        $tutor = $this->tutorFor($request);
        $session = $tutor->learningSessions()->whereKey($sessionId)->firstOrFail();

        $session->update([
            'session_status' => 'completed',
            'status' => 'completed',
            'absensi_keluar_guru' => $session->absensi_keluar_guru ?? now(),
            'teacher_clock_out' => $session->teacher_clock_out ?? now(),
        ]);

        return redirect()
            ->route('tutor.dashboard', ['agenda' => 1])
            ->with('status', 'Sesi telah ditandai selesai.');
    }

    private function tutorFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'teacher', 403);

        return Teacher::query()
            ->with('user')
            ->where('user_id', $user->user_id)
            ->firstOrFail();
    }
}
