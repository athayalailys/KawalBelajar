<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Admin;
use App\Models\EvaluationScore;
use App\Models\LearningMaterial;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\TeacherLevelRequest;
use App\Models\TeacherWorkLog;
use App\Models\TutorClassSessionContent;
use App\Models\TutorPackageRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Demo ecosystem for the Admin, Tutor, Student and Parent browser journeys.
 * All records use stable natural keys so running db:seed repeatedly is safe.
 */
class EcosystemDemoSeeder extends Seeder
{
    private const ADDRESS = 'Jl. Brigjen H. Hasan Basri No. 45, Banjarmasin';

    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@kawalbelajar.id')->firstOrFail();

        $arifin = $this->teacher('tutor@kawalbelajar.id', 'M. Arifin, S.Pd.', [
            'SD', 'SMP', 'SMA',
        ], 'Fisika dan Matematika');
        $haikal = $this->teacher('haikal@kawalbelajar.id', 'M. Haikal Fauzan, S.Pd.', [
            'SMP', 'SMA',
        ], 'Matematika');

        $student = $this->student(
            'siswa@kawalbelajar.id',
            'Athaya Laily Syafitri',
            'SMP',
            '123456',
            'ortu@kawalbelajar.id',
            'Orang Tua Athaya',
        );
        $newStudent = $this->student(
            'siswabaru@kawalbelajar.id',
            'Alfisyah Rina',
            'SMA',
            '654321',
            'ortu.baru@kawalbelajar.id',
            'Orang Tua Alfisyah',
        );

        $this->seedSubjects();
        $this->seedAvailability($arifin);
        $haikalSlots = $this->seedAvailability($haikal);

        $arifinPhysics = $this->package(
            $arifin,
            'Pendalaman Hukum Newton: Konsep Inersia & Resultan Gaya',
            'SMP',
            'Fisika',
            'online_zoom',
            10,
            '4',
            300000,
            true,
        );
        $arifinMath = $this->package(
            $arifin,
            'Klinik PR & Persiapan Ujian Matematika SMP',
            'SMP',
            'Matematika',
            'home_visit',
            5,
            '1,4',
            75000,
            true,
        );

        $this->seedPackageContent($arifinPhysics, $arifin, 4);
        $this->seedPackageContent($arifinMath, $arifin, 4);

        $haikalMath = $this->package(
            $haikal,
            'Matematika SMP — Bimbingan Intensif 4 Sesi',
            'SMP',
            'Matematika',
            'home_visit',
            5,
            '1,4',
            75000,
            true,
        );
        $this->seedPackageContent($haikalMath, $haikal, 4);

        $this->seedOfficialTariffs($arifin, $arifinPhysics, $arifinMath, $admin);
        $this->seedOfficialTariffs($haikal, $haikalMath, $haikalMath, $admin);

        $this->seedPendingTutor();
        $this->seedUpgradeRequest($arifin);
        $this->seedSettledStudentJourney($student, $haikal, $haikalMath, $haikalSlots[0]);

        // Deliberately no order or sessions for this account: it is the empty-state/demo buyer.
        $newStudent->loadCount('orders');

