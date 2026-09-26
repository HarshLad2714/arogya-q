<?php

namespace App\Services;

use App\Enums\TokenStatus;
use App\Models\Prescription;
use App\Models\Token;

class PrescriptionService
{
    /**
     * @param  array<int, array{name: string, dosage?: string, duration?: string, timing?: string}>  $medicines
     */
    public function write(Token $token, array $medicines, ?string $notes, ?string $followUp): Prescription
    {
        $rows = array_values(array_filter($medicines, fn ($row) => filled($row['name'] ?? null)));

        $prescription = Prescription::query()->updateOrCreate(
            ['token_id' => $token->id],
            [
                'doctor_id' => $token->doctor_id,
                'patient_id' => $token->patient_id,
                'medicines' => $rows,
                'notes' => $notes,
                'follow_up_date' => $followUp,
            ],
        );

        if ($token->status !== TokenStatus::Completed) {
            $token->update(['status' => TokenStatus::Completed]);
        }

        return $prescription;
    }
}
