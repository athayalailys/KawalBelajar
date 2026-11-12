<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\ClockInRequest;
use App\Http\Requests\Classroom\ClockOutRequest;
use App\Models\LearningSession;
use App\Services\Classroom\AttendanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    public function navigation(string $sessionId): \Illuminate\Http\JsonResponse
    {
        $session = LearningSession::query()
            ->with('order')
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $user = request()->user();
        abort_unless($user, 403);

        $studentId = $user->student?->student_id;
        $teacherId = $user->teacher?->teacher_id;
        $isStudentOwner = $studentId !== null
            && ($session->student_id === $studentId || $session->order?->student_id === $studentId);
        $isAssignedTeacher = $teacherId !== null && $session->teacher_id === $teacherId;

        abort_unless($isStudentOwner || $isAssignedTeacher, 403);

        $googleMapsUrl = sprintf(
            'https://www.google.com/maps/dir/?api=1&destination=%f,%f',
            $session->latitude,
            $session->longitude
        );

        return response()->json([
            'message' => 'Detail rute navigasi berhasil dimuat.',
            'data' => [
                'session_id' => $session->session_id,
                'address' => $session->onsite_address ?? $session->student_address,
                'landmark' => $session->address_landmark,
                'latitude' => (float) $session->latitude,
                'longitude' => (float) $session->longitude,
                'google_maps_url' => $googleMapsUrl,
            ],
        ], 200);
    }


    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $session = LearningSession::where('session_id', $request->validated('session_id'))->firstOrFail();
        $this->authorize('clockIn', $session);

        $teacherId = $request->user()->teacher?->teacher_id ?? (string) $request->user()->id;

        try {
            $updatedSession = $this->attendanceService->clockIn(
                $session->session_id,
                $teacherId,
                $request->latitude,
                $request->longitude
            );

            return response()->json([
                'message' => 'Clock-In berhasil dicatat.',
                'data' => [
                    'session_id' => $updatedSession->session_id,
                    'status' => $updatedSession->status,
                    'session_status' => $updatedSession->session_status,
                    'status_label' => 'Dalam Pembelajaran',
                    'absensi_masuk_guru' => $updatedSession->absensi_masuk_guru?->toIso8601String(),
                ]
            ], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $sessionId = $request->validated('session_id') ?? $request->validated('learning_session_id');
        $session = LearningSession::where('session_id', $sessionId)->firstOrFail();
        $this->authorize('clockOut', $session);

        $teacherId = $request->user()->teacher?->teacher_id ?? (string) $request->user()->id;

        try {
            $updatedSession = $this->attendanceService->clockOut(
                $session->session_id,
                $teacherId
            );

            return response()->json([
                'message' => 'Clock-Out berhasil dicatat.',
                'data' => [
                    'session_id' => $updatedSession->session_id,
                    'status' => $updatedSession->status,
                    'absensi_keluar_guru' => $updatedSession->absensi_keluar_guru?->toIso8601String(),
                ]
            ], 200);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
