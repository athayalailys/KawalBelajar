<?php

namespace App\Http\Requests\Classroom;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->teacher !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'uuid', Rule::exists('learning_sessions', 'session_id')],
        ];
    }
}
