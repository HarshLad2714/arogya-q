<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Token;
use App\Services\PaymentService;
use App\Services\ReviewService;
use App\Services\TokenBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $tokens = $user->patientTokens()->with(['doctor.user', 'clinic', 'payment'])->latest('date')->limit(8)->get();

        return view('patient.dashboard', [
            'tokens' => $tokens,
            'liveToken' => $tokens->first(fn (Token $token) => $token->date->isToday() && in_array($token->status->value, ['booked', 'arrived', 'in_progress'], true)),
            'notifications' => $user->notifications()->limit(6)->get(),
        ]);
    }

    public function history(): View
    {
        $tokens = auth()->user()->patientTokens()
            ->with(['doctor.user', 'clinic', 'prescription', 'review', 'payment'])
            ->latest('date')
            ->paginate(12);

        return view('patient.history', compact('tokens'));
    }

    public function payments(): View
    {
        $payments = Payment::query()
            ->with(['token.doctor.user', 'clinic'])
            ->where('patient_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('patient.payments', compact('payments'));
    }

    public function pay(Payment $payment, PaymentService $payments): RedirectResponse
    {
        abort_unless($payment->patient_id === auth()->id(), 403);
        $checkout = $payments->startOnline($payment);

        if ($checkout['mode'] === 'demo') {
            $payments->markPaid($payment, null, 'demo');

            return back()->with('status', __('ui.patient.paid_demo'));
        }

        return back()->with('status', __('ui.patient.pay_gateway'))->with('razorpay', $checkout);
    }

    public function prescription(Prescription $prescription): View
    {
        abort_unless($prescription->patient_id === auth()->id(), 403);
        $prescription->load(['token.doctor.user', 'token.clinic', 'patient']);

        return view('patient.prescription', compact('prescription'));
    }

    public function cancel(Request $request, Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('cancel', $token);

        try {
            $booking->cancel($token, $request->string('reason')->toString() ?: null);
        } catch (BookingException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.booking.cancelled'));
    }

    public function reschedule(Request $request, Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('cancel', $token);
        $data = $request->validate(['date' => ['required', 'date', 'after_or_equal:today']]);

        try {
            $fresh = $booking->reschedule($token, $data['date']);
        } catch (BookingException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return redirect()->route('track.show', $fresh)->with('status', __('ui.booking.rescheduled'));
    }

    public function review(ReviewRequest $request, Token $token, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('view', $token);

        try {
            $reviews->store($token, $request->user(), (int) $request->integer('rating'), $request->input('comment'));
        } catch (BookingException $exception) {
            return back()->withErrors(['review' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.patient.review_saved'));
    }
}
