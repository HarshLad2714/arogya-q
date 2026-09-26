<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Services\QueueEngineService;
use App\Services\WaitTimeCalculator;
use Illuminate\View\View;

class TrackerController extends Controller
{
    public function show(Token $token, QueueEngineService $queue, WaitTimeCalculator $wait): View
    {
        $token->load(['clinic', 'doctor.user', 'patient', 'payment']);
        $status = $queue->status($token->doctor_id, $token->date->toDateString());
        $ahead = $wait->ahead($token->token_number, (int) $status['current_token']);

        return view('track.show', [
            'token' => $token,
            'live' => $status,
            'ahead' => $ahead,
            'waitMinutes' => $ahead * (int) $status['avg_consultation_minutes'],
        ]);
    }
}
