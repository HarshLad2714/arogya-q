<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Doctor;
    }

    public function rules(): array
    {
        return [
            'medicines' => ['required', 'array', 'min:1'],
            'medicines.*.name' => ['nullable', 'string', 'max:120'],
            'medicines.*.dosage' => ['nullable', 'string', 'max:80'],
            'medicines.*.duration' => ['nullable', 'string', 'max:80'],
            'medicines.*.timing' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'follow_up_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
