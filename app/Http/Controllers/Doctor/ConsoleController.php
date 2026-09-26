<?php

namespace App\Http\Controllers\Doctor;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionRequest;
use App\Models\Token;
use App\Services\DoctorService;
use App\Services\PrescriptionService;
use App\Services\QueueEngineService;
use App\Services\TokenBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsoleController extends Controller
{
    public function dashboard(QueueEngineService $queue): View
    {
        $doctor = $this->doctor();
        $date = now()->toDateString();
        $tokens = Token::query()
            ->with(['patient', 'payment'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->orderBy('token_number')
            ->get();

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'tokens' => $tokens,
            'live' => $queue->status($doctor->id, $date),
        ]);
    }

    public function next(TokenBookingService $booking): RedirectResponse
    {
        $result = $booking->callNext($this->doctor());
        $number = $result['current_token'];

        return back()->with('status', __('ui.doctor.now_serving', ['number' => $number]));
    }

    public function complete(Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('prescribe', $token);
        $booking->complete($token);

        return back()->with('status', __('ui.common.saved'));
    }

    public function noShow(Token $token, TokenBookingService $booking): RedirectResponse
    {
        $this->authorize('prescribe', $token);

        try {
            $booking->markNoShow($token);
        } catch (BookingException $exception) {
            return back()->withErrors(['booking' => $exception->getMessage()]);
        }

        return back()->with('status', __('ui.doctor.no_show_marked'));
    }

    public function prescriptionForm(Token $token): View
    {
        $this->authorize('prescribe', $token);
        $token->load(['patient', 'prescription', 'doctor.user']);

        return view('doctor.prescription', compact('token'));
    }

    public function prescriptionStore(PrescriptionRequest $request, Token $token, PrescriptionService $prescriptions): RedirectResponse
    {
        $this->authorize('prescribe', $token);
        $medicines = $request->input('medicines', []);
        $named = collect($medicines)->filter(fn ($row) => filled($row['name'] ?? null));

        if ($named->isEmpty()) {
            return back()->withErrors(['medicines' => __('ui.doctor.medicine_required')])->withInput();
        }

        $prescriptions->write($token, $medicines, $request->input('notes'), $request->input('follow_up_date'));

        return redirect()->route('doctor.dashboard')->with('status', __('ui.doctor.prescription_saved'));
    }

    public function leave(Request $request, DoctorService $doctors): RedirectResponse
    {
        $data = $request->validate([
            'leave_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:160'],
        ]);

        $doctors->grantLeave($this->doctor(), $data['leave_date'], $data['reason'] ?? null);

        return back()->with('status', __('ui.doctor.leave_saved'));
    }

    private function doctor()
    {
        $doctor = auth()->user()->doctorProfile;
        abort_unless($doctor, 403);

        return $doctor;
    }
}
