<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->mergeIfMissing(['user_role' => 'siswa']);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'user_role' => ['required', 'in:siswa,tutor'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->getAuthPassword())) {
            return back()->withErrors([
                'email' => 'Email atau kata sandi tidak valid.',
            ])->onlyInput('email', 'user_role');
        }

        $roleMatches = match ($credentials['user_role']) {
            'siswa' => in_array($user->role, ['student', 'admin'], true),
            'tutor' => in_array($user->role, ['teacher', 'tutor', 'admin'], true),
        };

        if (!$roleMatches) {
            return back()->withErrors([
                'email' => 'Pilih peran yang sesuai dengan jenis akun Anda.',
            ])->onlyInput('email', 'user_role');
        }

        if (!$user->is_active) {
            $teacher = $user->teacher;
            $message = 'Akun Anda saat ini tidak aktif.';

            if ($user->suspension_reason) {
                $message = 'Akun Anda telah dibekukan sementara. Silakan hubungi Admin untuk proses klarifikasi.';
            } elseif ($teacher?->cv_status === 'rejected') {
                $message = 'Pendaftaran akun Anda ditolak. Silakan periksa email untuk melihat alasan dan instruksi perbaikan.';
            } elseif (in_array($teacher?->cv_status, ['pending', 'under_review'], true)) {
                $message = 'Akun Anda masih dalam tahap peninjauan. Kredensial akan dikirimkan setelah disetujui.';
            }

            return back()->withErrors([
                'email' => $message,
            ])->onlyInput('email', 'user_role');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        if ($user->role === 'admin') {
            return redirect()->route('admin.tutors.index');
        }

        return redirect()->route('tutor.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
