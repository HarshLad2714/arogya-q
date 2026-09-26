<?php

namespace App\Http\Controllers;

use App\Enums\TokenStatus;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Token;
use App\Services\QueueEngineService;
use App\Services\WaitTimeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveQueueController extends Controller
{
    public function doctor(Request $request, Doctor $doctor, QueueEngineService $queue): JsonResponse
    {
        $date = $request->validate(['date' => ['nullable', 'date']])['date'] ?? now()->toDateString();

        return response()->json($queue->status($doctor->id, $date));
    }

    public function token(Token $token, QueueEngineService $queue, WaitTimeCalculator $wait): JsonResponse
    {
        $token->load(['doctor.user', 'clinic']);
        $status = $queue->status($token->doctor_id, $token->date->toDateString());
        $ahead = $wait->ahead($token->token_number, (int) $status['current_token']);

        return response()->json([
            'token_number' => $token->token_number,
            'status' => $token->status->value,
            'current_token' => $status['current_token'],
            'ahead' => $ahead,
            'wait_minutes' => $ahead * (int) $status['avg_consultation_minutes'],
            'room' => $token->doctor?->room,
            'doctor' => $token->doctor?->user?->name,
            'clinic' => $token->clinic?->name,
            'your_turn' => $token->token_number === (int) $status['current_token'],
        ]);
    }

    public function clinic(Clinic $clinic, QueueEngineService $queue): JsonResponse
    {
        $date = now()->toDateString();

        $doctors = $clinic->doctors()->with('user')->where('is_active', true)->get()->map(function (Doctor $doctor) use ($queue, $date) {
            $status = $queue->status($doctor->id, $date);
            $upcoming = Token::query()
                ->where('doctor_id', $doctor->id)
                ->whereDate('date', $date)
                ->whereIn('status', [TokenStatus::Booked, TokenStatus::Arrived, TokenStatus::InProgress])
                ->orderBy('token_number')
                ->limit(5)
                ->get(['token_number', 'status']);

            return array_merge($status, [
                'doctor_name' => $doctor->user?->name,
                'room' => $doctor->room,
                'specialization' => $doctor->specialization,
                'upcoming' => $upcoming,
            ]);
        });

        return response()->json([
            'clinic' => $clinic->name,
            'doctors' => $doctors,
        ]);
    }
}
