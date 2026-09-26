<?php

namespace App\Notifications;

use App\Models\Token;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsappChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QueuePositionAlert extends Notification
{
    use Queueable;

    public function __construct(public Token $token, public string $key) {}

    public function via(object $notifiable): array
    {
        return [SmsChannel::class, WhatsappChannel::class, 'database'];
    }

    public function toSms(object $notifiable): array
    {
        return [
            'type' => 'queue_'.$this->key,
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
            'title' => __('ui.queue.live'),
            'message' => $this->message($notifiable),
            'url' => url('/track/'.$this->token->public_code),
        ];
    }

    private function message(object $notifiable): string
    {
        $locale = $notifiable->language_pref ?? app()->getLocale();
        $replace = [
            'token' => $this->token->token_number,
            'doctor' => $this->token->doctor?->user?->name,
            'room' => $this->token->doctor?->room,
            'count' => $this->key,
            'link' => url('/track/'.$this->token->public_code),
        ];

        $alert = match ($this->key) {
            'now' => 'alerts.now',
            '0' => 'alerts.next',
            '1' => 'alerts.one',
            default => 'alerts.ahead',
        };

        return __($alert, $replace, $locale);
    }
}
