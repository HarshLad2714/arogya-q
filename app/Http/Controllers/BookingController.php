<?php

namespace App\Http\Controllers;

use App\Enums\BookingType;
use App\Enums\ClinicStatus;
use App\Enums\PaymentMode;
use App\Exceptions\BookingException;
use App\Http\Requests\BookTokenRequest;
use App\Models\Doctor;
use App\Services\QueueEngineService;
use App\Services\TokenBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Doctor $doctor, QueueEngineService $queue): View
    {
        $doctor->load(['user', 'clinic', 'schedules', 'leaves' => fn ($q) => $q->whereDate('leave_date', '>=', today())]);
        abort_unless($doctor->is_active && $doctor->clinic?->status === ClinicStatus::Approved, 404);

        $days = collect(range(0, 6))->map(function (int $offset) use ($doctor) {
            $date = now()->addDays($offset);
            $open = $doctor->schedules->contains(fn ($row) => (int) $row->day_of_week === $date->dayOfWeek);
            $leave = $doctor->leaves->contains(fn ($row) => $row->leave_date->toDateString() === $date->toDateString());

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('D'),
                'day' => $date->format('d'),
                'available' => $open && ! $leave,
            ];
        });

        return view('booking.create', [
            'doctor' => $doctor,
            'days' => $days,
            'live' => $queue->status($doctor->id, now()->toDateString()),
        ]);
    }

    public function store(BookTokenRequest $request, Doctor $doctor, TokenBookingService $booking): RedirectResponse
    {
        try {
            $token = $booking->book(
                $request->user(),
                $doctor,
                $request->string('date')->toString(),
                BookingType::Online,
                $request->input('symptoms'),
                PaymentMode::from($request->string('pay_mode')->toString()),
            );
        } catch (BookingException $exception) {
            return back()->withInput()->withErrors(['booking' => $exception->getMessage()]);
        }

        return redirect()->route('track.show', $token)->with('status', __('ui.booking.confirmed'));
    }
}
