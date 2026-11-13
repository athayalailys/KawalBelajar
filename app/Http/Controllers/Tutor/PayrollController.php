<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);
        $teacher = Teacher::query()->where('user_id', $user->user_id)->firstOrFail();
        $logs = $teacher->workLogs()->with(['session.order.package', 'session.material'])
            ->orderByDesc('created_at')->get();

        $paidStatuses = ['paid', 'disbursed', 'settled', 'completed'];
        $paidEarnings = (float) $logs->whereIn('payroll_status', $paidStatuses)->sum('teacher_earnings');
        $pendingEarnings = (float) $logs->reject(fn ($log) => in_array(strtolower((string) $log->payroll_status), $paidStatuses, true))
            ->sum('teacher_earnings');

        return view('tutor.payroll', [
            'teacher' => $teacher,
            'logs' => $logs,
            'totalEarnings' => (float) $logs->sum('teacher_earnings'),
            'pendingEarnings' => $pendingEarnings,
            'paidEarnings' => $paidEarnings,
            'completedSessionCount' => $logs->count(),
            'totalMinutes' => (int) $logs->sum('duration_minutes'),
        ]);
    }
}
