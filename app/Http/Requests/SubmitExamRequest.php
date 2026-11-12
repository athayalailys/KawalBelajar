<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->student !== null;
    }

    public function rules(): array
    {
        return [
            'exam_type' => ['required', 'string', 'in:pre_test,post_test'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'uuid', 'distinct'],
            // Empty answers are accepted so a timed exam can be submitted when time runs out.
            'answers.*.answer' => ['present', 'string', 'max:5000'],
        ];
    }
}
