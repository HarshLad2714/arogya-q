<?php

namespace Tests\Feature;

use App\Services\QueueEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesClinic;
use Tests\TestCase;

class QueueEngineServiceTest extends TestCase
{
    use CreatesClinic;
    use RefreshDatabase;

    public function test_it_reads_a_faked_queue_engine_response(): void
    {
        [, , , $doctor] = $this->makeClinic();
        config(['arogya.queue_engine.force_http' => true]);

        Http::fake([
            '*' => Http::response([
                'doctor_id' => $doctor->id,
                'date' => now()->toDateString(),
                'current_token' => 7,
                'total_booked' => 12,
                'avg_consultation_minutes' => 10,
                'avg_wait_time' => 50,
                'room' => 'Room 1',
                'doctor_name' => 'Meera',
            ], 200),
        ]);

        $status = app(QueueEngineService::class)->status($doctor->id, now()->toDateString());

        $this->assertSame(7, $status['current_token']);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/queue/status'));
    }

    public function test_webhook_notifies_the_patient_who_is_now_due(): void
    {
        [, , , $doctor] = $this->makeClinic();
        $patient = \App\Models\User::factory()->create();
        $token = \App\Models\Token::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $doctor->clinic_id,
            'patient_id' => $patient->id,
            'token_number' => 4,
            'date' => now()->toDateString(),
        ]);

        $this->postJson('/webhooks/queue', [
            'doctor_id' => $doctor->id,
            'date' => now()->toDateString(),
            'current_token' => 4,
        ], ['X-Queue-Secret' => config('arogya.queue_engine.secret')])
            ->assertOk();

        $this->assertSame('now', $token->fresh()->last_alert);
        $this->assertDatabaseHas('notifications_log', [
            'user_id' => $patient->id,
            'type' => 'queue_now',
        ]);
    }
}
