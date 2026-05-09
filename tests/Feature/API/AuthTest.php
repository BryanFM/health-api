<?php

namespace Tests\Feature\API;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['token', 'user' => ['id', 'name', 'email', 'roles']],
                 ])
                 ->assertJson(['success' => true]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'roles', 'permissions'],
                 ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['success' => true, 'message' => 'Logged out successfully']);
    }
}
