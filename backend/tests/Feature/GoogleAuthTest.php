<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    // Test 1: Google callback rechaza un estado inválido en la cookie
    public function test_google_callback_rejects_invalid_state(): void
    {
        $response = $this
            ->call('GET', '/api/google-auth/callback', ['state' => 'other-state'], [
                AuthController::GOOGLE_STATE_COOKIE => 'expected-state',
            ]);

        $response->assertRedirect('http://localhost:5173/login?error=google_invalid_state');
    }

    // Test 2: Google callback no autentica a un usuario bloqueado
    public function test_google_callback_does_not_authenticate_blocked_users(): void
    {
        $clientRole = Role::create(['name' => 'client']);
        $user = User::create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
            'password' => bcrypt('password'),
            'role_id' => $clientRole->id,
            'blocked' => true,
        ]);

        $googleUser = (object) [
            'id' => 'google-blocked-id',
            'name' => 'Blocked User',
            'email' => $user->email,
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this
            ->call('GET', '/api/google-auth/callback', ['state' => 'valid-state', 'code' => 'fake-code'], [
                AuthController::GOOGLE_STATE_COOKIE => 'valid-state',
            ]);

        $response->assertRedirect('http://localhost:5173/login?error=account_blocked');
        $this->assertNull($user->fresh()->api_token);
    }
    // Test 3: Google callback rechaza un usuario existente con un ID de Google diferente
    public function test_google_callback_rejects_existing_user_with_different_google_id(): void
    {
        $clientRole = Role::create(['name' => 'client']);
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'role_id' => $clientRole->id,
            'google_id' => 'stored-google-id',
        ]);

        $googleUser = (object) [
            'id' => 'different-google-id',
            'name' => 'Existing User',
            'email' => $user->email,
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this
            ->call('GET', '/api/google-auth/callback', ['state' => 'valid-state', 'code' => 'fake-code'], [
                AuthController::GOOGLE_STATE_COOKIE => 'valid-state',
            ]);

        $response->assertRedirect('http://localhost:5173/login?error=google_account_mismatch');
        $this->assertNull($user->fresh()->api_token);
    }
    // Test 4: Google callback autentica a un usuario existente con un ID de Google igual
    public function test_google_callback_creates_new_users_with_client_role_by_name(): void
    {
        Role::create(['id' => 7, 'name' => 'client']);

        $googleUser = (object) [
            'id' => 'new-google-id',
            'name' => 'New Google User',
            'email' => 'new-google-user@example.com',
        ];

        Socialite::shouldReceive('driver->stateless->user')
            ->once()
            ->andReturn($googleUser);

        $response = $this
            ->call('GET', '/api/google-auth/callback', ['state' => 'valid-state', 'code' => 'fake-code'], [
                AuthController::GOOGLE_STATE_COOKIE => 'valid-state',
            ]);

        $response->assertRedirect('http://localhost:5173/login?google=success');
        $this->assertDatabaseHas('users', [
            'email' => 'new-google-user@example.com',
            'google_id' => 'new-google-id',
            'role_id' => 7,
        ]);
    }
}
