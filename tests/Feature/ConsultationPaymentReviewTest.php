<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinic;
use Tests\TestCase;

class ConsultationPaymentReviewTest extends TestCase
{
    use CreatesClinic;
    use RefreshDatabase;

    public function test_doctor_calls_next_writes_a_prescription_and_patient_reviews(): void
    {
        [, $clinic, $doctorUser, $doctor] = $this->makeClinic();
        $patient = User::factory()->create(['role' => UserRole::Patient]);
        $desk = User::factory()->create([
            'role' => UserRole::Receptionist,
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($desk)->post(route('desk.walkin'), [
            'doctor_id' => $doctor->id,
            'name' => 'Walk In Patient',
            'mobile' => '9011122233',
        ])->assertRedirect();

        $token = Token::query()->first();

        $this->actingAs($doctorUser)
            ->post(route('doctor.next'))
            ->assertRedirect();

        $this->assertSame(TokenStatus::InProgress, $token->fresh()->status);

        $this->actingAs($doctorUser)->post(route('doctor.prescription.store', $token), [
            'medicines' => [
                ['name' => 'ORS', 'dosage' => '1 sachet', 'duration' => '2 days', 'timing' => 'After loose stool'],
            ],
            'notes' => 'Hydrate.',
            'follow_up_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('doctor.dashboard'));

        $this->assertSame(TokenStatus::Completed, $token->fresh()->status);

        $this->actingAs($desk)->post(route('desk.cash', $token))->assertRedirect();
        $this->assertSame(PaymentStatus::Paid, $token->payment->fresh()->status);

        $this->actingAs($patient);
        $owned = Token::query()->where('patient_id', $patient->id)->first();
        $this->assertNull($owned);

        $walkIn = User::query()->where('mobile', '9011122233')->first();
        $this->actingAs($walkIn)->post(route('patient.reviews.store', $token), [
            'rating' => 5,
            'comment' => 'On time.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'token_id' => $token->id,
            'rating' => 5,
        ]);
    }
}
