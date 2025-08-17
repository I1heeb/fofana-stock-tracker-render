<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * Test admin can access admin routes
     */
    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    /**
     * Test non-admin cannot access admin routes
     */
    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $client = User::factory()->create([
            'role' => User::ROLE_SERVICE_CLIENT,
            'permissions' => User::getDefaultPermissions(User::ROLE_SERVICE_CLIENT)
        ]);

        $response = $this->actingAs($client)->get('/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test user logout functionality
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test guest is redirected to login
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * Test role-based access control
     */
    public function test_role_based_access_control(): void
    {
        $packaging = User::factory()->create([
            'role' => User::ROLE_PACKAGING_AGENT,
            'permissions' => User::getDefaultPermissions(User::ROLE_PACKAGING_AGENT)
        ]);

        // Packaging agent can access orders
        $response = $this->actingAs($packaging)->get('/orders');
        $response->assertStatus(200);

        // But cannot access user management
        $response = $this->actingAs($packaging)->get('/admin/users');
        $response->assertStatus(403);
    }
}
