<?php

use App\Http\Controllers\LandingPageController; // Controller untuk halaman utama / landing page
use App\Http\Controllers\Admin\TutorReviewController; // Controller untuk pengelolaan dan peninjauan tutor oleh admin
use App\Http\Controllers\Auth\TutorRegistrationController; // Controller untuk proses registrasi tutor
use App\Http\Controllers\Auth\LoginController; // Controller untuk proses login pengguna
use App\Http\Controllers\Student\StudentDashboardController; // Controller untuk dashboard siswa
use App\Http\Controllers\Student\BookingController;
use Illuminate\Http\Request; // Class Request untuk membaca informasi permintaan pengguna   
use Illuminate\Support\Facades\Route; // Facade Route untuk mendefinisikan URL aplikasi
use App\Http\Controllers\Classroom\ExamController;

// 2. HALAMAN UTAMA (PUBLIC)
// GET /Menampilkan landing page. Nama rute: home
Route::get('/', [LandingPageController::class, 'index'])
    ->name('home');

// 3. REGISTRASI SISWA
// Menampilkan formulir registrasi siswa secara langsung.
Route::get('/register/student', fn() => view('pages.auth.student-registration'))
    ->name('register.student');

// Catatan:
// Pada kode ini, belum ada rute POST khusus untuk registrasi siswa.
// Formulir mungkin ditangani melalui mekanisme lain atau belum erlihat pada file web.php ini.

// 4. REGISTRASI TUTOR
// Mnampilkan formulir registrasi tutor melalui controller.
Route::get('/register/tutor', [TutorRegistrationController::class, 'create'])
    ->name('register.tutor');
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
    ->name('tutor.register');
// POST /tutor/register
// Memproses registrasi tutor melalui URL alternatif.
Route::post('/tutor/register', [TutorRegistrationController::class, 'store'])
    ->name('tutor.register.store');

// 6. LOGIN PENGGUNA
// Menampilkan halaman login.
Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');

// Memproses data login melalui LoginController.
Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

// 7. FITUR SISWA: BOOKING
Route::middleware(['auth', 'active-account'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Menampilkan halaman pemesanan siswa.
        Route::get('/booking', function (Request $request) {
            // saat ini dinonaktifkan karena baris berikut dikomentari.
            // abort_unless($request->user()?->student, 403);
            return view('pages.student.booking-wizard');
        })->name('booking');

    });

// 8. DASHBOARD SISWA
// Rute ini saat ini berada di luar grup middleware auth an active-account di atas. Jadi, middleware dari grup booking tidak otomatis berlaku untuk dashboard ini.
// Rute Portal Siswa
Route::prefix('student')->name('student.')->group(function () {
    // Dashboard Siswa
    Route::get('/dashboard', [StudentDashboardController::class, 'show'])->name('dashboard');
    // 5-Step Booking Wizard
    Route::get('/student/booking', [BookingController::class, 'index'])->name('student.booking');

    // Dummy Placeholder Rute untuk fitur yang sedang dikembangkan (Mencegah error 500/Route Exception)
    Route::get('/schedule', function () {
        return view('pages.student.dashboard');
    })->name('schedule');
    //Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');

    Route::get('/classroom', function () {
        return view('pages.student.dashboard');
    })->name('classroom');
    //Route::get('/classroom', [ClassroomController::class, 'index'])->name('classroom');

    Route::get('/report', function () {
        return view('pages.student.dashboard');
    })->name('report');
    //Route::get('/report', [ReportController::class, 'index'])->name('report');

    Route::get('/parent', function () {
        return view('pages.student.dashboard');
    })->name('parent');
     //Route::get('/parent', [ParentPortalController::class, 'index'])->name('parent');
});

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
