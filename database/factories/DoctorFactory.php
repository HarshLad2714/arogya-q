<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'user_id' => User::factory()->state(['role' => UserRole::Doctor]),
            'specialization' => 'General Medicine',
            'qualification' => 'MBBS',
            'experience_years' => 8,
            'consultation_fee' => 400,
            'room' => 'Room 1',
            'bio' => 'Neighbourhood physician.',
            'max_tokens_per_day' => 40,
            'is_active' => true,
        ];
    }
}
