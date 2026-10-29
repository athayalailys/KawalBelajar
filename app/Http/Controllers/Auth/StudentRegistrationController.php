<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRegistrationRequest;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentRegistrationController extends Controller
{
    public function register(StudentRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $response = DB::transaction(function () use ($validated) {
            $user = User::create([
                'user_id'       => (string) Str::uuid(),
                'full_name'     => $validated['full_name'],
                'email'         => $validated['email'],
                'phone_number'  => $validated['phone_number'],
                'password_hash' => Hash::make($validated['password']),
                'role'          => 'student',
                'is_active'     => true,
            ]);

            $student = Student::create([
                'student_id'      => (string) Str::uuid(),
                'user_id'         => $user->user_id,
                'education_level' => $validated['education_level'],
                'saved_address'   => $validated['saved_address'] ?? null,
                'landmark'        => $validated['landmark'] ?? null,
                'latitude'        => $validated['latitude'] ?? null,
                'longitude'       => $validated['longitude'] ?? null,
                'parent_name'     => $validated['parent_name'] ?? null,
                'parent_phone'    => $validated['parent_phone'] ?? null,
                'parent_pin_hash' => isset($validated['parent_pin']) ? Hash::make($validated['parent_pin']) : null,
            ]);

            // Menentukan visibilitas Parent Mode sesuai aturan Skenario 3 (SD, SMP, SMA = True, UMUM = False)
            $isParentModeVisible = in_array($student->education_level, ['SD', 'SMP', 'SMA']);

            return [
                'user_id'              => $user->user_id,
                'student_id'           => $student->student_id,
                'full_name'            => $user->full_name,
                'email'                => $user->email,
                'education_level'      => $student->education_level,
                'has_parent_protection' => !empty($student->parent_pin_hash),
                'parent_mode_visible'  => $isParentModeVisible,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Registrasi pembelajar berhasil dilakukan.',
            'data'    => $response
        ], 201);
    }
}
