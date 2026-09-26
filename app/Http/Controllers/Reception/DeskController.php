<?php

namespace App\Http\Controllers\Reception;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalkInRequest;
use App\Models\Doctor;
use App\Models\Token;
use App\Services\PaymentService;
use App\Services\QueueEngineService;
use App\Services\TokenBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeskController extends Controller
{
    public function dashboard(QueueEngineService $queue): View
    {
        $clinicId = auth()->user()->clinic_id;
        abort_unless($clinicId, 403);

        $doctors = Doctor::query()->with('user')->where('clinic_id', $clinicId)->where('is_active', true)->get();
        $tokens = Token::query()
            ->with(['patient', 'doctor.user', 'payment'])
            ->where('clinic_id', $clinicId)
            ->whereDate('date', today())
            ->orderBy('doctor_id')
            ->orderBy('token_number')
            ->get();

        $live = $doctors->mapWithKeys(fn (Doctor $doctor) => [$doctor->id => $queue->status($doctor->id, now()->toDateString())]);

        return view('desk.dashboard', [
            'doctors' => $doctors,
            'tokens' => $tokens,
            'live' => $live,
            'clinic' => auth()->user()->clinic,
        ]);
    }

    public function walkIn(WalkInRequest $request, TokenBookingService $booking): RedirectResponse
    {
        try {
            $token = $booking->walkIn($request->user(), $request->validated());
        } catch (BookingException $exception) {
            return back()->withInput()->withErrors(['walkin' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.desk.issued', ['number' => $token->token_number]));
    }

    public function arrive(Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('manage', $token);

        try {
            $booking->markArrived($token);
        } catch (BookingException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.desk.arrived'));
    }

    public function cash(Token $token, PaymentService $payments): RedirectResponse
    {
        $this->authorize('manage', $token);
        $payment = $token->payment;

        if ($payment) {
            $payments->markCash($payment);
        }

        return back()->with('status', __('ui.desk.cash_recorded'));
    }

    public function noShow(Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('manage', $token);

        try {
            $booking->markNoShow($token);
        } catch (BookingException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.doctor.no_show_marked'));
    }
}
