<?php

namespace App\Services;

use App\Enums\ClinicStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClinicService
{
    public function register(array $owner, array $clinicData, ?UploadedFile $document = null): Clinic
    {
        return DB::transaction(function () use ($owner, $clinicData, $document) {
            $user = User::query()->create([
                'name' => $owner['name'],
                'mobile' => $owner['mobile'],
                'email' => $owner['email'] ?? null,
                'password' => $owner['password'],
                'role' => UserRole::ClinicAdmin,
                'language_pref' => $owner['language_pref'] ?? 'en',
                'is_active' => true,
                'mobile_verified_at' => now(),
            ]);

            $clinic = Clinic::query()->create([
                'admin_id' => $user->id,
                'name' => $clinicData['name'],
                'slug' => $this->uniqueSlug($clinicData['name']),
                'specialty' => $clinicData['specialty'],
                'description' => $clinicData['description'] ?? null,
                'address' => $clinicData['address'],
                'city' => $clinicData['city'],
                'state' => $clinicData['state'] ?? 'Gujarat',
                'pincode' => $clinicData['pincode'] ?? null,
                'latitude' => $clinicData['latitude'] ?? null,
                'longitude' => $clinicData['longitude'] ?? null,
                'phone' => $clinicData['phone'] ?? $owner['mobile'],
                'email' => $clinicData['email'] ?? $owner['email'] ?? null,
                'status' => ClinicStatus::Pending,
                'services' => $clinicData['services'] ?? ['OPD'],
                'cancel_cutoff_minutes' => 30,
                'refund_percent' => 100,
            ]);

            if ($document) {
                $path = $document->store('clinics/'.$clinic->id, 'public');
                $clinic->update(['documents' => [$path]]);
            }

            $user->update(['clinic_id' => $clinic->id]);

            return $clinic->fresh('admin');
        });
    }

    public function update(Clinic $clinic, array $data, ?UploadedFile $document = null): Clinic
    {
        if ($document) {
            $documents = $clinic->documents ?? [];
            $documents[] = $document->store('clinics/'.$clinic->id, 'public');
            $data['documents'] = $documents;
        }

        if (isset($data['services']) && is_string($data['services'])) {
            $data['services'] = array_values(array_filter(array_map('trim', explode(',', $data['services']))));
        }

        $clinic->update($data);

        return $clinic->fresh();
    }

    public function approve(Clinic $clinic): Clinic
    {
        $clinic->update([
            'status' => ClinicStatus::Approved,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return $clinic;
    }

    public function reject(Clinic $clinic, string $reason): Clinic
    {
        $clinic->update([
            'status' => ClinicStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return $clinic;
    }

    public function addReceptionist(Clinic $clinic, array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => UserRole::Receptionist,
            'language_pref' => 'en',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'mobile_verified_at' => now(),
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'clinic';
        $slug = $base;
        $i = 2;

        while (Clinic::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
