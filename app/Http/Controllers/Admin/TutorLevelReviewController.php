<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherLevelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TutorLevelReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $requests = TeacherLevelRequest::with('teacher.user')
            ->where('request_status', 'pending_upgrade')->latest('created_at')->get();

        return response()->json(['data' => $requests]);
    }

    public function approve(string $id): JsonResponse
    {
        $request = TeacherLevelRequest::with('teacher.user')->where('request_status', 'pending_upgrade')->findOrFail($id);
        $teacher = $request->teacher;
        $approved = array_values(array_unique(array_merge($teacher->approved_levels ?? [], [$request->requested_level])));
        $verified = array_values(array_unique(array_merge($teacher->verified_levels ?? [], [$request->requested_level])));

        DB::transaction(function () use ($teacher, $request, $approved, $verified) {
            $teacher->update([
                'approved_levels' => $approved,
                'verified_levels' => $verified,
                'teacher_level' => implode(', ', $approved),
            ]);
            $request->update([
                'request_status' => 'approved',
                'reviewed_by_admin_id' => auth()->user()->admin?->admin_id,
                'reviewed_at' => now(),
            ]);
            DB::table('teacher_level_verifications')->updateOrInsert(
                ['teacher_id' => $teacher->teacher_id, 'jenjang' => $request->requested_level],
                ['status_verifikasi' => 'approved', 'updated_at' => now(), 'created_at' => now()],
            );
        });

        Mail::raw("Pengajuan upgrade jenjang {$request->requested_level} Anda telah disetujui.", function ($message) use ($teacher) {
            $message->to($teacher->user->email)->subject('Pengajuan jenjang disetujui - KawalBelajar');
        });

        return response()->json(['message' => 'Pengajuan upgrade jenjang disetujui.', 'data' => ['verified_levels' => $verified]]);
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $levelRequest = TeacherLevelRequest::with('teacher.user')->where('request_status', 'pending_upgrade')->findOrFail($id);
        $levelRequest->update([
            'request_status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'reviewed_by_admin_id' => auth()->user()->admin?->admin_id,
            'reviewed_at' => now(),
        ]);

        Mail::raw("Pengajuan upgrade jenjang Anda belum disetujui. Alasan: {$validated['reason']}", function ($message) use ($levelRequest) {
            $message->to($levelRequest->teacher->user->email)->subject('Status pengajuan jenjang - KawalBelajar');
        });

        return response()->json(['message' => 'Pengajuan upgrade jenjang ditolak.']);
    }
}
