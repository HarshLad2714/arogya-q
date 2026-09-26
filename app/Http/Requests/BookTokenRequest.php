<?php

namespace App\Http\Requests;

use App\Enums\PaymentMode;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Patient;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'symptoms' => ['nullable', 'string', 'max:500'],
            'pay_mode' => ['required', Rule::enum(PaymentMode::class)],
        ];
    }
}
