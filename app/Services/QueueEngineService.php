<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\QueueState;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * HTTP contract with the native PHP queue engine.
 *
 * GET  /queue/status?doctor_id=&date=
 * POST /queue/next  {doctor_id, date}
 * POST /queue/sync  {doctor_id, clinic_id, date, total_booked, avg_consultation_minutes, room, doctor_name}
 * POST /queue/reset {date}
 *
 * Response: {doctor_id, date, current_token, total_booked, avg_consultation_minutes, avg_wait_time, room, doctor_name}
 */
class QueueEngineService
{
    public function status(int $doctorId, string $date): array
    {
        $remote = $this->get('/queue/status', [
            'doctor_id' => $doctorId,
            'date' => $date,
        ]);

        if ($remote) {
            return $remote;
        }

        $state = QueueState::query()
            ->where('doctor_id', $doctorId)
            ->where('service_date', $date)
            ->first();

        return $this->shape($state, $doctorId, $date);
    }

    public function next(int $doctorId, string $date): array
    {
        $remote = $this->post('/queue/next', [
            'doctor_id' => $doctorId,
            'date' => $date,
        ]);

        if ($remote) {
            return $remote;
        }

        $state = $this->localState($doctorId, $date);
        $state->current_token = ((int) $state->current_token) + 1;
        $state->save();

        return $this->shape($state, $doctorId, $date);
    }

    /**
     * @param  array{doctor_id: int, clinic_id: int, date: string, total_booked: int, avg_consultation_minutes: int, room?: string|null, doctor_name?: string|null}  $payload
     */
    public function sync(array $payload): array
    {
        $remote = $this->post('/queue/sync', $payload);

        if ($remote) {
            return $remote;
        }

        $state = QueueState::query()->updateOrCreate(
            [
                'doctor_id' => $payload['doctor_id'],
                'service_date' => $payload['date'],
            ],
            [
                'clinic_id' => $payload['clinic_id'],
                'total_booked' => $payload['total_booked'],
                'avg_consultation_minutes' => $payload['avg_consultation_minutes'],
                'room' => $payload['room'] ?? null,
                'doctor_name' => $payload['doctor_name'] ?? null,
            ],
        );

        return $this->shape($state, (int) $payload['doctor_id'], $payload['date']);
    }

    public function reset(?string $date = null): array
    {
        $date ??= now()->toDateString();
        $remote = $this->post('/queue/reset', ['date' => $date]);

        if ($remote) {
            return $remote;
        }

        QueueState::query()->where('service_date', $date)->update(['current_token' => 0]);

        return ['reset' => $date];
    }

    private function get(string $path, array $query): ?array
    {
        if (! $this->remoteEnabled()) {
            return null;
        }

        try {
            $response = Http::connectTimeout(0.4)->timeout(0.8)->acceptJson()->get($this->url($path), $query);

            if ($response->successful()) {
                Cache::forget('queue-engine-down');

                return $response->json();
            }
        } catch (ConnectionException|Throwable $exception) {
            $this->markDown($path, $exception);
        }

        return null;
    }

    private function post(string $path, array $payload): ?array
    {
        if (! $this->remoteEnabled()) {
            return null;
        }

        try {
            $response = Http::connectTimeout(0.4)
                ->timeout(1.2)
                ->acceptJson()
                ->withHeaders(['X-Queue-Secret' => (string) config('arogya.queue_engine.secret')])
                ->post($this->url($path), $payload);

            if ($response->successful()) {
                Cache::forget('queue-engine-down');

                return $response->json();
            }
        } catch (ConnectionException|Throwable $exception) {
            $this->markDown($path, $exception);
        }

        return null;
    }

    private function remoteEnabled(): bool
    {
        if (app()->environment('testing') && ! config('arogya.queue_engine.force_http')) {
            return false;
        }

        return Cache::get('queue-engine-down') !== true;
    }

    private function markDown(string $path, Throwable $exception): void
    {
        Cache::put('queue-engine-down', true, 15);
        Log::warning('Queue engine unreachable', [
            'path' => $path,
            'error' => $exception->getMessage(),
        ]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('arogya.queue_engine.url'), '/').$path;
    }

    private function shape(?QueueState $state, int $doctorId, string $date): array
    {
        $current = (int) ($state->current_token ?? 0);
        $total = (int) ($state->total_booked ?? 0);
        $avg = (int) ($state->avg_consultation_minutes ?? 12);

        return [
            'doctor_id' => $doctorId,
            'date' => $date,
            'current_token' => $current,
            'total_booked' => $total,
            'avg_consultation_minutes' => $avg,
            'avg_wait_time' => max(0, $total - $current) * $avg,
            'room' => $state->room ?? null,
            'doctor_name' => $state->doctor_name ?? null,
        ];
    }

    private function localState(int $doctorId, string $date): QueueState
    {
        $existing = QueueState::query()
            ->where('doctor_id', $doctorId)
            ->where('service_date', $date)
            ->first();

        if ($existing) {
            return $existing;
        }

        $doctor = Doctor::query()->with('user')->findOrFail($doctorId);

        return QueueState::query()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $doctor->clinic_id,
            'service_date' => $date,
            'current_token' => 0,
            'total_booked' => 0,
            'avg_consultation_minutes' => 12,
            'room' => $doctor->room,
            'doctor_name' => $doctor->user?->name,
        ]);
    }
}
