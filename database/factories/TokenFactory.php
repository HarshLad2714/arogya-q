<?php

namespace Database\Factories;

use App\Enums\BookingType;
use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Token>
 */
class TokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => fn (array $attributes) => Doctor::query()->find($attributes['doctor_id'])?->clinic_id,
            'doctor_id' => Doctor::factory(),
            'patient_id' => User::factory()->state(['role' => UserRole::Patient]),
            'token_number' => 1,
            'date' => now()->toDateString(),
            'status' => TokenStatus::Booked,
            'booking_type' => BookingType::Online,
            'symptoms' => 'Fever',
            'estimated_wait_minutes' => 12,
        ];
    }
}
