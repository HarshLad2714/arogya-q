<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

class Navigation
{
    /**
     * @return array<int, array{route: string, match: string, label: string}>
     */
    public static function items(User $user): array
    {
        return match ($user->role) {
            UserRole::Patient => [
                ['route' => 'patient.dashboard', 'match' => 'patient.dashboard', 'label' => __('ui.nav.dashboard')],
                ['route' => 'patient.history', 'match' => 'patient.history', 'label' => __('ui.patient.history')],
                ['route' => 'patient.payments', 'match' => 'patient.payments', 'label' => __('ui.patient.payments')],
                ['route' => 'clinics.index', 'match' => 'clinics.*', 'label' => __('ui.nav.clinics')],
            ],
            UserRole::Doctor => [
                ['route' => 'doctor.dashboard', 'match' => 'doctor.*', 'label' => __('ui.doctor.queue')],
            ],
            UserRole::Receptionist => [
                ['route' => 'desk.dashboard', 'match' => 'desk.*', 'label' => __('ui.desk.title')],
            ],
            UserRole::ClinicAdmin => [
                ['route' => 'clinic.dashboard', 'match' => 'clinic.dashboard', 'label' => __('ui.nav.dashboard')],
                ['route' => 'clinic.doctors', 'match' => 'clinic.doctors*', 'label' => __('ui.panel.doctors')],
                ['route' => 'clinic.reviews', 'match' => 'clinic.reviews', 'label' => __('ui.panel.reviews')],
                ['route' => 'clinic.reports', 'match' => 'clinic.reports*', 'label' => __('ui.panel.reports')],
                ['route' => 'clinic.profile', 'match' => 'clinic.profile*', 'label' => __('ui.panel.profile')],
            ],
            UserRole::SuperAdmin => [
                ['route' => 'platform.dashboard', 'match' => 'platform.dashboard', 'label' => __('ui.nav.dashboard')],
                ['route' => 'platform.clinics', 'match' => 'platform.clinics', 'label' => __('ui.platform.clinics')],
            ],
        };
    }
}
