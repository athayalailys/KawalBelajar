<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\TutorPackageRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingWizardDemoSeeder extends Seeder
{
    /**
     * Seed data khusus untuk mencoba katalog multi-tutor dan alur checkout.
     * Jalankan dengan: php artisan db:seed --class=BookingWizardDemoSeeder
     */
    public function run(): void
    {
        $studentUser = User::query()->updateOrCreate(
            ['email' => 'booking.demo@kawalbelajar.id'],
            [
                'username' => 'booking_demo',
                'full_name' => 'Rizky Demo Pembelajar',
                'phone_number' => '081234567800',
                'password_hash' => Hash::make('password123'),
                'role' => 'student',
                'is_active' => true,
            ],
        );

        Student::query()->updateOrCreate(
            ['user_id' => $studentUser->user_id],
            [
                'education_level' => 'SMP',
                'saved_address' => 'Jl. Kayu Tangi No. 10, Banjarmasin',
                'address' => 'Jl. Kayu Tangi No. 10, Banjarmasin',
                'landmark' => 'Dekat kampus',
            ],
        );

        $tutors = [
            ['email' => 'haikal.booking@kawalbelajar.id', 'name' => 'M. Haikal Fauzan, S.Pd', 'university' => 'Universitas Lambung Mangkurat', 'subject' => 'Matematika', 'rate' => 100000],
            ['email' => 'sarah.booking@kawalbelajar.id', 'name' => 'Sarah Amanda, M.Si', 'university' => 'Universitas Indonesia', 'subject' => 'Matematika', 'rate' => 125000],
            ['email' => 'dimas.booking@kawalbelajar.id', 'name' => 'Dimas Arya, S.Si', 'university' => 'Institut Teknologi Bandung', 'subject' => 'Fisika', 'rate' => 115000],
        ];

        $packages = [];
        foreach (['Matematika', 'Fisika', 'Bahasa Inggris'] as $subject) {
            $packages[$subject] = ModulePackage::query()->updateOrCreate(
                ['package_name' => "Paket {$subject} SMP - Booking Demo"],
                [
                    'grade_level' => 'SMP',
                    'subject_name' => $subject,
                    'description' => "Paket demo KawalBelajar untuk {$subject} tingkat SMP.",
                    'base_price' => 100000,
                    'is_active' => true,
                    'is_published' => true,
                    'is_open' => true,
                    'supported_session_packages' => '1,4',
                ],
            );
        }

        foreach ($tutors as $index => $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'username' => Str::before($data['email'], '@'),
                    'full_name' => $data['name'],
                    'phone_number' => '0812345678' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'password_hash' => Hash::make('password123'),
                    'role' => 'teacher',
                    'is_active' => true,
                ],
            );

            $teacher = Teacher::query()->updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'university' => $data['university'],
                    'study_program' => $data['subject'] === 'Fisika' ? 'Pendidikan Fisika' : 'Pendidikan Matematika',
                    'focus_subject' => $data['subject'],
                    'approved_levels' => ['SMP'],
                    'verified_levels' => ['SMP'],
                    'requested_levels' => ['SMP'],
                    'cv_status' => 'approved',
                    'teacher_level' => ['junior', 'intermediate', 'senior'][$index],
                ],
            );
            $teacher->forceFill(['is_active' => true])->save();

            $subjectsForTutor = $index === 2 ? ['Fisika', 'Matematika'] : ['Matematika', 'Bahasa Inggris'];
            foreach ($subjectsForTutor as $subject) {
                $package = $packages[$subject];

                foreach (['home_visit', 'online_zoom'] as $method) {
                    TutorPackageRate::query()->updateOrCreate(
                        [
                            'package_id' => $package->package_id,
                            'teacher_level' => $teacher->teacher_level,
                            'learning_method' => $method,
                        ],
                        ['category_rate' => $data['rate']],
                    );
                }

                LearningMaterial::query()->updateOrCreate(
                    [
                        'teacher_id' => $teacher->teacher_id,
                        'package_id' => $package->package_id,
                        'title' => "Modul {$subject} {$data['name']} - Booking Demo",
                    ],
                    ['material_type' => 'pdf', 'file_or_url' => null, 'video_duration_minutes' => null],
                );
            }

            foreach (['home_visit', 'online_zoom'] as $method) {
                foreach (range(1, 7) as $dayOffset) {
                    $date = Carbon::today()->addDays($dayOffset);
                    foreach (['16:00:00', '18:00:00', '20:00:00'] as $startTime) {
                        TeacherAvailabilitySlot::query()->updateOrCreate(
                            [
                                'teacher_id' => $teacher->teacher_id,
                                'day_of_week' => $date->format('l'),
                                'start_time' => $startTime,
                                'learning_method' => $method,
                            ],
                            [
                                'end_time' => Carbon::parse($startTime)->addMinutes(90)->format('H:i:s'),
                                'is_active' => true,
                                'is_available' => true,
                            ],
                        );
                    }
                }
            }
        }

        $demoStudent = Student::query()->where('user_id', $studentUser->user_id)->firstOrFail();
        $demoTutor = Teacher::query()->whereHas('user', fn ($query) => $query->where('email', 'haikal.booking@kawalbelajar.id'))->firstOrFail();
        $demoPackage = $packages['Matematika'];
        $scheduleOrder = Order::query()->updateOrCreate(
            ['invoice_number' => 'BOOKING-DEMO-JADWAL-4-SESI'],
            [
                'student_id' => $demoStudent->student_id,
                'teacher_id' => $demoTutor->teacher_id,
                'package_id' => $demoPackage->package_id,
                'squad_id' => null,
                'learning_method' => 'home_visit',
                'session_package' => 4,
                'selected_slot_id' => null,
                'total_amount' => 400000,
                'payment_status' => 'settled',
                'order_status' => 'confirmed',
                'order_date' => now(),
            ],
        );

        $material = LearningMaterial::query()
            ->where('teacher_id', $demoTutor->teacher_id)
            ->where('package_id', $demoPackage->package_id)
            ->first();

        $cursor = Carbon::now()->startOfDay()->addDay();
        for ($sessionNumber = 1; $sessionNumber <= 4; $sessionNumber++) {
            while ($cursor->isSunday()) $cursor->addDay();

            $startAt = $cursor->copy()->setTime(17 + ($sessionNumber % 2), 0);
            $endAt = $startAt->copy()->addMinutes(90);
            $schedule = LearningSchedule::query()->updateOrCreate(
                [
                    'teacher_id' => $demoTutor->teacher_id,
                    'package_id' => $demoPackage->package_id,
                    'method' => 'home_visit',
                    'start_time' => $startAt,
                ],
                [
                    'location' => $demoStudent->saved_address,
                    'end_time' => $endAt,
                    'schedule_status' => 'scheduled',
                ],
            );

            LearningSession::query()->updateOrCreate(
                ['order_id' => $scheduleOrder->order_id, 'session_number' => $sessionNumber],
                [
                    'student_id' => $demoStudent->student_id,
                    'teacher_id' => $demoTutor->teacher_id,
                    'schedule_id' => $schedule->schedule_id,
                    'material_id' => $material?->material_id,
                    'scheduled_at' => $startAt,
                    'duration_minutes' => 90,
                    'learning_method' => 'home_visit',
                    'session_status' => 'scheduled',
                    'status' => 'scheduled',
                    'onsite_address' => $demoStudent->saved_address,
                    'student_address' => $demoStudent->saved_address,
                    'latitude' => $demoStudent->latitude,
                    'longitude' => $demoStudent->longitude,
                    'address_landmark' => $demoStudent->landmark,
                    'zoom_meeting_url' => null,
                ],
            );

            $cursor = $cursor->addDay();
        }

        $this->command?->info('Data demo booking wizard siap digunakan.');
        $this->command?->line('Akun pembelajar: booking.demo@kawalbelajar.id / password123');
        $this->command?->line('Akun tutor: haikal.booking@kawalbelajar.id, sarah.booking@kawalbelajar.id, dimas.booking@kawalbelajar.id / password123');
    }
}
