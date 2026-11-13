<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherWorkLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function markPaid(Request $request, string $logId): JsonResponse
    {
        $data = $request->validate(['reference' => ['nullable', 'string', 'max:100']]);
        $log = TeacherWorkLog::findOrFail($logId);
        abort_unless($log->payroll_status !== 'paid', 409, 'Honor ini sudah ditandai telah dicairkan.');

        DB::transaction(function () use ($log, $data) {
            $log->update(['payroll_status' => 'paid']);
            $log->teacher->payouts()->create([
                'amount' => $log->teacher_earnings,
                'bank_name' => 'Pencairan melalui admin',
                'bank_account_number' => 'N/A',
                'bank_account_holder' => $log->teacher->user->full_name,
                'disbursement_reference' => $data['reference'] ?? null,
                'payout_status' => 'paid',
                'payout_date' => now(),
            ]);
        });

        return response()->json(['message' => 'Honor tutor ditandai telah dicairkan.']);
    }
}
