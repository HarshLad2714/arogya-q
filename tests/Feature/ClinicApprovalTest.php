<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_clinic_stays_hidden_until_the_platform_approves_it(): void
    {
        $this->post('/register/clinic', [
            'name' => 'Owner One',
            'mobile' => '9345678901',
            'email' => 'owner@clinic.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'clinic_name' => 'River OPD',
            'specialty' => 'Ayurveda',
            'address' => 'CG Road',
            'city' => 'Ahmedabad',
        ])->assertRedirect(route('clinic.dashboard'));

        $this->get('/clinics')->assertDontSee('River OPD');

        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'mobile' => '9000000099',
        ]);

        $this->actingAs($admin)
            ->post(route('platform.clinics.approve', 'river-opd'))
            ->assertRedirect();

        $this->get('/clinics')->assertSee('River OPD');
    }
}
