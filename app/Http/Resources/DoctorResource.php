<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'specialization' => $this->specialization,
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'consultation_fee' => $this->consultation_fee,
            'room' => $this->room,
            'rating' => round((float) ($this->reviews_avg_rating ?? 0), 1),
        ];
    }
}
