<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Token;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    public function csv(Clinic $clinic, string $from, string $to): StreamedResponse
    {
        $tokens = $this->rows($clinic, $from, $to);
        $filename = 'arogyaq-'.$clinic->slug.'-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($tokens) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Token', 'Patient', 'Mobile', 'Doctor', 'Status', 'Type', 'Amount', 'Payment']);

            foreach ($tokens as $token) {
                fputcsv($out, [
                    $token->date->toDateString(),
                    $token->token_number,
                    $token->patient?->name,
                    $token->patient?->mobile,
                    $token->doctor?->user?->name,
                    $token->status->value,
                    $token->booking_type->value,
                    $token->payment?->amount,
                    $token->payment?->status?->value,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Token>
     */
    public function rows(Clinic $clinic, string $from, string $to)
    {
        return Token::query()
            ->with(['patient', 'doctor.user', 'payment'])
            ->where('clinic_id', $clinic->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('token_number')
            ->get();
    }
}
