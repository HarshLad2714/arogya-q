<?php

namespace App\Notifications;

use App\Models\Token;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification
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
            'type' => 'booking_confirmation',
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
            'title' => __('ui.booking.confirmed'),
            'message' => $this->message($notifiable),
            'url' => url('/track/'.$this->token->public_code),
        ];
    }

    private function message(object $notifiable): string
    {
        $locale = $notifiable->language_pref ?? app()->getLocale();

        return __('alerts.booked', [
            'token' => $this->token->token_number,
            'doctor' => $this->token->doctor?->user?->name,
            'clinic' => $this->token->clinic?->name,
            'date' => $this->token->date?->format('d M Y'),
            'link' => url('/track/'.$this->token->public_code),
        ], $locale);
    }
}
