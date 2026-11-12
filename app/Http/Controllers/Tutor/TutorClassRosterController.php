<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TutorClassRosterController extends Controller
{
    public function index(Request $request, string $packageId): View
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        $orders = $package->orders()
            ->whereIn('payment_status', ['settled', 'paid'])
            ->with([
                'student.user',
                'student.parentProfile.user',
                'selectedSlot',
                'learningSessions' => fn ($query) => $query->with(['evaluationScores', 'studentAttendances'])->orderByDesc('scheduled_at'),
            ])
            ->orderByDesc('order_date')
            ->get();

        $students = $orders->filter(fn ($order) => $order->student !== null)
            ->groupBy('student_id')
            ->map(fn (Collection $studentOrders) => $this->studentRow($studentOrders->first()))
            ->values();

        $slot = $orders->pluck('selectedSlot')->filter()->first()
            ?? $teacher->availabilitySlots()->where('is_active', true)->orderBy('day_of_week')->orderBy('start_time')->first();
        $occupiedSeats = $students->count();
        $capacity = (int) ($package->max_capacity ?? 0);
        $isFull = $capacity > 0 && $occupiedSeats >= $capacity;
        $module = $package->learningMaterials()->orderByDesc('created_at')->first();

        return view('pages.tutor.class-roster', [
            'teacher' => $teacher,
            'package' => $package,
            'students' => $students,
            'slot' => $slot,
            'occupiedSeats' => $occupiedSeats,
            'capacity' => $capacity,
            'isFull' => $isFull,
            'module' => $module,
        ]);
    }

    public function updateListing(Request $request, string $packageId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        $data = $request->validate(['is_open' => ['required', 'boolean']]);

        if ($data['is_open']) {
            abort_unless($package->is_active && $package->is_published, 422, 'Kelas ini belum siap ditampilkan di katalog.');
            abort_unless($package->isReadyForEnrollment(), 422, 'Lengkapi topik, target belajar, modul, dan minimal 3 soal pada setiap pertemuan sebelum membuka penerimaan.');
            $studentCount = $package->orders()->whereIn('payment_status', ['settled', 'paid'])->distinct('student_id')->count('student_id');
            abort_if($package->max_capacity && $studentCount >= $package->max_capacity, 422, 'Kapasitas kelas sudah penuh.');
        }

        $package->update(['is_open' => (bool) $data['is_open']]);

        return back()->with('status', $package->is_open ? 'Penerimaan kelas dibuka.' : 'Penerimaan kelas ditutup. Kelas tidak lagi tampil di katalog siswa.');
    }

    public function exportCsv(Request $request, string $packageId): StreamedResponse
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        $orders = $package->orders()
            ->whereIn('payment_status', ['settled', 'paid'])
            ->with(['student.user', 'student.parentProfile.user', 'learningSessions.evaluationScores', 'learningSessions.studentAttendances'])
            ->get();
        $students = $orders->filter(fn ($order) => $order->student !== null)
            ->groupBy('student_id')
            ->map(fn (Collection $studentOrders) => $this->studentRow($studentOrders->first()))
            ->values();

        return response()->streamDownload(function () use ($students) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['No', 'Nama Siswa', 'Telepon Wali', 'Nama Wali', 'Pre-Test', 'Post-Test', 'Progres (%)', 'Presensi Terakhir']);
            foreach ($students as $index => $row) {
                fputcsv($output, [
                    $index + 1,
                    $row['name'],
                    $row['guardian_phone'],
                    $row['guardian_name'],
                    $row['pretest'] ?? '',
                    $row['posttest'] ?? '',
                    $row['progress'] === null ? '' : $row['progress'].'%',
                    $row['attendance_label'],
                ]);
            }
            fclose($output);
        }, 'roster-'.str($package->package_name)->slug().'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function studentRow(object $order): array
    {
        /** @var Student $student */
        $student = $order->student;
        $sessions = $order->learningSessions->sortByDesc('scheduled_at')->values();
        $scores = $sessions->flatMap(fn ($session) => $session->evaluationScores->where('student_id', $student->student_id));
        $pretest = $scores->first(fn ($score) => $score->pretest_score !== null)?->pretest_score;
        $posttest = $scores->first(fn ($score) => $score->posttest_score !== null)?->posttest_score;
        $score = $scores->first(fn ($item) => $item->pretest_score !== null || $item->posttest_score !== null);
        $progress = $pretest !== null && $posttest !== null
            ? ($pretest === 0 ? null : round((($posttest - $pretest) / $pretest) * 100, 1))
            : ($score?->progress_percentage !== null ? round((float) $score->progress_percentage, 1) : null);

        $attendance = $sessions->flatMap(fn ($session) => $session->studentAttendances->where('student_id', $student->student_id))
            ->sortByDesc('attendance_time')
            ->first();
        $attendanceLabel = match ($attendance?->status) {
            'present' => 'Hadir', 'absent' => 'Tidak Hadir', 'sick' => 'Izin Sakit', 'permission' => 'Izin', default => 'Belum Dicatat',
        };
        $parent = $student->parentProfile;

        return [
            'student' => $student,
            'name' => $student->user?->full_name ?? 'Nama siswa belum tersedia',
            'guardian_name' => $parent?->user?->full_name ?? $student->parent_name ?? 'Wali belum dicatat',
            'guardian_phone' => $parent?->phone_number ?? $student->parent_phone ?? '',
            'pretest' => $pretest,
            'posttest' => $posttest,
            'progress' => $progress,
            'attendance_label' => $attendanceLabel,
            'attendance_time' => $attendance?->attendance_time,
        ];
    }

    private function packageForTeacher(Teacher $teacher, string $packageId): ModulePackage
    {
        return ModulePackage::query()->where('teacher_id', $teacher->teacher_id)->whereKey($packageId)->firstOrFail();
    }

    private function teacherFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        return Teacher::query()->with('user')->where('user_id', $user->user_id)->firstOrFail();
    }
}
