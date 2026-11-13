<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Email atau kata sandi tidak valid.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // TAHAP 3: Validasi Akun Non-Aktif (Suspended, Rejected, Pending)
        if (!$user->is_active) {
            // Batalkan login karena akun tidak aktif
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Skenario 4: Akun Dibekukan (Suspended) karena pelanggaran SOP
            if (!is_null($user->suspension_reason)) {
                return back()->withErrors([
                    'email' => 'Akun Anda telah dibekukan sementara karena pelanggaran SOP. Silakan hubungi Admin untuk proses klarifikasi.',
                ])->onlyInput('email');
            }

            // Skenario B & C: Cek relasi data pendaftaran tutor
            // Pastikan model User.php Anda memiliki relasi: public function teacher()
            if ($user->teacher) {
                if ($user->teacher->cv_status === 'rejected') {
                    return back()->withErrors([
                        'email' => 'Pendaftaran akun Anda ditolak. Silakan periksa email untuk melihat alasan dan instruksi perbaikan.',
                    ])->onlyInput('email');
                }

                if ($user->teacher->cv_status === 'pending') {
                    return back()->withErrors([
                        'email' => 'Akun Anda masih dalam tahap peninjauan. Kredensial akan dikirimkan via email setelah disetujui.',
                    ])->onlyInput('email');
                }
            }

            // Fallback pesan jika akun non-aktif tanpa alasan spesifik
            return back()->withErrors([
                'email' => 'Akun Anda saat ini tidak aktif.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($user->role === 'student') {
            return redirect()->route('student.dashboard');
        }

        // Fallback route jika yang login adalah Tutor atau Admin
        return redirect()->intended(route('student.dashboard'));
    }
}
