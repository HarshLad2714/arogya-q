<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingType;
use App\Enums\PaymentMode;
use App\Enums\UserRole;
use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Resources\TokenResource;
use App\Models\Doctor;
use App\Models\Token;
use App\Services\TokenBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingApiController extends Controller
{
    /**
     * POST /api/v1/tokens
     * Body: {doctor_id, date, symptoms?, pay_mode}
     */
    public function store(Request $request, TokenBookingService $booking): JsonResponse
    {
        abort_unless($request->user()->role === UserRole::Patient, 403);

        $data = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'symptoms' => ['nullable', 'string', 'max:500'],
            'pay_mode' => ['required', Rule::enum(PaymentMode::class)],
        ]);

        try {
            $token = $booking->book(
                $request->user(),
                Doctor::query()->findOrFail($data['doctor_id']),
                $data['date'],
                BookingType::Online,
                $data['symptoms'] ?? null,
                PaymentMode::from($data['pay_mode']),
            );
        } catch (BookingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return (new TokenResource($token))->response()->setStatusCode(201);
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->role === UserRole::Patient, 403);

        $tokens = Token::query()
            ->with(['doctor.user', 'clinic'])
            ->where('patient_id', $request->user()->id)
            ->latest('date')
            ->paginate(20);

        return TokenResource::collection($tokens);
    }

    public function cancel(Request $request, Token $token, TokenBookingService $booking): JsonResponse
    {
        $this->authorize('cancel', $token);

        try {
            $booking->cancel($token, $request->input('reason'));
        } catch (BookingException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }
}
