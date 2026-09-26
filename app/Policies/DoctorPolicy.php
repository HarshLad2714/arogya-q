<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Doctor;
use App\Models\User;

class DoctorPolicy
{
    public function update(User $user, Doctor $doctor): bool
    {
        return $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::ClinicAdmin && $user->clinic_id === $doctor->clinic_id);
    }
}
