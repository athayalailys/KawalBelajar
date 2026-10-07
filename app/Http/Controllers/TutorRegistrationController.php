<?php

namespace App\Http\Controllers;

use App\Http\Requests\TutorRegistrationRequest;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TutorRegistrationController extends Controller
{
    /**
     * Tampilan Form Registrasi (Step 1 & Step 2)
     */
    public function create(Request $request)
    {
        $step = (int) $request->get('step', 1);
        
        return view('pages.tutor-registration', compact('step'));
    }

    /**
     * Eksekusi Pendaftaran Tutor
     */
    public function store(TutorRegistrationRequest $request)
    {
        $validated = $request->validated();

        $storedFiles = [];

        try {
            $teacher = DB::transaction(function () use ($request, $validated, &$storedFiles) {
                // 1. Simpan Akun User Awal
                $user = User::create([
                    'email'         => $validated['email'],
                    'password_hash' => Hash::make(Str::random(16)), // Temporary Password
                    'full_name'     => $validated['full_name'],
                    'phone_number'  => $validated['phone_number'],
                    'role'          => 'teacher',
                    'is_active'     => false, // Nonaktif sampai diverifikasi admin
                ]);

                // 2. Upload Berkas ke Storage
                $cvPath = $request->file('cv_file')->store('tutors/cv', 'public');
                $identityPath = $request->file('ktp_ktm_file')->store('tutors/identity', 'public');
                $transcriptPath = $request->file('transcript_file')->store('tutors/transcripts', 'public');
                $certificatePath = $request->file('certificate_file')?->store('tutors/certificates', 'public');
                $storedFiles = array_filter([$cvPath, $identityPath, $transcriptPath, $certificatePath]);

                // 3. Simpan Profile Teacher (status_cv = 'pending')
                $teacher = Teacher::create([
                    'user_id'          => $user->user_id,
                    'cv_url'           => $cvPath,
                    'identity_document_url' => $identityPath,
                    'transcript_url' => $transcriptPath,
                    'certificate_url' => $certificatePath,
                    'video_link' => $validated['video_link'] ?? null,
                    'university' => $validated['university'],
                    'study_program' => $validated['study_program'],
                    'semester' => $validated['semester'],
                    'focus_subject' => $validated['focus_subject'],
                    'requested_levels' => $validated['selected_levels'],
                    'cv_status'        => 'pending',
                    'teacher_level'    => 'Junior',
                    'default_location' => $validated['domicile_address'],
                ]);

                DB::table('teacher_level_verifications')->insert(collect($validated['selected_levels'])
                    ->map(fn (string $level) => [
                        'teacher_id' => $teacher->teacher_id,
                        'jenjang' => $level,
                        'status_verifikasi' => 'menunggu_review',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])->all());

                return $teacher;
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pendaftaran berhasil. Berkas sedang menunggu verifikasi admin.',
                    'data' => [
                        'teacher_id' => $teacher->teacher_id,
                        'status_cv' => $teacher->cv_status,
                        'jenjang' => DB::table('teacher_level_verifications')
                            ->where('teacher_id', $teacher->teacher_id)
                            ->get(['jenjang', 'status_verifikasi']),
                    ],
                ], 201);
            }

            return redirect()->route('tutor.register')
                ->with('step', 1)
                ->with('success', 'Pendaftaran berhasil! Berkas Anda sedang dalam proses verifikasi Admin.');

        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedFiles);
            Log::error('Tutor registration failed.', ['exception' => $e]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pendaftaran gagal diproses. Silakan coba lagi.'], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pendaftaran. Silakan coba lagi.');
        }
    }
}
