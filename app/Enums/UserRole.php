<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case ClinicAdmin = 'clinic_admin';
    case Doctor = 'doctor';
    case Receptionist = 'receptionist';
    case Patient = 'patient';

    public function label(): string
    {
        return __('ui.roles.'.$this->value);
    }
}
