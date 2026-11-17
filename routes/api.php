<?php

use App\Http\Controllers\Auth\TutorRegistrationController;
use App\Http\Controllers\Auth\StudentRegistrationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\BookingController;
use App\Http\Controllers\Payment\XenditWebhookController;
use App\Http\Controllers\Classroom\AttendanceController;
use App\Http\Controllers\Classroom\EvaluationController;
use App\Http\Controllers\Classroom\SessionRoomController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\ParentModeController;

Route::post('/tutors/register', [TutorRegistrationController::class, 'store'])
    ->name('api.tutors.register');

Route::post('/student/register', [StudentRegistrationController::class, 'register'])
    ->name('api.student.register');
Route::post('/students/register', [StudentRegistrationController::class, 'register'])
    ->name('api.students.register');

Route::prefix('student')->name('api.student.')->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');
    Route::get('/catalog/subjects', [CatalogController::class, 'subjects'])->name('catalog.subjects');

    Route::middleware(['web', 'auth:web', 'active-account'])->group(function () {
        Route::get('/booking/tutor-slots/{teacherId}', [BookingController::class, 'getTutorSlots'])->name('booking.tutor-slots');
        Route::post('/booking/calculate', [BookingController::class, 'calculate'])->name('booking.calculate');
        Route::post('/booking/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
        Route::get('/booking/orders/{orderId}/status', [BookingController::class, 'paymentStatus'])->name('booking.orders.status');
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/parent-mode/verify-pin', [ParentModeController::class, 'verifyPin'])->name('parent-mode.verify-pin');
        Route::get('/parent-mode/report', [ParentModeController::class, 'report'])->name('parent-mode.report');
    });
});

Route::middleware(['web', 'auth:web', 'active-account'])->prefix('classroom')->name('api.classroom.')->group(function () {
    Route::get('/session/{sessionId}/exam/{examType}', [SessionRoomController::class, 'getExam'])->name('session.exam');
    Route::get('/session/{sessionId}/exam', [SessionRoomController::class, 'examQuestions'])->name('session.exam.compat');
    Route::get('/session/{sessionId}/room-access', [SessionRoomController::class, 'roomAccess'])->name('session.room-access');
    Route::get('/session/{sessionId}/room', [SessionRoomController::class, 'getRoomDetails'])->name('session.room');
    Route::post('/session/{sessionId}/submit-exam', [SessionRoomController::class, 'submitExam'])->name('session.submit-exam');
    Route::post('/session/{sessionId}/evaluation', [EvaluationController::class, 'store'])->name('session.evaluation.store');
    Route::get('/session/{sessionId}/navigation', [AttendanceController::class, 'navigation'])->name('session.navigation');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
});

Route::post('/payments/xendit/webhook', [XenditWebhookController::class, 'handleCallback'])->name('api.payments.xendit.webhook');
