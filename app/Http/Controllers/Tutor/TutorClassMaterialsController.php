<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\ModulePackage;
use App\Models\QuestionBank;
use App\Models\Teacher;
use App\Models\TutorClassDraft;
use App\Models\TutorClassSessionContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class TutorClassMaterialsController extends Controller
{
    public function index(Request $request, string $packageId): View
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        $plannedSessions = $this->plannedSessions($package);
        $selectedSession = (int) $request->query('session', 1);
        abort_unless(in_array($selectedSession, range(1, $plannedSessions), true), 404);

        $contents = TutorClassSessionContent::query()->where('package_id', $package->package_id)->get()->keyBy('session_number');
        $materials = LearningMaterial::query()->where('package_id', $package->package_id)->orderByDesc('created_at')->get()->groupBy('session_number');
        $questions = QuestionBank::query()->where('package_id', $package->package_id)
            ->where('exam_type', 'pre_test')->whereIn('session_number', range(1, $plannedSessions))
            ->orderBy('question_id')->get()->groupBy('session_number');

        $sessionStates = collect(range(1, $plannedSessions))->mapWithKeys(function (int $number) use ($contents, $materials, $questions) {
            $content = $contents->get($number);
            $material = $materials->get($number, collect())->first();
            $questionCount = $questions->get($number, collect())->count();
            $ready = filled($content?->topic) && filled($content?->learning_target) && $material && $questionCount >= 3;

            return [$number => [
                'content' => $content,
                'material' => $material,
                'question_count' => $questionCount,
                'ready' => $ready,
            ]];
        });

        return view('pages.tutor.class-materials', compact(
            'teacher', 'package', 'plannedSessions', 'selectedSession', 'sessionStates', 'contents', 'materials', 'questions'
        ));
    }

    public function saveSession(Request $request, string $packageId, int $sessionNumber): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        abort_unless(in_array($sessionNumber, range(1, $this->plannedSessions($package)), true), 404);

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'learning_target' => ['required', 'string', 'max:2000'],
            'module_file' => ['nullable', 'file', 'mimes:pdf,ppt,pptx', 'max:20480'],
            'questions' => ['nullable', 'array', 'max:50'],
            'questions.*.question_text' => ['required', 'string', 'max:5000'],
            'questions.*.option_a' => ['required', 'string', 'max:1000'],
            'questions.*.option_b' => ['required', 'string', 'max:1000'],
            'questions.*.option_c' => ['required', 'string', 'max:1000'],
            'questions.*.option_d' => ['required', 'string', 'max:1000'],
            'questions.*.answer_key' => ['required', Rule::in(['a', 'b', 'c', 'd'])],
        ]);

        DB::transaction(function () use ($request, $validated, $package, $teacher, $sessionNumber) {
            TutorClassSessionContent::query()->updateOrCreate(
                ['package_id' => $package->package_id, 'session_number' => $sessionNumber],
                ['topic' => $validated['topic'], 'learning_target' => $validated['learning_target']],
            );

            if ($request->hasFile('module_file')) {
                $file = $request->file('module_file');
                $path = $file->store('learning-modules', 'public');
                LearningMaterial::create([
                    'teacher_id' => $teacher->teacher_id,
                    'package_id' => $package->package_id,
                    'session_number' => $sessionNumber,
                    'title' => $file->getClientOriginalName(),
                    'material_type' => strtolower($file->getClientOriginalExtension()),
                    'file_or_url' => $path,
                    'created_at' => now(),
                ]);
            }

            foreach ($validated['questions'] ?? [] as $question) {
                foreach (['pre_test', 'post_test'] as $examType) {
                    QuestionBank::create([
                        'teacher_id' => $teacher->teacher_id,
                        'package_id' => $package->package_id,
                        'session_number' => $sessionNumber,
                        'exam_type' => $examType,
                        'category' => mb_substr($validated['topic'], 0, 30),
                        'question_text' => $question['question_text'],
                        'option_a' => $question['option_a'],
                        'option_b' => $question['option_b'],
                        'option_c' => $question['option_c'],
                        'option_d' => $question['option_d'],
                        'answer_key' => $question['answer_key'],
                    ]);
                }
            }
        });

        return redirect()->route('tutor.classes.materials', ['packageId' => $package->package_id, 'session' => $sessionNumber])
            ->with('status', 'Materi pertemuan berhasil disimpan.');
    }

    public function publish(Request $request, string $packageId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $package = $this->packageForTeacher($teacher, $packageId);
        $plannedSessions = $this->plannedSessions($package);
        $contents = TutorClassSessionContent::query()->where('package_id', $package->package_id)->get()->keyBy('session_number');
        $materials = LearningMaterial::query()->where('package_id', $package->package_id)->get()->groupBy('session_number');
        $questionCounts = QuestionBank::query()->where('package_id', $package->package_id)
            ->where('exam_type', 'pre_test')->selectRaw('session_number, count(*) as aggregate')
            ->groupBy('session_number')->pluck('aggregate', 'session_number');

        $incomplete = collect(range(1, $plannedSessions))->filter(function (int $number) use ($contents, $materials, $questionCounts) {
            $content = $contents->get($number);
            return ! filled($content?->topic) || ! filled($content?->learning_target)
                || $materials->get($number, collect())->isEmpty()
                || (int) $questionCounts->get($number, 0) < 3;
        });

        if ($incomplete->isNotEmpty()) {
            return back()->withErrors(['publication' => 'Lengkapi topik, target belajar, modul, dan minimal 3 soal pada setiap pertemuan. Pertemuan belum lengkap: '.$incomplete->map(fn ($number) => 'Sesi '.$number)->implode(', ').'.']);
        }

        $package->update(['is_active' => true, 'is_published' => true, 'is_open' => true]);
        TutorClassDraft::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('package_id', $package->package_id)
            ->delete();

        return redirect()->route('tutor.classes')
            ->with('clear_tutor_class_draft', $teacher->teacher_id)
            ->with('status', 'Kelas berhasil dipublikasikan dan dibuka untuk pendaftaran siswa.');
    }

    private function plannedSessions(ModulePackage $package): int
    {
        $count = (int) ($package->supported_session_packages ?? 1);
        return in_array($count, [1, 4], true) ? $count : 1;
    }

    private function packageForTeacher(Teacher $teacher, string $packageId): ModulePackage
    {
        return ModulePackage::query()->where('teacher_id', $teacher->teacher_id)->whereKey($packageId)->firstOrFail();
    }

    private function teacherFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        return Teacher::query()->with('user')->where('user_id', $user->user_id)->firstOrFail();
    }
}
