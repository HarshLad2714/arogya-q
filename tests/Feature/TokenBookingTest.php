<?php

namespace Tests\Feature;

use App\Enums\TokenStatus;
use App\Enums\UserRole;
use App\Models\DoctorLeave;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinic;
use Tests\TestCase;

class TokenBookingTest extends TestCase
{
    use CreatesClinic;
    use RefreshDatabase;

    public function test_patient_books_and_can_cancel_when_enough_time_remains(): void
    {
        [, , , $doctor] = $this->makeClinic();
        $patient = User::factory()->create(['role' => UserRole::Patient]);

        $this->actingAs($patient)->post(route('booking.store', $doctor), [
            'date' => now()->toDateString(),
            'pay_mode' => 'cash',
            'symptoms' => 'Headache',
        ])->assertRedirect();

        $second = User::factory()->create(['role' => UserRole::Patient]);
        $this->actingAs($second)->post(route('booking.store', $doctor), [
            'date' => now()->toDateString(),
            'pay_mode' => 'online',
        ])->assertRedirect();

        $token = Token::query()->where('patient_id', $second->id)->first();
        $this->assertSame(2, $token->token_number);
        $this->assertDatabaseHas('notifications_log', [
            'user_id' => $second->id,
            'type' => 'booking_confirmation',
            'channel' => 'sms',
        ]);

        $this->actingAs($second)
            ->post(route('patient.tokens.cancel', $token))
            ->assertRedirect();

        $this->assertSame(TokenStatus::Cancelled, $token->fresh()->status);
    }

    public function test_the_patient_being_served_cannot_cancel(): void
    {
        [, , , $doctor] = $this->makeClinic();
        $patient = User::factory()->create(['role' => UserRole::Patient]);

        $this->actingAs($patient)->post(route('booking.store', $doctor), [
            'date' => now()->toDateString(),
            'pay_mode' => 'cash',
        ]);

        $token = Token::query()->first();

        $this->actingAs($patient)
            ->post(route('patient.tokens.cancel', $token))
            ->assertSessionHasErrors('booking');

        $this->assertSame(TokenStatus::Booked, $token->fresh()->status);
    }

    public function test_a_doctor_on_leave_cannot_be_booked(): void
    {
        [, , , $doctor] = $this->makeClinic();
        DoctorLeave::query()->create([
            'doctor_id' => $doctor->id,
            'leave_date' => now()->toDateString(),
            'reason' => 'Travel',
        ]);
        $patient = User::factory()->create(['role' => UserRole::Patient]);

        $this->actingAs($patient)->post(route('booking.store', $doctor), [
            'date' => now()->toDateString(),
            'pay_mode' => 'cash',
        ])->assertSessionHasErrors('booking');

        $this->assertSame(0, Token::query()->count());
    }
}
