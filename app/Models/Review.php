<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'token_id',
    'patient_id',
    'doctor_id',
    'clinic_id',
    'rating',
    'comment',
    'response',
    'responded_at',
])]
class Review extends Model
{
    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
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
