<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherLevelRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TutorLevelRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        $teacher = Teacher::query()->where('user_id', $user->user_id)->firstOrFail();
        $validated = $request->validate([
            'requested_level' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'UMUM'])],
            'certificate_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $path = $request->file('certificate_file')->store('tutors/level-requests', 'public');

        try {
            TeacherLevelRequest::create([
                'teacher_id' => $teacher->teacher_id,
                'requested_level' => $validated['requested_level'],
                'supporting_document_path' => $path,
                'request_status' => 'pending_upgrade',
                'created_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return redirect()->route('tutor.dashboard')->with('status', 'Pengajuan kenaikan jenjang berhasil dikirim dan menunggu verifikasi admin.');
    }
}
