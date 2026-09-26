<?php

namespace App\Services;

use App\Enums\ClinicStatus;
use App\Enums\PaymentStatus;
use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Token;
use App\Models\User;

class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function platform(): array
    {
        $revenue = (int) Payment::query()->where('status', PaymentStatus::Paid)->sum('amount');
        $sharePercent = (int) config('arogya.revenue_share', 10);

        return [
            'clinics' => Clinic::query()->count(),
            'approved' => Clinic::query()->where('status', ClinicStatus::Approved)->count(),
            'pending' => Clinic::query()->where('status', ClinicStatus::Pending)->count(),
            'patients' => User::query()->where('role', UserRole::Patient)->count(),
            'visits_today' => Token::query()->whereDate('date', today())->count(),
            'revenue' => $revenue,
            'share' => (int) round($revenue * $sharePercent / 100),
            'share_percent' => $sharePercent,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function clinic(Clinic $clinic, ?string $from = null, ?string $to = null): array
    {
        $from = $from ?: now()->startOfMonth()->toDateString();
        $to = $to ?: now()->toDateString();

        $tokens = Token::query()->where('clinic_id', $clinic->id)->whereBetween('date', [$from, $to]);
        $completed = (clone $tokens)->where('status', TokenStatus::Completed)->count();
        $noShow = (clone $tokens)->where('status', TokenStatus::NoShow)->count();
        $cancelled = (clone $tokens)->where('status', TokenStatus::Cancelled)->count();
        $total = (clone $tokens)->count();
        $denom = max(1, $completed + $noShow);

        $revenue = (int) Payment::query()
            ->where('clinic_id', $clinic->id)
            ->where('status', PaymentStatus::Paid)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount');

        $peak = Token::query()
            ->where('clinic_id', $clinic->id)
            ->whereDate('date', today())
            ->get()
            ->groupBy(fn (Token $token) => $token->created_at->format('G'));

        $hours = [];

        foreach (range(8, 20) as $hour) {
            $hours[] = [
                'hour' => $hour,
                'label' => sprintf('%02d:00', $hour),
                'count' => (int) ($peak->get((string) $hour)?->count() ?? 0),
            ];
        }

        $doctors = Doctor::query()
            ->with('user')
            ->withAvg('reviews', 'rating')
            ->where('clinic_id', $clinic->id)
            ->get()
            ->map(function (Doctor $doctor) use ($from, $to) {
                $visits = Token::query()->where('doctor_id', $doctor->id)->whereBetween('date', [$from, $to]);
                $ids = (clone $visits)->pluck('id');

                return [
                    'name' => $doctor->user?->name,
                    'specialization' => $doctor->specialization,
                    'booked' => (clone $visits)->count(),
                    'completed' => (clone $visits)->where('status', TokenStatus::Completed)->count(),
                    'rating' => round((float) ($doctor->reviews_avg_rating ?? 0), 1),
                    'revenue' => (int) Payment::query()->whereIn('token_id', $ids)->where('status', PaymentStatus::Paid)->sum('amount'),
                ];
            });

        return [
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'completed' => $completed,
            'no_show' => $noShow,
            'cancelled' => $cancelled,
            'no_show_rate' => (int) round($noShow / $denom * 100),
            'revenue' => $revenue,
            'hours' => $hours,
            'doctors' => $doctors,
        ];
    }
}
