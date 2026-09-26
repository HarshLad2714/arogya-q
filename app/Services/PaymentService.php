<?php

namespace App\Services;

use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * @return array{mode: string, payment_id: int, order?: array<string, mixed>, key?: string}
     */
    public function startOnline(Payment $payment): array
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (! $key || ! $secret) {
            return ['mode' => 'demo', 'payment_id' => $payment->id];
        }

        $response = Http::withBasicAuth((string) $key, (string) $secret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $payment->amount * 100,
                'currency' => 'INR',
                'receipt' => 'token_'.$payment->token_id,
            ]);

        if ($response->successful()) {
            $payment->update([
                'transaction_id' => $response->json('id'),
                'gateway' => 'razorpay',
            ]);

            return [
                'mode' => 'razorpay',
                'payment_id' => $payment->id,
                'order' => $response->json(),
                'key' => (string) $key,
            ];
        }

        return ['mode' => 'demo', 'payment_id' => $payment->id];
    }

    public function markPaid(Payment $payment, ?string $transactionId = null, string $gateway = 'demo'): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Paid,
            'mode' => $payment->mode ?: PaymentMode::Online,
            'transaction_id' => $transactionId ?: ('DEMO-'.Str::upper(Str::random(8))),
            'gateway' => $gateway,
        ]);

        return $payment->fresh();
    }

    public function markCash(Payment $payment): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Paid,
            'mode' => PaymentMode::Cash,
            'gateway' => 'cash',
            'transaction_id' => 'CASH-'.$payment->id,
        ]);

        return $payment->fresh();
    }
}
