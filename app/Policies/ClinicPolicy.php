<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\User;

class ClinicPolicy
{
    public function update(User $user, Clinic $clinic): bool
    {
        return $user->role === UserRole::SuperAdmin
            || ($user->role === UserRole::ClinicAdmin && $user->id === $clinic->admin_id);
    }

    public function approve(User $user, Clinic $clinic): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }
}
