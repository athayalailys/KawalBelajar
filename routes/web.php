<?php

use App\Http\Controllers\LandingPageController; // Controller untuk halaman utama / landing page
use App\Http\Controllers\Admin\TutorReviewController; // Controller untuk pengelolaan dan peninjauan tutor oleh admin
use App\Http\Controllers\Auth\TutorRegistrationController; // Controller untuk proses registrasi tutor
use App\Http\Controllers\Auth\LoginController; // Controller untuk proses login pengguna
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Tutor\TutorLandingController;
use App\Http\Controllers\Student\StudentDashboardController; // Controller untuk dashboard siswa
use App\Http\Controllers\Student\BookingController;
use App\Http\Controllers\Student\ClassroomPageController;
use Illuminate\Http\Request; // Class Request untuk membaca informasi permintaan pengguna   
use Illuminate\Support\Facades\Route; // Facade Route untuk mendefinisikan URL aplikasi
use App\Http\Controllers\Classroom\ExamController;

// 2. HALAMAN UTAMA (PUBLIC)
// GET /Menampilkan landing page. Nama rute: home
Route::get('/', [LandingPageController::class, 'index'])
    ->name('home');
Route::get('/menjadi-tutor', [TutorLandingController::class, 'index'])
    ->name('tutor.landing');

// 3. REGISTRASI SISWA
// Menampilkan formulir registrasi siswa secara langsung.
Route::get('/register/student', function (Request $request) {
    $returnTo = $request->query('return_to') === 'login' ? 'login' : 'home';

    return view('pages.auth.student-registration', compact('returnTo'));
})->name('register.student');
Route::get('/register', function (Request $request) {
    $returnTo = $request->query('return_to') === 'login' ? 'login' : 'home';

    return redirect()->route('register.student', ['return_to' => $returnTo]);
})->name('register');

// Catatan:
// Pada kode ini, belum ada rute POST khusus untuk registrasi siswa.
// Formulir mungkin ditangani melalui mekanisme lain atau belum erlihat pada file web.php ini.

// 4. REGISTRASI TUTOR
// Mnampilkan formulir registrasi tutor melalui controller.
Route::get('/register/tutor', [TutorRegistrationController::class, 'create'])
    ->name('tutor.register');
// POST /register/tutor
// Memproses data yang dikirim dari formulir registrasi tutor.
Route::post('/register/tutor', [TutorRegistrationController::class, 'store'])
    ->name('register.tutor.store');

// 5. ALIAS / PENGALIHAN URL REGISTRASI
// Mengalihkan URL lama /student/register ke /register/student.
Route::redirect('/student/register', '/register/student', 301)
    ->name('student.register');
// Mengalihkan URL lama /tutor/register ke /register/tutor.
Route::redirect('/tutor/register', '/register/tutor', 301)
    ->name('register.tutor');
// POST /tutor/register
// Memproses registrasi tutor melalui URL alternatif.
Route::post('/tutor/register', [TutorRegistrationController::class, 'store'])
    ->name('tutor.register.store');

// 6. LOGIN PENGGUNA
// Menampilkan halaman login.
Route::get('/login', [LoginController::class, 'create'])->name('login');

// Memproses data login melalui LoginController.
Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', [PasswordResetController::class, 'create'])
    ->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])
    ->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])
    ->name('password.update');

// Portal siswa
Route::middleware(['auth', 'active-account', 'student-account'])
    ->prefix('siswa')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'show'])->name('dashboard');
        Route::get('/katalog', fn () => view('pages.student.catalog'))->name('catalog');
        Route::get('/booking', [BookingController::class, 'index'])->name('booking');
        Route::get('/schedule', fn () => view('pages.student.schedule'))->name('schedule');
        Route::get('/classroom', [ClassroomPageController::class, 'index'])->name('classroom');
        Route::get('/report', fn () => view('pages.student.report'))->name('report');
        Route::get('/parent', function (Request $request) {
            $educationLevel = strtoupper(trim((string) $request->user()?->student?->education_level));

            return view('pages.student.parent', [
                'parentModeAvailable' => in_array($educationLevel, ['SD', 'SMP', 'SMA'], true),
            ]);
        })->name('parent');
    });

// Pertahankan URL portal lama sambil mengarahkan ke URL berbahasa Indonesia.
Route::get('/student/dashboard', fn () => redirect()->route('student.dashboard', [], 301))->name('student.dashboard.legacy');
Route::get('/student/catalog', fn () => redirect()->route('student.catalog', [], 301))->name('student.catalog.legacy');
Route::get('/student/booking', fn () => redirect()->route('student.booking', [], 301))->name('student.booking.legacy');
Route::get('/student/schedule', fn () => redirect()->route('student.schedule', [], 301))->name('student.schedule.legacy');
Route::get('/student/classroom', function (Request $request) {
    return redirect()->route('student.classroom', $request->query(), 301);
})->name('student.classroom.legacy');
Route::get('/student/report', fn () => redirect()->route('student.report', [], 301))->name('student.report.legacy');
Route::get('/student/parent', fn () => redirect()->route('student.parent', [], 301))->name('student.parent.legacy');

Route::middleware('auth:sanctum')->prefix('classroom')->group(function () {
    // US 3.2 Endpoint
    Route::post('/session/{sessionId}/submit-exam', [ExamController::class, 'submitExam']);
});

// 9. ADMIN: PENGELOLAAN DAN PENINJAUAN TUTOR
// Prefix URL: /admin/api/tutors
// Prefix nama rute: admin.tutors.
Route::middleware(['auth', 'active-account', 'admin'])
    ->prefix('admin/api/tutors')
    ->name('admin.tutors.')
    ->group(function () {

        // Menampilkan daftar tutor yang perlu ditinjau.
        // Nama rute lengkap: admin.tutors.index
        Route::get('/', [TutorReviewController::class, 'index'])
            ->name('index');

        // Menyetujui tutor yang dipilih.
        Route::post('/{teacher}/approve', [TutorReviewController::class, 'approve'])
            ->name('approve');

        // Menolak tutor yang dipilih.
        Route::post('/{teacher}/reject', [TutorReviewController::class, 'reject'])
            ->name('reject');

        // Menangguhkan tutor yang dipilih.
        Route::post('/{teacher}/suspend', [TutorReviewController::class, 'suspend'])
            ->name('suspend');

        // Mengaktifkan kembali tutor yang ditangguhkan.
        Route::post('/{teacher}/reinstate', [TutorReviewController::class, 'reinstate'])
            ->name('reinstate');

    });
