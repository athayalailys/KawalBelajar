<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'       => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number'    => ['required', 'string', 'max:20'],
            'password'        => ['required', 'string', 'min:8'],
            'education_level' => ['required', 'string', 'in:SD,SMP,SMA,UMUM'],
            'saved_address'   => ['nullable', 'string'],
            'landmark'        => ['nullable', 'string', 'max:255'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            // Field khusus pendampingan Orang Tua (Skenario 1)
            'parent_name'     => ['required_if:education_level,SD', 'nullable', 'string', 'max:255'],
            'parent_phone'    => ['required_if:education_level,SD', 'nullable', 'string', 'max:20'],
            'parent_pin'      => ['required_if:education_level,SD', 'nullable', 'digits:6'],
        ];
    }
}