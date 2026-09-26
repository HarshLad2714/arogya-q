<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'mobile' => fake()->unique()->numerify('9#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'mobile_verified_at' => now(),
            'password' => 'password',
            'role' => UserRole::Patient,
            'language_pref' => 'en',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
            'mobile_verified_at' => null,
        ]);
    }
}
