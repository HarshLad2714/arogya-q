<?php

namespace App\Models;

use App\Enums\ClinicStatus;
use Database\Factories\ClinicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'admin_id',
    'name',
    'slug',
    'specialty',
    'description',
    'address',
    'city',
    'state',
    'pincode',
    'latitude',
    'longitude',
    'phone',
    'email',
    'status',
    'rejection_reason',
    'documents',
    'photos',
    'services',
    'cancel_cutoff_minutes',
    'refund_percent',
    'approved_at',
])]
class Clinic extends Model
{
    /** @use HasFactory<ClinicFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ClinicStatus::class,
            'documents' => 'array',
            'photos' => 'array',
            'services' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'approved_at' => 'datetime',
        ];
    }

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', ClinicStatus::Approved);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
