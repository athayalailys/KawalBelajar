<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\TutorReviewController;
use App\Http\Controllers\TutorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::get('/tutor/register', [TutorRegistrationController::class, 'create'])->name('tutor.register');
Route::post('/tutor/register', [TutorRegistrationController::class, 'store'])->name('tutor.register.store');

Route::middleware(['auth', 'active-account', 'admin'])->prefix('admin/api/tutors')->name('admin.tutors.')->group(function () {
    Route::get('/', [TutorReviewController::class, 'index'])->name('index');
    Route::post('/{teacher}/approve', [TutorReviewController::class, 'approve'])->name('approve');
    Route::post('/{teacher}/reject', [TutorReviewController::class, 'reject'])->name('reject');
    Route::post('/{teacher}/suspend', [TutorReviewController::class, 'suspend'])->name('suspend');
    Route::post('/{teacher}/reinstate', [TutorReviewController::class, 'reinstate'])->name('reinstate');
});
