<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TutorReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $applications = Teacher::with('user')->where('cv_status', 'pending')->latest()->get();

        return response()->json($applications->map(fn (Teacher $teacher) => [
            'teacher_id' => $teacher->teacher_id,
            'applicant' => [
                'full_name' => $teacher->user->full_name,
                'email' => $teacher->user->email,
                'phone_number' => $teacher->user->phone_number,
            ],
            'university' => $teacher->university,
            'study_program' => $teacher->study_program,
            'semester' => $teacher->semester,
            'focus_subject' => $teacher->focus_subject,
            'requested_levels' => $teacher->requested_levels,
            'default_location' => $teacher->default_location,
            'video_link' => $teacher->video_link,
            'documents' => collect([
                'cv' => $teacher->cv_url,
                'identity' => $teacher->identity_document_url,
                'transcript' => $teacher->transcript_url,
                'certificate' => $teacher->certificate_url,
            ])->map(fn ($path) => $path ? Storage::disk('public')->url($path) : null),
            'submitted_at' => $teacher->created_at,
        ]));
    }

    public function approve(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate([
            'approved_levels' => ['required', 'array', 'min:1'],
            'approved_levels.*' => ['required', 'distinct', 'in:SD,SMP,SMA,UMUM'],
        ]);

        $credentials = DB::transaction(function () use ($teacher, $validated) {
            $teacher = Teacher::with('user')->lockForUpdate()->findOrFail($teacher->teacher_id);
            if ($teacher->cv_status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Berkas tutor ini sudah ditinjau.']);
            }

            $requested = $teacher->requested_levels ?? [];
            $approved = $validated['approved_levels'];
            if (array_diff($approved, $requested)) {
                throw ValidationException::withMessages(['approved_levels' => 'Jenjang yang disetujui harus berasal dari jenjang pengajuan.']);
            }

            $base = Str::slug($teacher->user->full_name, '');
            $base = substr($base ?: 'tutor', 0, 16);
            do {
                $username = $base.Str::lower(Str::random(4));
            } while (DB::table('users')->where('username', $username)->exists());

            $password = Str::password(14);
            $teacher->update([
                'cv_status' => 'approved',
                'approved_levels' => $approved,
                'teacher_level' => implode(', ', $approved),
                'review_note' => null,
            ]);
            $teacher->user->update([
                'username' => $username,
                'password_hash' => Hash::make($password),
                'is_active' => true,
                'suspension_reason' => null,
            ]);

            Mail::raw(
                "Halo {$teacher->user->full_name},\n\nBerkas Anda telah disetujui. Jenjang yang disetujui: ".implode(', ', $approved).".\n\nUsername: {$username}\nPassword sementara: {$password}\n\nSegera masuk dan ganti password Anda.",
                function ($message) use ($teacher) {
                    $message->to($teacher->user->email)->subject('Akun tutor Kawal Belajar disetujui');
                }
            );

            return ['username' => $username];
        });

        return response()->json(['message' => 'Tutor disetujui dan kredensial dikirim melalui email.', ...$credentials]);
    }

    public function reject(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($teacher->cv_status === 'pending', 409, 'Berkas tutor ini sudah ditinjau.');

        DB::transaction(function () use ($teacher, $validated) {
            $teacher->update(['cv_status' => 'rejected', 'review_note' => $validated['reason']]);
            $teacher->user()->update(['is_active' => false]);
        });

        return response()->json(['message' => 'Pengajuan tutor ditolak.']);
    }

    public function suspend(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($teacher->cv_status === 'approved', 409, 'Hanya tutor yang telah disetujui yang dapat dibekukan.');
        $teacher->user()->update(['is_active' => false, 'suspension_reason' => $validated['reason']]);

        return response()->json(['message' => 'Akun tutor dibekukan. Sesi aktif akan ditolak pada permintaan berikutnya.']);
    }

    public function reinstate(Teacher $teacher): JsonResponse
    {
        abort_unless($teacher->cv_status === 'approved', 409, 'Hanya tutor yang telah disetujui yang dapat diaktifkan kembali.');
        $teacher->user()->update(['is_active' => true, 'suspension_reason' => null]);

        return response()->json(['message' => 'Akun tutor diaktifkan kembali.']);
    }
}
