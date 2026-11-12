<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class BookingCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->student !== null;
    }

    public function rules(): array
    {
        return [
            'teacher_id'       => ['required', 'uuid', Rule::exists('teachers', 'teacher_id')->where('is_active', true)->where('cv_status', 'approved')],
            'package_id'       => ['required', 'uuid', Rule::exists('module_packages', 'package_id')->where('is_active', true)],
            'learning_method'  => ['required', 'string', 'in:home_visit,online_zoom'],
            'session_package'  => ['required', 'integer', 'in:1,4'],
            'format'           => ['required', 'string', 'in:solo,host,joiner'],
            'target_capacity'  => ['required_if:format,host', 'nullable', 'integer', 'min:2', 'max:20'],
            'squad_code'       => ['required_if:format,joiner', 'nullable', 'string', Rule::exists('squads', 'squad_code')->where('status', 'open')],
            'selected_slot_id' => ['required', 'uuid', Rule::exists('teacher_availability_slots', 'slot_id')->where('is_active', true)],
            'payment_method'   => ['sometimes', 'nullable', 'string', 'in:qris,virtual_account,credit_card'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('format') === 'host' && is_numeric($this->input('target_capacity'))) {
                $maximum = $this->input('learning_method') === 'home_visit' ? 5 : 20;
                if ((int) $this->input('target_capacity') > $maximum) {
                    $validator->errors()->add('target_capacity', 'Kapasitas squad melebihi batas metode belajar.');
                }
            }
        });
    }
}
