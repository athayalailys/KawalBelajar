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

        $approvedLevels = array_merge((array) $teacher->approved_levels, (array) $teacher->verified_levels);
        $approvedLevels = array_map(fn ($level) => mb_strtolower(trim((string) $level)), $approvedLevels);
        if (!in_array(mb_strtolower(trim((string) $student->education_level)), $approvedLevels, true)) {
            throw ValidationException::withMessages(['teacher_id' => 'Tutor belum disetujui untuk jenjang pembelajar ini.']);
        }

        $package = ModulePackage::query()
            ->whereKey($packageId)
            ->where('is_active', true)
            ->where('is_published', true)
            ->where('is_open', true)
            ->whereRaw('LOWER(TRIM(grade_level)) = ?', [mb_strtolower(trim((string) $student->education_level))])
            ->where('teacher_id', $teacher->teacher_id)
            ->first();

        if (!$package) {
            throw ValidationException::withMessages(['package_id' => 'Paket tidak tersedia untuk jenjang pembelajar ini.']);
        }

        if (!$package->isReadyForEnrollment()) {
            throw ValidationException::withMessages(['package_id' => 'Kelas belum siap dipesan. Tutor perlu melengkapi materi dan minimal 3 soal pada setiap pertemuan.']);
        }

        $supportedPackages = filled($package->supported_session_packages)
            ? array_map('intval', explode(',', $package->supported_session_packages))
            : [1, 4];
        if (!in_array($sessionCount, $supportedPackages, true)) {
            throw ValidationException::withMessages(['session_package' => 'Paket kelas tidak menyediakan jumlah pertemuan yang dipilih.']);
        }

        $normalizeMethod = static fn (?string $value): string => match (mb_strtolower(trim((string) $value))) {
            'online', 'zoom', 'online_zoom' => 'online_zoom',
            'home_visit', 'home visit' => 'home_visit',
            default => mb_strtolower(trim((string) $value)),
        };
        if ($package->learning_method && $normalizeMethod($package->learning_method) !== $normalizeMethod($method)) {
            throw ValidationException::withMessages(['learning_method' => 'Metode belajar tidak sesuai dengan kelas yang dipilih.']);
        }

        $rateQuery = TutorPackageRate::query()
            ->where('package_id', $package->package_id)
            ->where('teacher_level', $teacher->teacher_level);
        $rate = $rateQuery->get()->first(fn ($candidate) =>
            $candidate->learning_method !== null && $normalizeMethod($candidate->learning_method) === $normalizeMethod($method)
        ) ?? $rateQuery->whereNull('learning_method')->first();

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

    public function getTeacherSlotsWithClashDetection(string $teacherId, string $method, ?string $packageId = null): array
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
            ->where('is_available', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->filter(function (TeacherAvailabilitySlot $slot) use ($method) {
                $slotMethod = mb_strtolower(trim((string) $slot->learning_method));
                $requested = mb_strtolower(trim($method));
                $slotMethod = in_array($slotMethod, ['online', 'zoom', 'online_zoom'], true) ? 'online_zoom' : $slotMethod;
                $requested = in_array($requested, ['online', 'zoom', 'online_zoom'], true) ? 'online_zoom' : $requested;
                return $slotMethod === '' || $slotMethod === $requested;
            });

        if ($packageId) {
            $package = ModulePackage::query()->whereKey($packageId)->where('teacher_id', $teacherId)->firstOrFail();
            $packageSlotIds = $package->availabilitySlots()->pluck('teacher_availability_slots.slot_id');
            if ($packageSlotIds->isNotEmpty()) {
                $slots = $slots->whereIn('slot_id', $packageSlotIds);
            }
        }

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
