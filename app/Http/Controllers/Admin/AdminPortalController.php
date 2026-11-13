<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Teacher;
use App\Models\TeacherLevelRequest;
use App\Models\TeacherWorkLog;
use App\Models\TariffAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPortalController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'pendingTutorCount' => Teacher::where('cv_status', 'pending')->count(),
            'activeTutorCount' => Teacher::where('cv_status', 'approved')->whereHas('user', fn ($query) => $query->where('is_active', true))->count(),
            'platformRevenue' => Order::where('payment_status', 'settled')->sum('total_amount'),
            'tutorEarnings' => TeacherWorkLog::sum('teacher_earnings'),
            'recentApplications' => Teacher::with('user')->where('cv_status', 'pending')->latest()->limit(5)->get(),
            'recentTariffChanges' => TariffAuditLog::with('admin.user', 'tariff')->latest('changed_at')->limit(5)->get(),
        ]);
    }

    public function tutors(): View
    {
        return view('admin.tutors', [
            'teachers' => Teacher::with('user')->latest()->get(),
            'levelRequests' => TeacherLevelRequest::with('teacher.user')->where('request_status', 'pending_upgrade')->latest('created_at')->get(),
        ]);
    }

    public function tariffs(): View
    {
        return view('admin.tariffs', [
            'tariffs' => DB::table('master_tariffs')->leftJoin('module_packages', 'master_tariffs.package_id', '=', 'module_packages.package_id')
                ->select('master_tariffs.*', 'module_packages.grade_level as package_grade', 'module_packages.package_name')
                ->orderBy('grade_level')->orderBy('method')->get(),
            'auditLogs' => TariffAuditLog::with('admin.user')->latest('changed_at')->limit(100)->get(),
        ]);
    }

    public function subjects(): View
    {
        return view('admin.subjects', ['subjects' => \App\Models\Subject::orderBy('grade_level')->orderBy('name')->get()]);
    }

    public function finance(): View
    {
        $settled = (float) Order::where('payment_status', 'settled')->sum('total_amount');

        return view('admin.finance', [
            'settledRevenue' => $settled,
            'allocations' => [
                'Tutor (25%)' => $settled * .25,
                'Operasional & Iklan (45%)' => $settled * .45,
                'Pengembangan (20%)' => $settled * .20,
                'Subsidi Beasiswa (10%)' => $settled * .10,
            ],
            'workLogs' => TeacherWorkLog::with('teacher.user', 'session.order.package')->latest('created_at')->paginate(20),
        ]);
    }
}
