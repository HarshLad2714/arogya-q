<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['token_id', 'doctor_id', 'patient_id', 'medicines', 'notes', 'follow_up_date'])]
class Prescription extends Model
{
    protected function casts(): array
    {
        return [
            'medicines' => 'array',
            'follow_up_date' => 'date',
        ];
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
