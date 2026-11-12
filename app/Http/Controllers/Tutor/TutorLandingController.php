<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TutorLandingController extends Controller
{
    public function index(): View
    {
        return view('pages.tutor.landing');
    }
}
