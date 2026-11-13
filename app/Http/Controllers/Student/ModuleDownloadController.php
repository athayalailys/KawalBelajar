<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EvaluationScore;
use App\Models\LearningMaterial;
use App\Models\LearningSession;
use App\Models\StudentModuleDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ModuleDownloadController extends Controller
{
    public function download(Request $request, string $sessionId, string $materialId): Response
    {
        $student = $request->user()?->student;
        abort_unless($student, 403);

        $session = LearningSession::query()
            ->where('session_id', $sessionId)
            ->where('student_id', $student->student_id)
            ->where('material_id', $materialId)
            ->with('material')
            ->firstOrFail();

        $pretestComplete = EvaluationScore::query()
            ->where('session_id', $session->session_id)
            ->where('student_id', $student->student_id)
            ->whereNotNull('pretest_score')
            ->exists();
        abort_unless($pretestComplete, 403, 'Selesaikan tes awal sebelum mengunduh modul sesi.');

        /** @var LearningMaterial $material */
        $material = $session->material;
        abort_unless($material && filled($material->file_or_url), 404);

        StudentModuleDownload::query()->firstOrCreate(
            ['student_id' => $student->student_id, 'session_id' => $session->session_id, 'material_id' => $material->material_id],
            ['downloaded_at' => now()],
        );

        if (Storage::disk('public')->exists($material->file_or_url)) {
            return Storage::disk('public')->download($material->file_or_url, basename($material->file_or_url));
        }

        abort_unless(filter_var($material->file_or_url, FILTER_VALIDATE_URL), 404);

        return redirect()->away($material->file_or_url);
    }
}
