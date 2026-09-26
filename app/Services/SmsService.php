<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsService
{
    public function send(User $user, string $message, string $type): bool
    {
        $driver = (string) config('services.sms.driver', 'log');
        $status = 'sent';
        $meta = ['driver' => $driver];

        if (in_array($driver, ['msg91', 'fast2sms', 'twilio'], true) && ! config('services.sms.key')) {
            $driver = 'log';
            $meta['note'] = 'missing_key_fallback_log';
        }

        try {
            match ($driver) {
                'msg91' => $this->msg91($user->mobile, $message),
                'fast2sms' => $this->fast2sms($user->mobile, $message),
                'twilio' => $this->twilio($user->mobile, $message),
                default => Log::info('SMS', ['to' => $user->mobile, 'type' => $type, 'message' => $message]),
            };
        } catch (Throwable $exception) {
            $status = 'failed';
            $meta['error'] = $exception->getMessage();
            Log::error('SMS failed', $meta);
        }

        NotificationLog::query()->create([
            'user_id' => $user->id,
            'channel' => 'sms',
            'type' => $type,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
            'meta' => $meta,
        ]);

        return $status === 'sent';
    }

    private function msg91(string $mobile, string $message): void
    {
        Http::withHeaders(['authkey' => config('services.sms.key')])
            ->post('https://control.msg91.com/api/v5/flow/', [
                'template_id' => config('services.sms.template'),
                'short_url' => '0',
                'recipients' => [[
                    'mobiles' => '91'.$mobile,
                    'message' => $message,
                ]],
            ])
            ->throw();
    }

    private function fast2sms(string $mobile, string $message): void
    {
        Http::withHeaders(['authorization' => config('services.sms.key')])
            ->post('https://www.fast2sms.com/dev/bulkV2', [
                'route' => 'q',
                'message' => $message,
                'language' => 'english',
                'flash' => 0,
                'numbers' => $mobile,
            ])
            ->throw();
    }

    private function twilio(string $mobile, string $message): void
    {
        $sid = config('services.sms.sid');

        Http::withBasicAuth((string) $sid, (string) config('services.sms.key'))
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => config('services.sms.from'),
                'To' => '+91'.$mobile,
                'Body' => $message,
            ])
            ->throw();
    }
}
