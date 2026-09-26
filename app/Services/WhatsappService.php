<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsappService
{
    public function send(User $user, string $message, string $type): bool
    {
        $driver = (string) config('services.whatsapp.driver', 'log');
        $status = 'sent';
        $meta = ['driver' => $driver];

        if (in_array($driver, ['meta', 'gupshup'], true) && ! config('services.whatsapp.key')) {
            $driver = 'log';
            $meta['note'] = 'missing_key_fallback_log';
        }

        try {
            match ($driver) {
                'meta' => $this->meta($user->mobile, $message),
                'gupshup' => $this->gupshup($user->mobile, $message),
                default => Log::info('WhatsApp', ['to' => $user->mobile, 'type' => $type, 'message' => $message]),
            };
        } catch (Throwable $exception) {
            $status = 'failed';
            $meta['error'] = $exception->getMessage();
            Log::error('WhatsApp failed', $meta);
        }

        NotificationLog::query()->create([
            'user_id' => $user->id,
            'channel' => 'whatsapp',
            'type' => $type,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
            'meta' => $meta,
        ]);

        return $status === 'sent';
    }

    private function meta(string $mobile, string $message): void
    {
        $phoneId = config('services.whatsapp.phone_id');

        Http::withToken((string) config('services.whatsapp.key'))
            ->post("https://graph.facebook.com/v21.0/{$phoneId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => '91'.$mobile,
                'type' => 'text',
                'text' => ['body' => $message],
            ])
            ->throw();
    }

    private function gupshup(string $mobile, string $message): void
    {
        Http::withHeaders(['apikey' => config('services.whatsapp.key')])
            ->asForm()
            ->post('https://api.gupshup.io/wa/api/v1/msg', [
                'channel' => 'whatsapp',
                'source' => config('services.whatsapp.source'),
                'destination' => '91'.$mobile,
                'message' => json_encode(['type' => 'text', 'text' => $message]),
                'src.name' => config('app.name'),
            ])
            ->throw();
    }
}
