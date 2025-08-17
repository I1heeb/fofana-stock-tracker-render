<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserBasicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can be created with basic fields
     */
    public function test_user_can_be_created_with_basic_fields(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);
    }

    /**
     * Test user role constants exist
     */
    public function test_user_role_constants_exist(): void
    {
        $this->assertTrue(defined('App\Models\User::ROLE_ADMIN'));
        $this->assertTrue(defined('App\Models\User::ROLE_PACKAGING_AGENT'));
        $this->assertTrue(defined('App\Models\User::ROLE_SERVICE_CLIENT'));
    }

    /**
     * Test admin role checking
     */
    public function test_admin_role_checking(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isServiceClient());
    }

    /**
     * Test service client role checking
     */
    public function test_service_client_role_checking(): void
    {
        $client = User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => bcrypt('password'),
            'role' => 'service_client'
        ]);

        $this->assertFalse($client->isAdmin());
        $this->assertTrue($client->isServiceClient());
    }

    /**
     * Test user relationships exist
     */
    public function test_user_relationships_exist(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        // Test that relationships are defined (don't need data)
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->orders());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->logs());
    }
}
