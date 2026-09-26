<?php

namespace App\Services;

use App\Enums\BookingType;
use App\Enums\ClinicStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentStatus;
use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Events\TokenBooked;
use App\Exceptions\BookingException;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Token;
use App\Models\User;
use App\Notifications\BookingCancelled;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TokenBookingService
{
    public function __construct(
        private QueueEngineService $queue,
        private WaitTimeCalculator $wait,
        private NotificationService $notifications,
    ) {}

    public function book(
        User $patient,
        Doctor $doctor,
        string $date,
        BookingType $type,
        ?string $symptoms,
        PaymentMode $payMode,
    ): Token {
        $doctor->loadMissing(['clinic', 'user', 'schedules', 'leaves']);

        $this->assertBookable($patient, $doctor, $date);

        $token = DB::transaction(function () use ($patient, $doctor, $date, $type, $symptoms, $payMode) {
            $nextNumber = ((int) Token::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', $date)
                ->lockForUpdate()
                ->max('token_number')) + 1;

            $avg = $this->averageMinutes($doctor, $date);
            $status = $this->queue->status($doctor->id, $date);
            $wait = $this->wait->minutes($nextNumber, (int) $status['current_token'], $avg);

            $token = Token::query()->create([
                'clinic_id' => $doctor->clinic_id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'token_number' => $nextNumber,
                'date' => $date,
                'status' => TokenStatus::Booked,
                'booking_type' => $type,
                'symptoms' => $symptoms,
                'estimated_wait_minutes' => $wait,
            ]);

            Payment::query()->create([
                'token_id' => $token->id,
                'patient_id' => $patient->id,
                'clinic_id' => $doctor->clinic_id,
                'amount' => (int) $doctor->consultation_fee,
                'mode' => $payMode,
                'status' => (int) $doctor->consultation_fee === 0 ? PaymentStatus::Paid : PaymentStatus::Pending,
                'gateway' => $payMode === PaymentMode::Online ? 'razorpay' : null,
            ]);

            $this->queue->sync([
                'doctor_id' => $doctor->id,
                'clinic_id' => $doctor->clinic_id,
                'date' => $date,
                'total_booked' => $nextNumber,
                'avg_consultation_minutes' => $avg,
                'room' => $doctor->room,
                'doctor_name' => $doctor->user?->name,
            ]);

            return $token->load(['patient', 'doctor.user', 'clinic', 'payment']);
        });

        TokenBooked::dispatch($token);

        return $token;
    }

    public function walkIn(User $receptionist, array $data): Token
    {
        $doctor = Doctor::query()->with(['clinic', 'user'])->findOrFail($data['doctor_id']);

        if ($receptionist->clinic_id !== $doctor->clinic_id) {
            throw new BookingException(__('ui.booking.not_allowed'));
        }

        $patient = User::query()->firstOrCreate(
            ['mobile' => $data['mobile']],
            [
                'name' => $data['name'],
                'role' => UserRole::Patient,
                'password' => Str::password(12),
                'language_pref' => 'gu',
                'is_active' => true,
                'mobile_verified_at' => now(),
            ],
        );

        if ($patient->role !== UserRole::Patient) {
            throw new BookingException(__('ui.booking.staff_mobile'));
        }

        if ($patient->wasRecentlyCreated === false && $patient->name === '') {
            $patient->update(['name' => $data['name']]);
        }

        return $this->book(
            $patient,
            $doctor,
            $data['date'] ?? now()->toDateString(),
            BookingType::WalkIn,
            $data['symptoms'] ?? null,
            PaymentMode::Cash,
        );
    }

    public function canCancel(Token $token): bool
    {
        if ($token->status !== TokenStatus::Booked) {
            return false;
        }

        $day = $token->date->copy()->startOfDay();

        if ($day->lt(now()->startOfDay())) {
            return false;
        }

        if ($day->gt(now()->startOfDay())) {
            return true;
        }

        $token->loadMissing('clinic');
        $status = $this->queue->status($token->doctor_id, $day->toDateString());
        $minutes = $this->wait->minutes(
            $token->token_number,
            (int) $status['current_token'],
            (int) $status['avg_consultation_minutes'],
        );

        return $minutes >= (int) ($token->clinic->cancel_cutoff_minutes ?? 30);
    }

    public function cancel(Token $token, ?string $reason = null): Token
    {
        $token->loadMissing(['clinic', 'doctor.user', 'patient', 'payment']);

        if (! $this->canCancel($token)) {
            throw new BookingException(__('ui.booking.cancel_blocked'));
        }

        return DB::transaction(function () use ($token, $reason) {
            $token->update([
                'status' => TokenStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            $payment = $token->payment;

            if ($payment && $payment->status === PaymentStatus::Paid) {
                $percent = (int) $token->clinic->refund_percent;
                $payment->update([
                    'refund_amount' => (int) round($payment->amount * $percent / 100),
                    'refund_status' => 'pending',
                    'status' => PaymentStatus::Refunded,
                ]);
            } elseif ($payment && $payment->status === PaymentStatus::Pending) {
                $payment->update(['status' => PaymentStatus::Failed]);
            }

            $token->patient?->notify(new BookingCancelled($token));

            return $token->fresh(['payment', 'doctor.user', 'clinic']);
        });
    }

    public function reschedule(Token $token, string $newDate): Token
    {
        $token->loadMissing(['patient', 'doctor', 'payment']);

        if ($token->date->toDateString() === $newDate) {
            throw new BookingException(__('ui.booking.same_date'));
        }

        $mode = $token->payment?->mode ?? PaymentMode::Cash;
        $patient = $token->patient;
        $doctor = $token->doctor;
        $symptoms = $token->symptoms;

        $this->cancel($token, 'Rescheduled');

        return $this->book($patient, $doctor, $newDate, BookingType::Online, $symptoms, $mode);
    }

    public function callNext(Doctor $doctor, ?string $date = null): array
    {
        $date ??= now()->toDateString();
        $doctor->loadMissing('user');
        $result = $this->queue->next($doctor->id, $date);
        $current = (int) $result['current_token'];

        Token::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->where('status', TokenStatus::InProgress)
            ->update(['status' => TokenStatus::Completed]);

        $token = Token::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->where('token_number', $current)
            ->first();

        if ($token && in_array($token->status, [TokenStatus::Booked, TokenStatus::Arrived], true)) {
            $token->update(['status' => TokenStatus::InProgress]);
        }

        $this->notifications->announceQueue($doctor->id, $date, $current);

        return [
            'current_token' => $current,
            'token' => $token?->fresh(['patient', 'doctor.user']),
        ];
    }

    public function markArrived(Token $token): Token
    {
        if (! in_array($token->status, [TokenStatus::Booked], true)) {
            throw new BookingException(__('ui.booking.not_allowed'));
        }

        $token->update(['status' => TokenStatus::Arrived]);

        return $token;
    }

    public function markNoShow(Token $token): Token
    {
        if (! in_array($token->status, [TokenStatus::Booked, TokenStatus::Arrived, TokenStatus::InProgress], true)) {
            throw new BookingException(__('ui.booking.not_allowed'));
        }

        $token->update(['status' => TokenStatus::NoShow]);

        return $token;
    }

    public function complete(Token $token): Token
    {
        $token->update(['status' => TokenStatus::Completed]);

        return $token;
    }

    private function assertBookable(User $patient, Doctor $doctor, string $date): void
    {
        if ($patient->role !== UserRole::Patient) {
            throw new BookingException(__('ui.booking.not_allowed'));
        }

        if (! $doctor->is_active || $doctor->clinic?->status !== ClinicStatus::Approved) {
            throw new BookingException(__('ui.booking.unavailable'));
        }

        $day = \Illuminate\Support\Carbon::parse($date)->startOfDay();

        if ($day->lt(now()->startOfDay())) {
            throw new BookingException(__('ui.booking.past_date'));
        }

        $weekday = $day->dayOfWeek;

        if ($doctor->schedules->where('day_of_week', $weekday)->isEmpty()) {
            throw new BookingException(__('ui.booking.no_schedule'));
        }

        if ($doctor->leaves->contains(fn ($leave) => $leave->leave_date->toDateString() === $day->toDateString())) {
            throw new BookingException(__('ui.booking.on_leave'));
        }

        $issued = Token::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $day->toDateString())
            ->where('status', '!=', TokenStatus::Cancelled)
            ->count();

        if ($issued >= (int) $doctor->max_tokens_per_day) {
            throw new BookingException(__('ui.booking.full'));
        }

        $duplicate = Token::query()
            ->where('patient_id', $patient->id)
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $day->toDateString())
            ->whereIn('status', [TokenStatus::Booked, TokenStatus::Arrived, TokenStatus::InProgress])
            ->exists();

        if ($duplicate) {
            throw new BookingException(__('ui.booking.duplicate'));
        }
    }

    private function averageMinutes(Doctor $doctor, string $date): int
    {
        $weekday = \Illuminate\Support\Carbon::parse($date)->dayOfWeek;
        $avg = $doctor->schedules->where('day_of_week', $weekday)->avg('avg_consultation_minutes');

        return (int) round($avg ?: 12);
    }
}
