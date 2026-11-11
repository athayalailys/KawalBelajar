<?php

use App\Http\Controllers\LandingPageController; // Controller untuk halaman utama / landing page
use App\Http\Controllers\Admin\TutorReviewController; // Controller untuk pengelolaan dan peninjauan tutor oleh admin
use App\Http\Controllers\Auth\TutorRegistrationController; // Controller untuk proses registrasi tutor
use App\Http\Controllers\Auth\LoginController; // Controller untuk proses login pengguna
use App\Http\Controllers\Student\StudentDashboardController; // Controller untuk dashboard siswa
use App\Http\Controllers\Student\BookingController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Tutor\TutorClassDetailsController;
use App\Http\Controllers\Tutor\TutorClassesController;
use App\Http\Controllers\Tutor\TutorTeachingRoomController;
use App\Http\Controllers\Tutor\TutorAvailabilityController;
use App\Http\Controllers\Tutor\TutorClassRosterController;
use App\Http\Controllers\Tutor\TutorClassMaterialsController;
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

// 8. DASHBOARD TUTOR
Route::middleware(['auth', 'active-account'])
    ->prefix('tutor')
    ->name('tutor.')
    ->group(function () {
        Route::get('/dashboard', [TutorDashboardController::class, 'show'])
            ->name('dashboard');
        Route::get('/classes', [TutorClassesController::class, 'index'])
            ->name('classes');
        Route::get('/classes/create', [TutorClassesController::class, 'create'])
            ->name('classes.create');
        Route::post('/classes', [TutorClassesController::class, 'store'])
            ->name('classes.store');
        Route::get('/classes/{packageId}/materials', [TutorClassMaterialsController::class, 'index'])
            ->name('classes.materials');
        Route::post('/classes/{packageId}/materials/sessions/{sessionNumber}', [TutorClassMaterialsController::class, 'saveSession'])
            ->whereNumber('sessionNumber')
            ->name('classes.materials.session.save');
        Route::post('/classes/{packageId}/materials/publish', [TutorClassMaterialsController::class, 'publish'])
            ->name('classes.materials.publish');
        Route::get('/classes/{packageId}/roster', [TutorClassRosterController::class, 'index'])
            ->name('classes.roster');
        Route::patch('/classes/{packageId}/listing', [TutorClassRosterController::class, 'updateListing'])
            ->name('classes.listing');
        Route::get('/classes/{packageId}/roster/export', [TutorClassRosterController::class, 'exportCsv'])
            ->name('classes.roster.export');
        Route::get('/availability', [TutorAvailabilityController::class, 'index'])
            ->name('availability');
        Route::post('/availability', [TutorAvailabilityController::class, 'store'])
            ->name('availability.store');
        Route::patch('/availability/{slotId}', [TutorAvailabilityController::class, 'toggle'])
            ->name('availability.toggle');
        Route::delete('/availability/{slotId}', [TutorAvailabilityController::class, 'destroy'])
            ->name('availability.destroy');
        Route::get('/teaching-room', [TutorTeachingRoomController::class, 'index'])
            ->name('classroom');
        Route::get('/teaching-room/classes/{packageId}', [TutorTeachingRoomController::class, 'show'])
            ->name('classroom.show');
        Route::get('/teaching-room/classes/{packageId}/students/{studentId}', [TutorTeachingRoomController::class, 'show'])
            ->name('classroom.student');
        Route::post('/teaching-room/classes/{packageId}/students/{studentId}/attendance', [TutorTeachingRoomController::class, 'updateAttendance'])
            ->name('classroom.attendance');
        Route::post('/teaching-room/classes/{packageId}/students/{studentId}/evaluation', [TutorTeachingRoomController::class, 'saveEvaluation'])
            ->name('classroom.evaluation');
        Route::get('/classes/details', [TutorClassDetailsController::class, 'index'])
            ->name('class-details');
        Route::post('/sessions/{sessionId}/complete', [TutorClassDetailsController::class, 'complete'])
            ->name('sessions.complete');
        Route::post('/sessions/{sessionId}/evaluation', [TutorClassDetailsController::class, 'saveEvaluation'])
            ->name('sessions.evaluation.save');
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
