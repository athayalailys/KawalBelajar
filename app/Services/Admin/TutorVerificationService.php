<?php

namespace App\Services\Admin;

use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TutorVerificationService
{
    public function getDocumentPath(Teacher $teacher, string $type): string
    {
        // Menyesuaikan dengan nama properti yang Anda gunakan di Controller eksisting
        $path = match($type) {
            'cv' => $teacher->cv_url,
            'identity' => $teacher->identity_document_url,
            'transcript' => $teacher->transcript_url,
            'certificate' => $teacher->certificate_url,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            throw new NotFoundHttpException('Dokumen tidak ditemukan atau belum diunggah.');
        }

        return $path;
    }
}
