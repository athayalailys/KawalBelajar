<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\TariffAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TariffController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grade_level' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'UMUM'])],
            'learning_method' => ['required', Rule::in(['online_zoom', 'home_visit'])],
            'amount' => ['required', 'integer', 'min:1000'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $tariffs = DB::table('master_tariffs')->leftJoin('module_packages', 'master_tariffs.package_id', '=', 'module_packages.package_id')
            ->where(function ($query) use ($data) {
                $query->where('master_tariffs.grade_level', $data['grade_level'])
                    ->orWhere('module_packages.grade_level', $data['grade_level']);
            })
            ->where(function ($query) use ($data) {
                $query->where('master_tariffs.method', $data['learning_method'])
                    ->orWhere('master_tariffs.learning_method', $data['learning_method']);
            })
            ->select('master_tariffs.*')->get();

        if ($tariffs->isEmpty()) {
            throw ValidationException::withMessages(['grade_level' => 'Belum ada tarif paket untuk jenjang dan metode tersebut. Buat program paket terkait terlebih dahulu.']);
        }

        $admin = Admin::firstOrCreate(['user_id' => $request->user()->user_id], ['position' => 'Administrator']);
        DB::transaction(function () use ($tariffs, $data, $admin) {
            foreach ($tariffs as $tariff) {
                $id = $tariff->tariff_id ?? $tariff->rate_id;
                $old = (float) ($tariff->category_rate ?? $tariff->base_rate ?? 0);
                DB::table('master_tariffs')->where('rate_id', $tariff->rate_id)->update([
                    'category_rate' => $data['amount'], 'base_rate' => $data['amount'],
                ]);
                TariffAuditLog::create([
                    'tariff_id' => $id, 'admin_id' => $admin->admin_id,
                    'old_price' => $old, 'new_price' => $data['amount'],
                    'justification_notes' => $data['reason'], 'changed_at' => now(),
                ]);
            }
        });

        return response()->json(['message' => 'Tarif paket berhasil diperbarui dan dicatat.', 'updated_count' => $tariffs->count()]);
    }
}
