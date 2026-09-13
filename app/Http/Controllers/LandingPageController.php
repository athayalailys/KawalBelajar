<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Teacher;
use App\Models\ModulePackage;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $banners = Banner::where('is_active', true)->latest()->get();

        $tutors = Teacher::with(['user'])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->take(6)
            ->get();

        $programs = ModulePackage::where('is_active', true)
            ->take(3)
            ->get();

        return view('welcome', compact('banners', 'tutors', 'programs'));
    }
}