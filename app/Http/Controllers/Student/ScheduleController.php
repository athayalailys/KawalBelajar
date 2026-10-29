<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        // Menyiapkan data sesi mendatang & riwayat bimbingan
        $student = $request->user()?->student;
        
        // $upcomingSessions = $student ? $student->bookings()->where('status', 'confirmed')->get() : collect();
        // $pastSessions = $student ? $student->bookings()->where('status', 'completed')->get() : collect();

        return view('pages.student.schedule');
    }
}