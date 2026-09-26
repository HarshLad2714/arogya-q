<?php

namespace App\Notifications\Channels;

use App\Services\WhatsappService;
use Illuminate\Notifications\Notification;

class WhatsappChannel
{
    public function __construct(private WhatsappService $whatsapp) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsapp')) {
            return;
        }

        $payload = $notification->toWhatsapp($notifiable);

        if (! is_array($payload) || empty($notifiable->mobile)) {
            return;
        }

        $this->whatsapp->send($notifiable, (string) $payload['message'], (string) ($payload['type'] ?? 'alert'));
    }
}
