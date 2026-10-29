<?php

namespace App\Services\Student;

use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Squad;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\TutorPackageRate;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function calculatePricing(
        Student $student,
        string $teacherId,
        string $packageId,
        string $method,
        int $sessionCount,
        string $format,
        ?int $capacity = null,
        ?string $squadCode = null,
    ): array {
        $teacher = Teacher::query()
            ->with('user')
            ->whereKey($teacherId)
            ->where('is_active', true)
            ->where('cv_status', 'approved')
            ->first();

        if (!$teacher || !$teacher->user?->is_active) {
            throw ValidationException::withMessages(['teacher_id' => 'Tutor tidak tersedia untuk pemesanan.']);
        }

        if (!in_array($student->education_level, $teacher->approved_levels ?? [], true)) {
            throw ValidationException::withMessages(['teacher_id' => 'Tutor belum disetujui untuk jenjang pembelajar ini.']);
        }

        $package = ModulePackage::query()
            ->whereKey($packageId)
            ->where('is_active', true)
            ->where('grade_level', $student->education_level)
            ->first();

        if (!$package) {
            throw ValidationException::withMessages(['package_id' => 'Paket tidak tersedia untuk jenjang pembelajar ini.']);
        }

        $hasMethodAvailability = TeacherAvailabilitySlot::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('is_active', true)
            ->where(function ($query) use ($method) {
                $query->where('learning_method', $method)->orWhereNull('learning_method');
            })
            ->exists();

        if (!$hasMethodAvailability) {
            throw ValidationException::withMessages(['learning_method' => 'Tutor tidak memiliki jadwal untuk metode belajar ini.']);
        }

        $rateQuery = TutorPackageRate::query()
            ->where('package_id', $package->package_id)
            ->where('teacher_level', $teacher->teacher_level);
        $rate = (clone $rateQuery)->where('learning_method', $method)->first()
            ?? $rateQuery->whereNull('learning_method')->first();

        if (!$rate || (float) $rate->category_rate <= 0) {
            throw ValidationException::withMessages(['package_id' => 'Tarif paket untuk level tutor ini belum tersedia.']);
        }

        $discountPercentage = 0.0;
        $squad = null;

        if ($format === 'host') {
            $capacity = (int) $capacity;
            $maximumCapacity = $method === 'online_zoom' ? 20 : 5;

            if ($capacity < 2 || $capacity > $maximumCapacity) {
                throw ValidationException::withMessages(['target_capacity' => 'Kapasitas squad tidak sesuai dengan metode belajar.']);
            }

            if ($method === 'online_zoom') {
                $discountPercentage = match (true) {
                    $capacity >= 20 => 35,
                    $capacity >= 10 => 30,
                    $capacity >= 5 => 15,
                    $capacity >= 2 => 10,
                    default => 0,
                };
            } else {
                $discountPercentage = match (true) {
                    $capacity >= 5 => 25,
                    $capacity >= 3 => 15,
                    $capacity >= 2 => 10,
                    default => 0,
                };
            }
        } elseif ($format === 'joiner') {
            $squad = Squad::query()
                ->where('squad_code', $squadCode)
                ->lockForUpdate()
                ->first();

            if (!$squad || $squad->status !== 'open') {
                throw ValidationException::withMessages(['squad_code' => 'Squad tidak ditemukan atau sudah ditutup.']);
            }

            if (
                $squad->teacher_id !== $teacher->teacher_id
                || $squad->package_id !== $package->package_id
                || $squad->learning_method !== $method
            ) {
                throw ValidationException::withMessages(['squad_code' => 'Squad tidak cocok dengan tutor, paket, atau metode belajar yang dipilih.']);
            }

            if ($squad->members()->where('student_id', $student->student_id)->exists()) {
                throw ValidationException::withMessages(['squad_code' => 'Anda sudah tergabung dalam squad ini.']);
            }

            $activeMemberCount = $this->countActiveMembers($squad);
            if ($activeMemberCount >= $squad->target_capacity) {
                throw ValidationException::withMessages(['squad_code' => 'Kapasitas squad sudah penuh.']);
            }

            $discountPercentage = (float) $squad->discount_rate;
        }

        $baseRatePerSession = (float) $rate->category_rate;
        $discountAmount = $baseRatePerSession * $discountPercentage / 100;
        $finalRatePerSession = $baseRatePerSession - $discountAmount;
        $totalAmount = $finalRatePerSession * $sessionCount;

        return [
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => $method,
            'format' => $format,
            'base_rate_per_session' => $baseRatePerSession,
            'discount_percentage' => $discountPercentage,
            'final_price_per_session' => $finalRatePerSession,
            'package_sessions' => $sessionCount,
            'total_amount' => $totalAmount,
            'squad' => $squad,
        ];
    }

    public function getTeacherSlotsWithClashDetection(string $teacherId, string $method): array
    {
        $teacherIsBookable = Teacher::query()
            ->whereKey($teacherId)
            ->where('is_active', true)
            ->where('cv_status', 'approved')
            ->whereHas('user', fn ($query) => $query->where('is_active', true))
            ->exists();

        if (!$teacherIsBookable) {
            throw ValidationException::withMessages(['teacher_id' => 'Tutor tidak tersedia untuk pemesanan.']);
        }

        $slots = TeacherAvailabilitySlot::query()
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)
            ->where(function ($query) use ($method) {
                $query->where('learning_method', $method)->orWhereNull('learning_method');
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $slots->map(function (TeacherAvailabilitySlot $slot) {
            $activeOrders = Order::query()
                ->where('teacher_id', $slot->teacher_id)
                ->whereHas('selectedSlot', fn ($query) => $query
                    ->where('day_of_week', $slot->day_of_week)
                    ->where('start_time', $slot->start_time))
                ->where(function ($query) {
                    $query->where('payment_status', 'settled')
                        ->orWhere(function ($pending) {
                            $pending->where('payment_status', 'pending')
                                ->where('expires_at', '>', now());
                        });
                })
                ->get(['squad_id']);

            $availableSquadCodes = Squad::query()
                ->whereIn('squad_id', $activeOrders->pluck('squad_id')->filter()->unique())
                ->where('status', 'open')
                ->pluck('squad_code')
                ->values();
            $slotIsAvailable = $activeOrders->isEmpty();

            return [
                'slot_id' => $slot->slot_id,
                'day_of_week' => $slot->day_of_week,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'learning_method' => $slot->learning_method ?? 'both',
                'is_available' => $slotIsAvailable,
                'available_squad_codes' => $availableSquadCodes,
                'clash_label' => $slotIsAvailable ? 'Tersedia' : ($availableSquadCodes->isNotEmpty() ? 'Jadwal tersedia melalui squad.' : 'Jadwal ini sudah dipesan.'),
            ];
        })->all();
    }

    public function countActiveMembers(Squad $squad): int
    {
        return $squad->members()
            ->whereHas('order', function ($query) {
                $query->where(function ($active) {
                    $active->where('payment_status', 'settled')
                        ->orWhere(function ($pending) {
                            $pending->where('payment_status', 'pending')
                                ->where('expires_at', '>', now());
                        });
                });
            })
            ->count();
    }
}
