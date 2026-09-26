<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private OtpService $otp) {}

    public function verifyPassword(string $mobile, string $password): ?User
    {
        $user = User::query()->where('mobile', $mobile)->first();

        if (! $user || ! $user->is_active || ! $user->password || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function attempt(string $mobile, string $password): ?User
    {
        $user = $this->verifyPassword($mobile, $password);

        if ($user) {
            $this->login($user);
        }

        return $user;
    }

    public function registerPatient(array $data): User
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => UserRole::Patient,
            'language_pref' => $data['language_pref'] ?? 'en',
            'is_active' => false,
        ]);

        $this->otp->issue($user->mobile, 'register');

        return $user;
    }

    public function login(User $user): void
    {
        Auth::login($user, true);
        session(['locale' => $user->language_pref ?: 'en']);
    }
}
