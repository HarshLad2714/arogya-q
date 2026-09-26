<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class DoctorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ClinicAdmin;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'digits:10', 'unique:users,mobile'],
            'email' => ['nullable', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'specialization' => ['required', 'string', 'max:120'],
            'qualification' => ['required', 'string', 'max:160'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'consultation_fee' => ['required', 'integer', 'min:0'],
            'room' => ['required', 'string', 'max:40'],
            'bio' => ['nullable', 'string', 'max:800'],
            'max_tokens_per_day' => ['required', 'integer', 'min:1', 'max:200'],
            'avg_consultation_minutes' => ['required', 'integer', 'min:5', 'max:60'],
        ];
    }
}
