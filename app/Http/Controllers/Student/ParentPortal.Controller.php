<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    public function index(Request $request)
    {
        // Menyiapkan status otentikasi PIN 6-digit orang tua & ringkasan kehadiran
        $student = $request->user()?->student;

        return view('pages.student.parent');
    }
}