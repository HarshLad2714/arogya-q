<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'doctor_id',
    'clinic_id',
    'service_date',
    'current_token',
    'total_booked',
    'avg_consultation_minutes',
    'room',
    'doctor_name',
])]
class QueueState extends Model
{
    protected function casts(): array
    {
        return [];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
