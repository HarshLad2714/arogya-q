<?php

namespace App\Notifications;

use App\Models\Token;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(public Token $token) {}

    public function via(object $notifiable): array
    {
        return [SmsChannel::class, WhatsappChannel::class, 'database'];
    }

    public function toSms(object $notifiable): array
    {
        return [
            'type' => 'cancellation',
            'message' => $this->message($notifiable),
        ];
    }

    public function toWhatsapp(object $notifiable): array
    {
        return $this->toSms($notifiable);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('ui.booking.cancelled'),
            'message' => $this->message($notifiable),
        ];
    }

    private function message(object $notifiable): string
    {
        return __('alerts.cancelled', [
            'token' => $this->token->token_number,
            'date' => $this->token->date?->format('d M Y'),
        ], $notifiable->language_pref ?? app()->getLocale());
    }
}
