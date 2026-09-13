<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\ModulePackage;
use App\Models\LearningSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeVisitBookingController extends Controller
{
    public function index(Request $request): View
    {
        $step = (int) $request->get('step', 1);

        // 1. Ambil paket modul yang aktif
        $packages = ModulePackage::where('is_active', true)->get();
        
        // 2. Ambil data guru/tutor aktif beserta relasi user dan tarifnya
        $tutors = Teacher::with(['user', 'tutorPackageRates'])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        // 3. Ambil tutor terpilih (berdasarkan UUID teacher_id)
        $selectedTeacher = $request->has('teacher_id') 
            ? Teacher::with('user')->find($request->get('teacher_id')) 
            : $tutors->first();

        // 4. Ambil paket terpilih (berdasarkan UUID package_id)
        $selectedPackage = $request->has('package_id') 
            ? ModulePackage::find($request->get('package_id')) 
            : $packages->first();

        // 5. Ambil jadwal belajar offline/home visit yang tersedia untuk tutor terpilih
        $availableSchedules = $selectedTeacher 
            ? LearningSchedule::query()
                ->where('teacher_id', $selectedTeacher->teacher_id)
                ->where('method', 'offline')
                ->where('start_time', '>=', now())
                ->get() 
            : collect();

        return view('pages.booking-homevisit', compact(
            'step', 
            'packages', 
            'tutors', 
            'selectedTeacher', 
            'selectedPackage', 
            'availableSchedules'
        ));
    }
}