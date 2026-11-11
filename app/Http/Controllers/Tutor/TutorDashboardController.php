<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TutorDashboardController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'teacher', 403);

        $tutor = Teacher::query()->with('user')
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $sessions = $tutor->learningSessions()
            ->with([
                'order.package',
                'order.student.user',
                'order.squad.hostStudent.user',
                'order.squad.members.student.user',
                'order.squad.classMembers.student.user',
                'material',
            ])
            ->where(function ($query) {
                $query->whereNull('session_status')->orWhere('session_status', '!=', 'completed');
            })
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', 'completed');
            })
            ->whereHas('order', fn ($query) => $query->whereIn('payment_status', ['settled', 'paid']))
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now()->startOfDay())
            ->where('scheduled_at', '<=', now()->addDays(7)->endOfDay())
            ->orderBy('scheduled_at')
            ->get()
            ->map(function ($session) {
                $session->starts_at = $session->scheduled_at;
                $session->ends_at = $session->scheduled_at->copy()->addMinutes($session->duration_minutes ?? 0);
                $session->title = $session->order?->package?->package_name ?? $session->material?->title ?? 'Sesi Mengajar';
                $session->package_name = $session->order?->package?->package_name;
                $session->student_name = $session->order?->student?->user?->full_name
                    ?? $session->order?->squad?->hostStudent?->user?->full_name;
                $session->squad_name = $session->order?->squad?->squad_code;
                $session->squad_size = $session->order?->squad?->classMembers?->count()
                    ?: $session->order?->squad?->members?->count();
                $session->learning_method_label = match ($session->learning_method ?? $session->order?->learning_method) {
                    'online_zoom' => 'Online Zoom',
                    'home_visit' => 'Home Visit',
                    default => 'Metode belum ditentukan',
                };
                $session->location_address = $session->onsite_address ?? $session->student_address
                    ?? $session->order?->squad?->hostStudent?->address;
                $session->maps_url = $session->latitude && $session->longitude
                    ? 'https://www.google.com/maps/dir/?api=1&destination='.$session->latitude.','.$session->longitude
                    : ($session->location_address
                        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($session->location_address)
                        : null);
                $session->is_clocked_in = $session->teacher_clock_in !== null || $session->absensi_masuk_guru !== null;
                $session->total_sessions = $session->order?->session_package_count ?? $session->order?->session_package ?? null;
                $session->status_label = $session->session_status === 'in_progress' || $session->is_clocked_in
                    ? 'Sedang berlangsung'
                    : 'Terjadwal';

                return $session;
            });

        $todaySessions = $sessions->filter(fn ($session) => $session->starts_at->isToday())->values();
        $upcomingSessions = $sessions->take(12)->values();
        $nearestSession = $todaySessions->first(fn ($session) => ! $session->is_clocked_in)
            ?? $todaySessions->first()
            ?? $upcomingSessions->first();

        $activeOrders = $tutor->orders()
            ->whereIn('payment_status', ['settled', 'paid'])
            ->with(['squad.members', 'squad.classMembers'])
            ->get(['order_id', 'student_id', 'squad_id']);
        $activeStudentIds = $activeOrders->flatMap(function ($order) {
            return collect([$order->student_id])
                ->merge($order->squad?->members?->pluck('student_id') ?? collect())
                ->merge($order->squad?->classMembers?->pluck('student_id') ?? collect());
        })->filter()->unique()->values();
        $activeClassCount = ModulePackage::query()
            ->where('teacher_id', $tutor->teacher_id)
            ->where('is_active', true)
            ->count();

        $completedWorkLogs = $tutor->workLogs()->where('payroll_status', '!=', 'cancelled');
        $totalMinutes = (int) (clone $completedWorkLogs)->sum('duration_minutes');
        $workLogCount = (clone $completedWorkLogs)->count();
        $availableSlotCount = $tutor->availabilitySlots()
            ->where('is_active', true)
            ->where('is_available', true)
            ->count();

        $pretestProgress = $todaySessions->isEmpty() ? null : [
            'completed' => $todaySessions->filter(fn ($session) => $session->evaluationScores()->whereNotNull('pretest_score')->exists())->count(),
            'total' => $todaySessions->count(),
        ];

        return view('pages.tutor.beranda-tutor', [
            'tutor' => $tutor,
            'todaySessions' => $todaySessions,
            'upcomingSessions' => $upcomingSessions,
            'nearestSession' => $nearestSession,
            'activeStudentCount' => $activeStudentIds->count(),
            'activeClassCount' => $activeClassCount,
            'walletBalance' => (float) ($tutor->wallet_balance ?? 0),
            'totalTeachingHours' => round($totalMinutes / 60, 1),
            'completedSessionCount' => $workLogCount,
            'availableSlotCount' => $availableSlotCount,
            'pretestProgress' => $pretestProgress,
            'agendaDate' => Carbon::today()->translatedFormat('l, j F Y'),
            'showAgendaModal' => $request->boolean('agenda'),
        ]);
    }
}
