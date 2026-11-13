<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\Admin\TutorVerificationService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Mail\TutorRejectedMail;
use App\Mail\TutorApprovedMail;

class TutorReviewController extends Controller
{
    protected TutorVerificationService $verificationService;

    public function __construct(TutorVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

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
            'documents' => [
                'cv' => $teacher->cv_url ? route('admin.tutors.document', ['teacher' => $teacher->teacher_id, 'type' => 'cv']) : null,
                'identity' => $teacher->identity_document_url ? route('admin.tutors.document', ['teacher' => $teacher->teacher_id, 'type' => 'identity']) : null,
                'transcript' => $teacher->transcript_url ? route('admin.tutors.document', ['teacher' => $teacher->teacher_id, 'type' => 'transcript']) : null,
                'certificate' => $teacher->certificate_url ? route('admin.tutors.document', ['teacher' => $teacher->teacher_id, 'type' => 'certificate']) : null,
            ],
            'submitted_at' => $teacher->created_at,
        ]));
    }

    public function suspend(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($teacher->cv_status === 'approved', 409, 'Hanya tutor yang telah disetujui yang dapat dibekukan.');

        $user = $teacher->user;

        $user->update([
            'is_active' => false,
            'suspension_reason' => $validated['reason']
        ]);

        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->user_id)->delete();
        }

        return response()->json([
            'message' => 'Akun tutor berhasil dibekukan dan sesi aktifnya telah dicabut.'
        ]);
    }

    public function reinstate(Teacher $teacher): JsonResponse
    {
        abort_unless($teacher->cv_status === 'approved', 409, 'Hanya tutor yang telah disetujui yang dapat diaktifkan kembali.');
        $teacher->user()->update(['is_active' => true, 'suspension_reason' => null]);

        return response()->json(['message' => 'Akun tutor diaktifkan kembali.']);
    }

    public function downloadDocument(Teacher $teacher, string $type): StreamedResponse
    {
        $path = $this->verificationService->getDocumentPath($teacher, $type);
        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        return $publicDisk->download($path);
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
                'verified_levels' => $approved,
                'teacher_level' => implode(', ', $approved),
                'review_note' => null,
            ]);
            $teacher->user->update([
                'username' => $username,
                'password_hash' => Hash::make($password),
                'is_active' => true,
                'suspension_reason' => null,
            ]);

            DB::table('teacher_level_verifications')
                ->where('teacher_id', $teacher->teacher_id)
                ->whereIn('jenjang', $approved)
                ->update(['status_verifikasi' => 'approved', 'updated_at' => now()]);

            return [
                'username' => $username,
                'password' => $password, // Tangkap password untuk dikirim ke email
                'approvedLevels' => $approved,
            ];
        });

        // PENGIRIMAN EMAIL DI LUAR DB TRANSACTION
        Mail::to($teacher->user->email)->send(
            new TutorApprovedMail(
                $teacher,
                $credentials['approvedLevels'],
                $credentials['username'],
                $credentials['password']
            )
        );

        return response()->json(['message' => 'Tutor disetujui dan kredensial dikirim melalui email.', 'username' => $credentials['username']]);
    }

    public function reject(Request $request, Teacher $teacher): JsonResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($teacher->cv_status === 'pending', 409, 'Berkas tutor ini sudah ditinjau.');

        DB::transaction(function () use ($teacher, $validated) {
            $teacher->update(['cv_status' => 'rejected', 'review_note' => $validated['reason']]);
            $teacher->user()->update(['is_active' => false]);
            DB::table('teacher_level_verifications')
                ->where('teacher_id', $teacher->teacher_id)
                ->update(['status_verifikasi' => 'rejected', 'updated_at' => now()]);
        });

        // PENGIRIMAN EMAIL PENOLAKAN
        Mail::to($teacher->user->email)->send(
            new TutorRejectedMail($teacher, $validated['reason'])
        );

        return response()->json(['message' => 'Pengajuan tutor ditolak dan email instruksi telah dikirim.']);
    }
}
