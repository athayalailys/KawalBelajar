<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\LearningSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TutorSessionMeetingLinkController extends Controller
{
    public function update(Request $request, string $sessionId): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        $session = LearningSession::query()->where('session_id', $sessionId)->firstOrFail();
        abort_unless($session->teacher_id === $user->teacher?->teacher_id, 403);

        $validated = $request->validate([
            'zoom_meeting_url' => ['required', 'url', 'max:255'],
        ]);
        $session->update(['zoom_meeting_url' => $validated['zoom_meeting_url']]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tautan ruang belajar berhasil diperbarui.',
                'data' => ['session_id' => $session->session_id, 'zoom_meeting_url' => $session->zoom_meeting_url],
            ]);
        }

        return back()->with('status', 'Tautan ruang belajar berhasil diperbarui.');
    }
}
