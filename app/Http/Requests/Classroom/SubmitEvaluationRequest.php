<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->teacher !== null;
    }

    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'uuid', 'distinct', Rule::exists('students', 'student_id')],
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'sick', 'permission'])],
            'posttest_score' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'teacher_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
