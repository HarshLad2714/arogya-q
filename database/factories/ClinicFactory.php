<?php

namespace Database\Factories;

use App\Enums\ClinicStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company().' Clinic';

        return [
            'admin_id' => User::factory()->state(['role' => UserRole::ClinicAdmin]),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'specialty' => 'General Medicine',
            'description' => fake()->sentence(),
            'address' => fake()->streetAddress(),
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'latitude' => 23.03,
            'longitude' => 72.57,
            'phone' => fake()->numerify('9#########'),
            'status' => ClinicStatus::Approved,
            'services' => ['OPD'],
            'cancel_cutoff_minutes' => 30,
            'refund_percent' => 100,
            'approved_at' => now(),
        ];
    }
}
