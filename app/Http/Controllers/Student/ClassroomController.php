<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        // Menyiapkan data Pre-Test diagnostik & materi bimbingan aktif
        $student = $request->user()?->student;

        return view('pages.student.classroom');
    }
}