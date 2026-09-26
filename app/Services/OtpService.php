<?php

namespace App\Services;

use App\Exceptions\OtpException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    public function issue(string $mobile, string $purpose): void
    {
        $rateKey = 'otp-send:'.$mobile;

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw new OtpException(__('ui.auth.otp_throttled'));
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($mobile), [
            'hash' => Hash::make($code),
            'purpose' => $purpose,
        ], now()->addMinutes((int) config('arogya.otp.ttl_minutes', 5)));

        if (config('arogya.otp.demo')) {
            Cache::put($this->demoKey($mobile), $code, now()->addMinutes(5));
            Log::info('Demo OTP issued', ['mobile' => $mobile, 'purpose' => $purpose, 'code' => $code]);
        }

        RateLimiter::hit($rateKey, 600);

        $user = User::query()->where('mobile', $mobile)->first();
        $message = __('alerts.otp', ['code' => $code], $user->language_pref ?? app()->getLocale());

        if ($user) {
            app(SmsService::class)->send($user, $message, 'otp');
        }
    }

    public function verify(string $mobile, string $code, string $purpose): bool
    {
        $payload = Cache::get($this->cacheKey($mobile));

        if (! is_array($payload) || ($payload['purpose'] ?? null) !== $purpose) {
            return false;
        }

        if (! Hash::check($code, (string) ($payload['hash'] ?? ''))) {
            return false;
        }

        Cache::forget($this->cacheKey($mobile));
        Cache::forget($this->demoKey($mobile));

        return true;
    }

    public function demoCode(string $mobile): ?string
    {
        $code = Cache::get($this->demoKey($mobile));

        return is_string($code) ? $code : null;
    }

    private function cacheKey(string $mobile): string
    {
        return 'otp:'.$mobile;
    }

    private function demoKey(string $mobile): string
    {
        return 'otp-demo:'.$mobile;
    }
}
