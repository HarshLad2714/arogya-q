<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function __construct(private SmsService $sms) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $payload = $notification->toSms($notifiable);

        if (! is_array($payload) || empty($notifiable->mobile)) {
            return;
        }

        $this->sms->send($notifiable, (string) $payload['message'], (string) ($payload['type'] ?? 'alert'));
    }
}