        $this->command?->info('Ekosistem demo KawalBelajar berhasil disiapkan.');
        $this->command?->line('Admin: admin@kawalbelajar.id / password');
        $this->command?->line('Tutor aktif: tutor@kawalbelajar.id / password');
        $this->command?->line('Tutor sesi pembelajar: haikal@kawalbelajar.id / password');
        $this->command?->line('Calon tutor: calon.tutor@kawalbelajar.id / password');
        $this->command?->line('Pembelajar: siswa@kawalbelajar.id / password (tes awal terbuka sekarang)');
        $this->command?->line('Pembelajar baru: siswabaru@kawalbelajar.id / password');
        $this->command?->line('PIN Mode Orang Tua: 123456 (Athaya), 654321 (Alfisyah)');
    }

    private function teacher(string $email, string $name, array $levels, string $focus): Teacher
    {
        $user = $this->user($email, $name, 'teacher');

        $teacher = Teacher::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'university' => 'Universitas Lambung Mangkurat',
                'study_program' => 'Pendidikan Matematika',
                'semester' => 'Lulus',
                'focus_subject' => $focus,
                'requested_levels' => $levels,
                'approved_levels' => $levels,
                'verified_levels' => $levels,
                'cv_status' => 'approved',
                'review_note' => 'Dokumen terverifikasi untuk akun demo.',
                'teacher_level' => 'senior',
                'default_location' => 'Banjarmasin',
                'wallet_balance' => 18750,
            ],
        );
        $teacher->forceFill(['is_active' => true])->save();

        return $teacher;
    }

    private function student(
        string $email,
        string $name,
        string $level,
        string $pin,
        string $parentEmail,
        string $parentName,
    ): Student {
        $user = $this->user($email, $name, 'student');
        $parentUser = $this->user($parentEmail, $parentName, 'parent');
        $parent = ParentProfile::query()->updateOrCreate(
            ['user_id' => $parentUser->user_id],
            ['pin_hash' => Hash::make($pin), 'phone_number' => '081234567890'],
        );

        return Student::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'parent_id' => $parent->parent_id,
                'education_level' => $level,
                'address' => self::ADDRESS,
                'saved_address' => self::ADDRESS,
                'address_landmark' => 'Komplek Kayu Tangi II',
                'landmark' => 'Komplek Kayu Tangi II',
                'latitude' => -3.2956,
                'longitude' => 114.5912,
                'parent_name' => $parentName,
                'parent_phone' => '081234567890',
                'parent_pin_hash' => Hash::make($pin),
            ],
        );
    }

    private function user(string $email, string $name, string $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'username' => Str::before($email, '@'),
                'full_name' => $name,
                'password_hash' => Hash::make('password'),
                'role' => $role,
                'is_active' => true,
            ],
        );
    }

    private function seedSubjects(): void
    {
        $subjects = [
            'SD' => ['Tematik Terpadu SD', 'Matematika Dasar', 'Calistung', 'IPA SD', 'Bahasa Inggris Anak'],
            'SMP' => ['Fisika', 'Matematika', 'IPA Terpadu', 'Bahasa Inggris'],
            'SMA' => ['Fisika', 'Matematika Peminatan', 'Kimia', 'Biologi', 'UTBK SNBT'],
            'UMUM' => ['UI/UX Design', 'Web Coding', 'Bahasa Asing', 'Mengaji Metode Tilawati'],
        ];

        foreach ($subjects as $level => $names) {
            foreach ($names as $name) {
                DB::table('subjects')->updateOrInsert(
                    ['grade_level' => $level, 'name' => $name],
                    [
                        'subject_id' => DB::table('subjects')->where('grade_level', $level)->where('name', $name)->value('subject_id') ?? (string) Str::uuid(),
                        'category' => $level === 'UMUM' ? 'skill' : 'academic',
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }
    }

    /** @return list<TeacherAvailabilitySlot> */
    private function seedAvailability(Teacher $teacher): array
    {
        $slots = [
            ['Monday', '16:00:00', '17:30:00', 'online_zoom'],
            ['Wednesday', '18:00:00', '19:30:00', 'online_zoom'],
            ['Friday', '18:00:00', '19:30:00', 'home_visit'],
            ['Saturday', '10:00:00', '11:30:00', 'home_visit'],
        ];
        $models = [];

        foreach ($slots as [$day, $start, $end, $method]) {
            $models[] = TeacherAvailabilitySlot::query()->updateOrCreate(
                ['teacher_id' => $teacher->teacher_id, 'day_of_week' => $day, 'start_time' => $start],
                ['end_time' => $end, 'learning_method' => $method, 'is_active' => true, 'is_available' => true],
            );
        }

        return $models;
    }

    private function package(
        Teacher $teacher,
        string $name,
        string $level,
        string $subject,
        string $method,
        int $capacity,
        string $sessionPackages,
        int $price,
        bool $published,
    ): ModulePackage {
        return ModulePackage::query()->updateOrCreate(
            ['package_name' => $name],
            [
                'teacher_id' => $teacher->teacher_id,
                'grade_level' => $level,
                'subject_name' => $subject,
                'learning_method' => $method,
                'max_capacity' => $capacity,
                'description' => 'Program bimbingan demo untuk pengujian alur KawalBelajar.',
                'target_learning' => 'Memahami konsep dan meningkatkan kemampuan secara bertahap.',
                'supported_session_packages' => $sessionPackages,
                'base_price' => $price,
                'is_published' => $published,
                'is_open' => true,
                'is_active' => true,
            ],
        );
    }

    private function seedPackageContent(ModulePackage $package, Teacher $teacher, int $sessions): void
    {
        for ($number = 1; $number <= $sessions; $number++) {
            TutorClassSessionContent::query()->updateOrCreate(
                ['package_id' => $package->package_id, 'session_number' => $number],
                [
                    'topic' => $number === 1 ? $package->package_name : 'Pendalaman materi sesi '.$number,
                    'learning_target' => 'Pembelajar mampu menjelaskan dan menerapkan materi sesi '.$number.'.',
                ],
            );

            $path = sprintf('demo/materials/%s/session-%d.pdf', $package->package_id, $number);
            Storage::disk('public')->put($path, "%PDF-1.4\n% KawalBelajar demo module\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");
            LearningMaterial::query()->updateOrCreate(
                ['package_id' => $package->package_id, 'session_number' => $number],
                [
                    'teacher_id' => $teacher->teacher_id,
                    'title' => 'Modul '.$package->subject_name.' — Sesi '.$number,
                    'material_type' => 'pdf',
                    'file_or_url' => $path,
                    'created_at' => now(),
                ],
            );

            foreach (['pre_test', 'post_test'] as $examType) {
                for ($question = 1; $question <= 5; $question++) {
                    QuestionBank::query()->updateOrCreate(
                        [
                            'teacher_id' => $teacher->teacher_id,
                            'package_id' => $package->package_id,
                            'session_number' => $number,
                            'exam_type' => $examType,
                            'category' => 'DEMO',
                            'question_text' => sprintf('%s sesi %d — soal %d', $package->subject_name, $number, $question),
                        ],
                        [
                            'option_a' => 'Pilihan A',
                            'option_b' => 'Pilihan B',
                            'option_c' => 'Pilihan C',
                            'option_d' => 'Pilihan D',
                            'answer_key' => 'A',
                        ],
                    );
                }
            }
        }
    }

    private function seedOfficialTariffs(Teacher $teacher, ModulePackage $online, ModulePackage $home, User $admin): void
    {
        $prices = (array) config('booking.default_tariffs', []);

        foreach ($prices as $level => $byMethod) {
            foreach ($byMethod as $method => $amount) {
                $package = $level === 'SMP'
                    ? ($method === 'online_zoom' ? $online : $home)
                    : $this->package(
                        $teacher,
                        sprintf('Tarif Master %s %s — %s', $level, $method, Str::slug($teacher->teacher_id)),
                        $level,
                        'Tarif Master',
                        $method,
                        1,
                        '1',
                        $amount,
                        false,
                    );

                $rate = TutorPackageRate::query()->firstOrNew([
                    'package_id' => $package->package_id,
                    'teacher_level' => $level,
                    'learning_method' => $method,
                ]);
                $rate->category_rate = $amount;
                $rate->created_by_admin_id = Admin::query()->where('user_id', $admin->user_id)->value('admin_id');
                $rate->save();

                // Populate compatibility columns used by the Admin tariff table.
                DB::table('master_tariffs')->where('rate_id', $rate->rate_id)->update([
                    'grade_level' => $level,
                    'method' => $method,
                    'base_rate' => $amount,
                    'created_at' => now(),
                ]);
            }
        }
    }

    private function seedPendingTutor(): void
    {
        $user = $this->user('calon.tutor@kawalbelajar.id', 'Alya Rosaan, S.T.', 'teacher');
        $user->forceFill(['is_active' => false])->save();
        $paths = [
            'cv_url' => 'demo/pending-tutor/cv-alya.pdf',
            'identity_document_url' => 'demo/pending-tutor/ktm-alya.pdf',
            'transcript_url' => 'demo/pending-tutor/khs-alya.pdf',
        ];
        foreach ($paths as $path) {
            Storage::disk('public')->put($path, "%PDF-1.4\n% Demo pending tutor document\n%%EOF");
        }

        $cvDocument = $this->document($user, 'CV Alya Rosaan', $paths['cv_url'], 'cv');
        Teacher::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'cv_document_id' => $cvDocument->document_id,
                'cv_url' => $paths['cv_url'],
                'identity_document_url' => $paths['identity_document_url'],
                'transcript_url' => $paths['transcript_url'],
                'university' => 'Universitas Lambung Mangkurat',
                'study_program' => 'Teknik Informatika',
                'semester' => 'Semester 8',
                'focus_subject' => 'Matematika dan Informatika',
                'requested_levels' => ['SMP', 'SMA'],
                'approved_levels' => [],
                'verified_levels' => [],
                'cv_status' => 'pending',
                'teacher_level' => 'junior',
                'default_location' => 'Banjarmasin',
                'wallet_balance' => 0,
            ],
        )->forceFill(['is_active' => false])->save();

        $this->document($user, 'KTM Alya Rosaan', $paths['identity_document_url'], 'identity');
        $this->document($user, 'KHS Alya Rosaan', $paths['transcript_url'], 'transcript');
    }

    private function document(User $user, string $name, string $path, string $category): Document
    {
        return Document::query()->updateOrCreate(
            ['uploaded_by' => $user->user_id, 'document_name' => $name],
            ['file_url' => $path, 'category' => $category, 'created_at' => now()],
        );
    }

    private function seedUpgradeRequest(Teacher $teacher): void
    {
        $path = 'demo/upgrade/sertifikat-umum.pdf';
        Storage::disk('public')->put($path, "%PDF-1.4\n% Demo certificate for level upgrade\n%%EOF");
        $document = $this->document($teacher->user, 'Sertifikat Upgrade UMUM — M. Arifin', $path, 'certificate');

        TeacherLevelRequest::query()->updateOrCreate(
            ['teacher_id' => $teacher->teacher_id, 'requested_level' => 'UMUM'],
            [
                'supporting_document_id' => $document->document_id,
                'supporting_document_path' => $path,
                'request_status' => 'pending_upgrade',
                'created_at' => now(),
            ],
        );
    }

    private function seedSettledStudentJourney(
        Student $student,
        Teacher $teacher,
        ModulePackage $package,
        TeacherAvailabilitySlot $slot,
    ): void {
        $order = Order::query()->updateOrCreate(
            ['invoice_number' => 'KB-DEMO-ATHAYA-4SESI'],
            [
                'student_id' => $student->student_id,
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'squad_id' => null,
                'learning_method' => 'home_visit',
                'session_package' => 4,
                'session_package_count' => 4,
                'selected_slot_id' => $slot->slot_id,
                'total_amount' => 300000,
                'personal_amount' => 300000,
                'discount_percentage' => 0,
                'payment_status' => 'settled',
                'order_status' => 'confirmed',
                'order_date' => now()->subDays(7),
                'created_at' => now()->subDays(7),
            ],
        );

        Payment::query()->updateOrCreate(
            ['order_id' => $order->order_id],
            [
                'xendit_invoice_id' => 'demo-invoice-athaya-4sesi',
                'payment_method' => 'qris',
                'amount' => 300000,
                'payment_status' => 'settled',
                'paid_at' => now()->subDays(7),
                'created_at' => now()->subDays(7),
            ],
        );

        $starts = [
            1 => now()->subDays(7)->setTime(18, 0),
            2 => now()->subMinutes(10),
            3 => now()->addDays(7)->setTime(18, 0),
            4 => now()->addDays(14)->setTime(18, 0),
        ];
        foreach ($starts as $number => $start) {
            $schedule = LearningSchedule::query()->updateOrCreate(
                ['teacher_id' => $teacher->teacher_id, 'package_id' => $package->package_id, 'start_time' => $start],
                [
                    'slot_id' => $slot->slot_id,
                    'method' => 'home_visit',
                    'location' => self::ADDRESS,
                    'end_time' => $start->copy()->addMinutes(90),
                    'schedule_status' => $number === 1 ? 'completed' : 'scheduled',
                ],
            );
            $material = LearningMaterial::query()
                ->where('package_id', $package->package_id)
                ->where('session_number', $number)
                ->firstOrFail();
            $isCompleted = $number === 1;

            $session = LearningSession::query()->updateOrCreate(
                ['order_id' => $order->order_id, 'session_number' => $number],
                [
                    'student_id' => $student->student_id,
                    'teacher_id' => $teacher->teacher_id,
                    'schedule_id' => $schedule->schedule_id,
                    'material_id' => $material->material_id,
                    'scheduled_at' => $start,
                    'duration_minutes' => 90,
                    'learning_method' => 'home_visit',
                    'session_status' => $isCompleted ? 'completed' : 'scheduled',
                    'status' => $isCompleted ? 'completed' : 'scheduled',
                    'zoom_meeting_url' => 'https://zoom.us/j/9876543210',
                    'onsite_address' => self::ADDRESS,
                    'student_address' => self::ADDRESS,
                    'latitude' => -3.2956,
                    'longitude' => 114.5912,
                    'address_landmark' => 'Komplek Kayu Tangi II',
                    'teacher_clock_in' => $isCompleted ? $start->copy() : null,
                    'teacher_clock_out' => $isCompleted ? $start->copy()->addMinutes(90) : null,
                    'absensi_masuk_guru' => $isCompleted ? $start->copy() : null,
                    'absensi_keluar_guru' => $isCompleted ? $start->copy()->addMinutes(90) : null,
                ],
            );

            if ($isCompleted) {
                EvaluationScore::query()->updateOrCreate(
                    ['session_id' => $session->session_id, 'student_id' => $student->student_id],
                    [
                        'pretest_score' => 60,
                        'posttest_score' => 88,
                        'progress_percentage' => 28,
                        'teacher_notes' => 'Athaya menunjukkan pemahaman yang baik dan mampu menerapkan konsep dengan tepat.',
                        'created_at' => $start->copy()->addMinutes(90),
                    ],
                );
                StudentAttendance::query()->updateOrCreate(
                    ['session_id' => $session->session_id, 'student_id' => $student->student_id],
                    ['attendance_time' => $start, 'status' => 'present'],
                );
                TeacherWorkLog::query()->updateOrCreate(
                    ['session_id' => $session->session_id],
                    [
                        'teacher_id' => $teacher->teacher_id,
                        'duration_minutes' => 90,
                        'gross_session_amount' => 75000,
                        'teacher_earnings' => 18750,
                        'payroll_status' => 'pending',
                        'payout_scheduled_date' => Carbon::now()->addDays(7)->toDateString(),
                        'created_at' => $start->copy()->addMinutes(90),
                    ],
                );
            } else {
                // Keep demo sessions 2-4 in a clean, repeatable pre-test state.
                EvaluationScore::query()->updateOrCreate(
                    ['session_id' => $session->session_id, 'student_id' => $student->student_id],
                    ['pretest_score' => null, 'posttest_score' => null, 'progress_percentage' => null, 'teacher_notes' => null],
                );
                StudentAttendance::query()->where('session_id', $session->session_id)
                    ->where('student_id', $student->student_id)->delete();
                TeacherWorkLog::query()->where('session_id', $session->session_id)->delete();
            }
        }
    }
}
