<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class WalkInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Receptionist;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'digits:10'],
            'symptoms' => ['nullable', 'string', 'max:500'],
            'date' => ['nullable', 'date'],
        ];
    }
}
