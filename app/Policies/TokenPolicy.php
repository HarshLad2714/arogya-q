<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Token;
use App\Models\User;

class TokenPolicy
{
    public function view(User $user, Token $token): bool
    {
        return match ($user->role) {
            UserRole::SuperAdmin => true,
            UserRole::Patient => $token->patient_id === $user->id,
            UserRole::Doctor => $user->doctorProfile?->id === $token->doctor_id,
            UserRole::Receptionist, UserRole::ClinicAdmin => $user->clinic_id === $token->clinic_id,
        };
    }

    public function cancel(User $user, Token $token): bool
    {
        return $user->role === UserRole::Patient && $token->patient_id === $user->id;
    }

    public function prescribe(User $user, Token $token): bool
    {
        return $user->role === UserRole::Doctor && $user->doctorProfile?->id === $token->doctor_id;
    }

    public function manage(User $user, Token $token): bool
    {
        return in_array($user->role, [UserRole::Receptionist, UserRole::ClinicAdmin, UserRole::Doctor], true)
            && ($user->clinic_id === $token->clinic_id || $user->doctorProfile?->id === $token->doctor_id);
    }
}
