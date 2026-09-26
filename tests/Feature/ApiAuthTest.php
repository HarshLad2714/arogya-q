<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_a_sanctum_token_and_lists_clinics(): void
    {
        User::factory()->create([
            'mobile' => '9090909090',
            'password' => 'secret123',
            'role' => UserRole::Patient,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'mobile' => '9090909090',
            'password' => 'secret123',
        ]);

        $login->assertOk()->assertJsonStructure(['token', 'user' => ['role']]);
        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('user.mobile', '9090909090');

        $this->getJson('/api/v1/clinics')->assertOk();
    }
}
