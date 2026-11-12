<?php

namespace Database\Seeders;

use App\Models\LearningSession;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data fixture untuk uji manual black-box alur ruang kelas pembelajar.
 * Jalankan secara eksplisit; seeder ini tidak dipanggil oleh DatabaseSeeder.
 */
class ClassroomBlackBoxSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DashboardE2ESeeder::class);

        $session = LearningSession::query()
            ->with(['order', 'schedule', 'teacher'])
            ->whereHas('order.student.user', fn ($query) => $query->where('email', 'siswaA@kawalbelajar.id'))
            ->firstOrFail();

        // Reset only assessment/attendance state for this one demo session so the flow can be repeated.
        DB::table('student_answers')->where('session_id', $session->session_id)->delete();
        DB::table('student_attendances')->where('session_id', $session->session_id)->delete();
        DB::table('evaluation_scores')->where('session_id', $session->session_id)->delete();

        $startAt = Carbon::now()->subMinute()->startOfMinute();
        $session->forceFill([
            'scheduled_at' => $startAt,
            'duration_minutes' => 60,
            'session_status' => 'scheduled',
            'status' => 'scheduled',
            'teacher_clock_in' => null,
            'teacher_clock_out' => null,
            'absensi_masuk_guru' => null,
            'absensi_keluar_guru' => null,
            'zoom_meeting_url' => 'https://meet.example.test/blackbox-session',
        ])->save();

        if ($session->schedule) {
            $session->schedule->forceFill([
                'start_time' => $startAt,
                'end_time' => $startAt->copy()->addHour(),
                'schedule_status' => 'scheduled',
            ])->save();
        }

        $teacherId = $session->teacher_id ?? $session->order?->teacher_id;
        $packageId = $session->order?->package_id;
        abort_if(!$teacherId || !$packageId, 500, 'Data tutor atau paket demo tidak lengkap.');

        QuestionBank::query()->updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'package_id' => $packageId,
                'exam_type' => 'pre_test',
                'category' => 'BLACKBOX',
            ],
            [
                'question_text' => 'Untuk memulai pengujian, pilih jawaban A.',
                'option_a' => 'Jawaban benar',
                'option_b' => 'Jawaban lain',
                'option_c' => 'Jawaban lain',
                'option_d' => 'Jawaban lain',
                'answer_key' => 'A',
            ],
        );

        QuestionBank::query()->updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'package_id' => $packageId,
                'exam_type' => 'post_test',
                'category' => 'BLACKBOX',
            ],
            [
                'question_text' => 'Untuk menyelesaikan pengujian, pilih jawaban A.',
                'option_a' => 'Jawaban benar',
                'option_b' => 'Jawaban lain',
                'option_c' => 'Jawaban lain',
                'option_d' => 'Jawaban lain',
                'answer_key' => 'A',
            ],
        );

        $this->command?->info('Fixture black-box ruang kelas siap.');
        $this->command?->line('Pembelajar: siswaA@kawalbelajar.id / password');
        $this->command?->line('Tutor: tutor@kawalbelajar.id / password');
        $this->command?->line('ID sesi: '.$session->session_id);
        $this->command?->line('Jadwal sesi: '.$startAt->toDateTimeString().' (sesi sudah dapat dimulai)');
    }
}
