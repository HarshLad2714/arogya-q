<?php

namespace App\Services;

use App\Enums\TokenStatus;
use App\Models\Token;
use App\Notifications\QueuePositionAlert;

class NotificationService
{
    public function __construct(private WaitTimeCalculator $wait) {}

    public function announceQueue(int $doctorId, string $date, int $current): void
    {
        $tokens = Token::query()
            ->with(['patient', 'doctor.user', 'clinic'])
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->whereIn('status', [TokenStatus::Booked, TokenStatus::Arrived, TokenStatus::InProgress])
            ->get();

        foreach ($tokens as $token) {
            if ($token->token_number === $current) {
                $this->once($token, 'now');

                continue;
            }

            $ahead = $this->wait->ahead($token->token_number, $current);

            if (in_array($ahead, config('arogya.alerts.thresholds'), true)) {
                $this->once($token, (string) $ahead);
            }
        }
    }

    private function once(Token $token, string $key): void
    {
        if ($token->last_alert === $key || ! $token->patient) {
            return;
        }

        $token->update(['last_alert' => $key]);
        $token->patient->notify(new QueuePositionAlert($token, $key));
    }
}
