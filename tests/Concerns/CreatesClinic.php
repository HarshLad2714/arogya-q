<?php

namespace Tests\Concerns;

use App\Enums\ClinicStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;

trait CreatesClinic
{
    /**
     * @return array{0: User, 1: Clinic, 2: User, 3: Doctor}
     */
    protected function makeClinic(array $clinic = [], array $doctor = []): array
    {
        $admin = User::factory()->create(['role' => UserRole::ClinicAdmin]);
        $clinicModel = Clinic::factory()->create(array_merge([
            'admin_id' => $admin->id,
            'status' => ClinicStatus::Approved,
            'approved_at' => now(),
            'cancel_cutoff_minutes' => 10,
        ], $clinic));
        $admin->update(['clinic_id' => $clinicModel->id]);

        $doctorUser = User::factory()->create([
            'role' => UserRole::Doctor,
            'clinic_id' => $clinicModel->id,
        ]);
        $doctorModel = Doctor::factory()->create(array_merge([
            'clinic_id' => $clinicModel->id,
            'user_id' => $doctorUser->id,
            'consultation_fee' => 300,
        ], $doctor));

        foreach (range(0, 6) as $day) {
            DoctorSchedule::query()->create([
                'doctor_id' => $doctorModel->id,
                'day_of_week' => $day,
                'start_time' => '09:00',
                'end_time' => '18:00',
                'avg_consultation_minutes' => 12,
            ]);
        }

        return [$admin, $clinicModel, $doctorUser, $doctorModel];
    }
}
