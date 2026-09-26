<?php

namespace App\Models;

use App\Enums\BookingType;
use App\Enums\TokenStatus;
use Database\Factories\TokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'public_code',
    'clinic_id',
    'doctor_id',
    'patient_id',
    'token_number',
    'date',
    'status',
    'booking_type',
    'symptoms',
    'estimated_wait_minutes',
    'last_alert',
    'cancelled_at',
    'cancel_reason',
])]
class Token extends Model
{
    /** @use HasFactory<TokenFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => TokenStatus::class,
            'booking_type' => BookingType::class,
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Token $token): void {
            if (! $token->public_code) {
                $token->public_code = (string) Str::uuid();
            }
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_code';
    }
}
