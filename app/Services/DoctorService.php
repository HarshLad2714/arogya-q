<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DoctorService
{
    public function create(Clinic $clinic, array $profile, array $schedules): Doctor
    {
        return DB::transaction(function () use ($clinic, $profile, $schedules) {
            $user = User::query()->create([
                'name' => $profile['name'],
                'mobile' => $profile['mobile'],
                'email' => $profile['email'] ?? null,
                'password' => $profile['password'],
                'role' => UserRole::Doctor,
                'language_pref' => 'en',
                'clinic_id' => $clinic->id,
                'is_active' => true,
                'mobile_verified_at' => now(),
            ]);

            $doctor = Doctor::query()->create([
                'clinic_id' => $clinic->id,
                'user_id' => $user->id,
                'specialization' => $profile['specialization'],
                'qualification' => $profile['qualification'],
                'experience_years' => $profile['experience_years'] ?? 0,
                'consultation_fee' => $profile['consultation_fee'] ?? 0,
                'room' => $profile['room'] ?? 'Room 1',
                'bio' => $profile['bio'] ?? null,
                'max_tokens_per_day' => $profile['max_tokens_per_day'] ?? 40,
                'is_active' => true,
            ]);

            $this->syncSchedules($doctor, $schedules, (int) ($profile['avg_consultation_minutes'] ?? 12));

            return $doctor->load('user', 'schedules');
        });
    }

    public function update(Doctor $doctor, array $profile, array $schedules): Doctor
    {
        return DB::transaction(function () use ($doctor, $profile, $schedules) {
            $doctor->user->update([
                'name' => $profile['name'],
                'mobile' => $profile['mobile'],
                'email' => $profile['email'] ?? $doctor->user->email,
            ]);

            if (! empty($profile['password'])) {
                $doctor->user->update(['password' => $profile['password']]);
            }

            $doctor->update([
                'specialization' => $profile['specialization'],
                'qualification' => $profile['qualification'],
                'experience_years' => $profile['experience_years'] ?? $doctor->experience_years,
                'consultation_fee' => $profile['consultation_fee'] ?? $doctor->consultation_fee,
                'room' => $profile['room'] ?? $doctor->room,
                'bio' => $profile['bio'] ?? $doctor->bio,
                'max_tokens_per_day' => $profile['max_tokens_per_day'] ?? $doctor->max_tokens_per_day,
                'is_active' => (bool) ($profile['is_active'] ?? $doctor->is_active),
            ]);

            $this->syncSchedules($doctor, $schedules, (int) ($profile['avg_consultation_minutes'] ?? 12));

            return $doctor->fresh(['user', 'schedules']);
        });
    }

    public function grantLeave(Doctor $doctor, string $date, ?string $reason): DoctorLeave
    {
        return DoctorLeave::query()->updateOrCreate(
            ['doctor_id' => $doctor->id, 'leave_date' => $date],
            ['reason' => $reason],
        );
    }

    /**
     * @param  array<int, array{day_of_week: int, start_time: string, end_time: string}>  $schedules
     */
    public function syncSchedules(Doctor $doctor, array $schedules, int $avgMinutes): void
    {
        $doctor->schedules()->delete();

        foreach ($schedules as $row) {
            DoctorSchedule::query()->create([
                'doctor_id' => $doctor->id,
                'day_of_week' => $row['day_of_week'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'avg_consultation_minutes' => $avgMinutes,
            ]);
        }
    }
}
