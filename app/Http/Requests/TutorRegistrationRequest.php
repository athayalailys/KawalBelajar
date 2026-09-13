<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TutorRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Step 1: Identitas & Akademik
            'full_name'        => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email',
            'phone_number'     => 'required|string|max:20',
            'domicile_address' => 'required|string|max:150',
            'university'       => 'required|string|max:100',
            'study_program'    => 'required|string|max:100',
            'semester'         => 'required|string|max:50',
            'selected_levels'  => 'required|array|min:1',
            'selected_levels.*'=> 'in:SD,SMP,SMA,UMUM',
            'focus_subject'    => 'required|string|max:150',

            // Step 2: Unggah Berkas Kualifikasi
            'cv_file'          => 'required|file|mimes:pdf|max:10240', // Wajib PDF, max 10MB
            'ktp_ktm_file'     => 'required|file|mimes:pdf,jpg,png|max:10240',
            'transcript_file'  => 'required|file|mimes:pdf|max:10240',
            'certificate_file' => 'nullable|file|mimes:pdf|max:10240',
            'video_link'       => 'nullable|url|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'cv_file.mimes' => 'File CV wajib diunggah dalam format PDF sebelum mendaftar!',
            'cv_file.required' => 'Berkas CV (Curriculum Vitae) wajib diunggah.',
            'selected_levels.required' => 'Pilih minimal satu jenjang pengajuan.',
        ];
    }
}