<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Menyiapkan nilai Pre-Test, Post-Test, dan catatan evaluasi tutor
        $student = $request->user()?->student;

        return view('pages.student.report');
    }
}