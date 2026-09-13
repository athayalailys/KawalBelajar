<?php

namespace App\Http\Controllers;

use App\Http\Requests\TutorRegistrationRequest;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        try {
            DB::transaction(function () use ($request, $validated) {
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

                // 3. Simpan Profile Teacher (status_cv = 'pending')
                Teacher::create([
                    'user_id'          => $user->user_id,
                    'cv_url'           => $cvPath,
                    'cv_status'        => 'pending',
                    'teacher_level'    => 'Junior',
                    'default_location' => $validated['domicile_address'],
                ]);
            });

            return redirect()->route('tutor.register.success')
                ->with('success', 'Pendaftaran berhasil! Berkas Anda sedang dalam proses verifikasi Admin.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses pendaftaran: ' . $e->getMessage());
        }
    }
}