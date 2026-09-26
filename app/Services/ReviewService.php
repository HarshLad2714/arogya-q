<?php

namespace App\Services;

use App\Enums\TokenStatus;
use App\Exceptions\BookingException;
use App\Models\Review;
use App\Models\Token;
use App\Models\User;

class ReviewService
{
    public function store(Token $token, User $patient, int $rating, ?string $comment): Review
    {
        if ($token->patient_id !== $patient->id || $token->status !== TokenStatus::Completed) {
            throw new BookingException(__('ui.booking.review_blocked'));
        }

        if ($token->review) {
            throw new BookingException(__('ui.booking.review_exists'));
        }

        return Review::query()->create([
            'token_id' => $token->id,
            'patient_id' => $patient->id,
            'doctor_id' => $token->doctor_id,
            'clinic_id' => $token->clinic_id,
            'rating' => $rating,
            'comment' => $comment,
        ]);
    }

    public function respond(Review $review, string $response): Review
    {
        $review->update([
            'response' => $response,
            'responded_at' => now(),
        ]);

        return $review;
    }
}
