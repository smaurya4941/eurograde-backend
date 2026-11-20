<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mark requests as coming from the SPA so Sanctum enables sessions.
        $this->withHeader('Referer', 'http://localhost:5173');
    }

    public function test_user_can_register(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'Jane@Example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertAuthenticated('web');
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    }

    public function test_registration_validates_input(): void
    {
        $this->postJson('/api/v1/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login_and_fetch_profile(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk()->assertJsonPath('data.id', $user->id);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $this->assertGuest('web');
    }

    public function test_me_returns_null_for_guests(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            // The envelope omits null fields: no "data" means no signed-in user.
            ->assertExactJson(['success' => true]);
    }

    public function test_guest_cannot_logout(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertGuest('web');
    }
}
