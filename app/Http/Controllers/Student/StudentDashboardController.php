<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(Request $request, StudentDashboardService $dashboardService): JsonResponse
    {
        $student = $request->user()?->student;
        abort_unless($student, 403);

        return response()->json([
            'success' => true,
            'data' => $dashboardService->getDashboardData($student->student_id),
        ]);
    }

    public function show(Request $request): View
    {
        //abort_unless($request->user()?->student, 403);
        return view('pages.student.dashboard');
    }
}
