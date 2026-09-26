<?php

namespace App\Models;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'token_id',
    'patient_id',
    'clinic_id',
    'amount',
    'mode',
    'status',
    'transaction_id',
    'gateway',
    'refund_amount',
    'refund_status',
])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'mode' => PaymentMode::class,
            'status' => PaymentStatus::class,
            'amount' => 'integer',
            'refund_amount' => 'integer',
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

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
