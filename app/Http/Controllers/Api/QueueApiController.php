<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QueueEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueApiController extends Controller
{
    /**
     * GET /api/v1/queue/status?doctor_id=&date=
     */
    public function status(Request $request, QueueEngineService $queue): JsonResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'date' => ['nullable', 'date'],
        ]);

        return response()->json($queue->status((int) $data['doctor_id'], $data['date'] ?? now()->toDateString()));
    }
}
