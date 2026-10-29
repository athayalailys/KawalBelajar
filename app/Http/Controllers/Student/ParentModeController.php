<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EvaluationScore;
use App\Models\StudentAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentModeController extends Controller
{
    public function verifyPin(Request $request): JsonResponse
    {
        $validated = $request->validate(['pin' => ['required', 'digits:6']]);
        $student = $request->user()?->student;

        abort_unless($student && $student->education_level !== 'UMUM', 403);

        if (!$student->parent_pin_hash || !Hash::check($validated['pin'], $student->parent_pin_hash)) {
            return response()->json([
                'message' => 'PIN orang tua tidak valid.',
                'errors' => ['pin' => ['PIN yang dimasukkan salah.']],
            ], 422);
        }

        $request->session()->put('parent_mode_student_id', $student->student_id);
        $evaluation = EvaluationScore::query()
            ->where('student_id', $student->student_id)
            ->latest('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi PIN berhasil.',
            'data' => [
                'student_id' => $student->student_id,
                'student_name' => $student->user?->full_name,
                'pretest_score' => $evaluation?->pretest_score,
                'posttest_score' => $evaluation?->posttest_score,
                'attendance_count' => StudentAttendance::query()->where('student_id', $student->student_id)->count(),
            ],
        ]);
    }
}
