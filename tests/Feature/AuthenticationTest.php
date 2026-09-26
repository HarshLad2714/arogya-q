<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registers_with_otp_and_reaches_the_dashboard(): void
    {
        $this->post('/register', [
            'name' => 'Neel Patel',
            'mobile' => '9123456780',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'language_pref' => 'gu',
        ])->assertRedirect(route('otp.show'));

        $code = Cache::get('otp-demo:9123456780');
        $this->assertNotEmpty($code);

        $this->post('/otp', ['code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'mobile' => '9123456780',
            'role' => UserRole::Patient->value,
            'is_active' => 1,
        ]);
    }

    public function test_password_login_and_role_gate(): void
    {
        $patient = User::factory()->create([
            'mobile' => '9000000001',
            'password' => 'secret123',
            'role' => UserRole::Patient,
        ]);

        $this->post('/login', [
            'mobile' => '9000000001',
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($patient)->get('/platform')->assertForbidden();
    }

    public function test_password_can_be_reset_with_otp(): void
    {
        User::factory()->create(['mobile' => '9000000002', 'password' => 'old-password']);

        $this->post('/forgot-password', ['mobile' => '9000000002'])
            ->assertRedirect();

        $this->post('/reset-password', [
            'code' => Cache::get('otp-demo:9000000002'),
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->post('/login', [
            'mobile' => '9000000002',
            'password' => 'new-password',
        ])->assertRedirect(route('dashboard'));
    }
}
