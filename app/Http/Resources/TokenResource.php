<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_code' => $this->public_code,
            'token_number' => $this->token_number,
            'date' => $this->date?->toDateString(),
            'status' => $this->status?->value,
            'booking_type' => $this->booking_type?->value,
            'estimated_wait_minutes' => $this->estimated_wait_minutes,
            'track_url' => url('/track/'.$this->public_code),
            'doctor' => $this->whenLoaded('doctor', fn () => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->user?->name,
                'room' => $this->doctor->room,
            ]),
            'clinic' => $this->whenLoaded('clinic', fn () => [
                'name' => $this->clinic->name,
                'slug' => $this->clinic->slug,
            ]),
        ];
    }
}
