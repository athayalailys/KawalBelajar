<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Teacher;
use App\Models\ModulePackage;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $banner = Schema::hasTable('banners')
            ? Banner::where('is_active', true)->latest()->first()
            : null;

        $tutors = Teacher::with(['user'])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->take(6)
            ->get();

        $programs = Schema::hasTable('module_packages')
            ? ModulePackage::where('is_active', true)
                ->orderBy('grade_level')
                ->orderBy('package_name')
                ->get()
            : collect();

        return view('pages.public.landing', compact('banner', 'tutors', 'programs'));
    }
}
