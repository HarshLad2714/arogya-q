<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueWebhookController extends Controller
{
    public function __invoke(Request $request, NotificationService $notifications): JsonResponse
    {
        $secret = (string) config('arogya.queue_engine.secret');
        $given = (string) $request->header('X-Queue-Secret', '');

        abort_unless($secret !== '' && hash_equals($secret, $given), 401);

        $data = $request->validate([
            'doctor_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'current_token' => ['required', 'integer', 'min:0'],
        ]);

        $notifications->announceQueue((int) $data['doctor_id'], $data['date'], (int) $data['current_token']);

        return response()->json(['ok' => true]);
    }
}
