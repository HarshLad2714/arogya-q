<?php

namespace App\Listeners;

use App\Events\TokenBooked;
use App\Notifications\BookingConfirmed;

class SendBookingNotification
{
    public function handle(TokenBooked $event): void
    {
        $event->token->loadMissing(['patient', 'doctor.user', 'clinic']);
        $event->token->patient?->notify(new BookingConfirmed($event->token));
    }
}
