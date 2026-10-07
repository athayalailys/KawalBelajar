<?php

use App\Http\Controllers\TutorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/tutors/register', [TutorRegistrationController::class, 'store'])
    ->name('api.tutors.register');
