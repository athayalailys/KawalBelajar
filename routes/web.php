<?php

use App\Http\Controllers\LandingPageController; // Controller untuk halaman utama / landing page
use App\Http\Controllers\Admin\TutorReviewController; // Controller untuk pengelolaan dan peninjauan tutor oleh admin
use App\Http\Controllers\Admin\AdminPortalController;
use App\Http\Controllers\Admin\TutorLevelReviewController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Auth\TutorRegistrationController; // Controller untuk proses registrasi tutor
use App\Http\Controllers\Auth\LoginController; // Controller untuk proses login pengguna
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Tutor\TutorLandingController;
use App\Http\Controllers\Student\StudentDashboardController; // Controller untuk dashboard siswa
use App\Http\Controllers\Student\BookingController;
use App\Http\Controllers\Student\ClassroomPageController;
use App\Http\Controllers\Student\ModuleDownloadController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Tutor\TutorClassDetailsController;
use App\Http\Controllers\Tutor\TutorClassesController;
use App\Http\Controllers\Tutor\TutorTeachingRoomController;
use App\Http\Controllers\Tutor\TutorAvailabilityController;
use App\Http\Controllers\Tutor\TutorLevelRequestController;
use App\Http\Controllers\Tutor\TutorSessionMeetingLinkController;
use App\Http\Controllers\Tutor\PayrollController;
use App\Http\Controllers\Tutor\TutorClassRosterController;
use App\Http\Controllers\Tutor\TutorClassMaterialsController;
use Illuminate\Http\Request; // Class Request untuk membaca informasi permintaan pengguna   
use Illuminate\Support\Facades\Route; // Facade Route untuk mendefinisikan URL aplikasi

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
        Route::get('/classroom/session/{sessionId}/materials/{materialId}/download', [ModuleDownloadController::class, 'download'])->name('materials.download');
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

// 8. DASHBOARD TUTOR
Route::middleware(['auth', 'active-account'])
    ->prefix('tutor')
    ->name('tutor.')
    ->group(function () {
        Route::get('/dashboard', [TutorDashboardController::class, 'show'])
            ->name('dashboard');
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll');
        Route::post('/level-requests', [TutorLevelRequestController::class, 'store'])
            ->name('level-requests.store');
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
        Route::patch('/sessions/{sessionId}/meeting-link', [TutorSessionMeetingLinkController::class, 'update'])
            ->name('sessions.meeting-link');
    });

// Portal dan pengelolaan Admin
Route::middleware(['auth', 'active-account', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/tutors', [AdminPortalController::class, 'tutors'])->name('tutors');
    Route::get('/tariffs', [AdminPortalController::class, 'tariffs'])->name('tariffs');
    Route::get('/subjects', [AdminPortalController::class, 'subjects'])->name('subjects');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::patch('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::post('/subjects/{id}/toggle', [SubjectController::class, 'toggle'])->name('subjects.toggle');
    Route::get('/finance', [AdminPortalController::class, 'finance'])->name('finance');
});

// Admin API: peninjauan tutor, jenjang, tarif, dan pencairan payroll
Route::middleware(['auth', 'active-account', 'admin'])->prefix('admin/api')->group(function () {
    Route::get('/level-requests', [TutorLevelReviewController::class, 'index'])->name('admin.level-requests.index');
    Route::post('/level-requests/{id}/approve', [TutorLevelReviewController::class, 'approve'])->name('admin.level-requests.approve');
    Route::post('/level-requests/{id}/reject', [TutorLevelReviewController::class, 'reject'])->name('admin.level-requests.reject');
    Route::put('/tariffs', [TariffController::class, 'update'])->name('admin.tariffs.update');
    Route::post('/finance/payroll/{logId}/paid', [FinanceController::class, 'markPaid'])->name('admin.finance.payroll.paid');
});

// Pengelolaan dan peninjauan tutor oleh Admin
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

        Route::get('/{teacher}/document/{type}', [TutorReviewController::class, 'downloadDocument'])
            ->name('document');

    });
