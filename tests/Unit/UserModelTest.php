<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user role constants are defined correctly
     */
    public function test_user_role_constants_are_defined(): void
    {
        $this->assertEquals('admin', User::ROLE_ADMIN);
        $this->assertEquals('packaging_agent', User::ROLE_PACKAGING_AGENT);
        $this->assertEquals('service_client', User::ROLE_SERVICE_CLIENT);
    }

    /**
     * Test user role checking methods
     */
    public function test_user_role_checking_methods(): void
    {
        // Test admin user
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isPackagingAgent());
        $this->assertFalse($admin->isServiceClient());

        // Test service client
        $client = User::factory()->create(['role' => 'service_client']);
        $this->assertFalse($client->isAdmin());
        $this->assertFalse($client->isPackagingAgent());
        $this->assertTrue($client->isServiceClient());
    }

    /**
     * Test default permissions assignment
     */
    public function test_default_permissions_assignment(): void
    {
        $adminPermissions = User::getDefaultPermissions(User::ROLE_ADMIN);
        $packagingPermissions = User::getDefaultPermissions(User::ROLE_PACKAGING_AGENT);
        $clientPermissions = User::getDefaultPermissions(User::ROLE_SERVICE_CLIENT);

        // Admin should have all permissions (using actual permission constants)
        $this->assertContains('users.view', $adminPermissions);
        $this->assertContains('products.create', $adminPermissions);
        $this->assertContains('reports.view', $adminPermissions);
        $this->assertContains('orders.create', $adminPermissions);

        // Packaging should have limited permissions
        $this->assertContains('orders.create', $packagingPermissions);
        $this->assertContains('products.view', $packagingPermissions);
        $this->assertNotContains('users.create', $packagingPermissions);

        // Service client should have minimal permissions
        $this->assertContains('products.view', $clientPermissions);
        $this->assertContains('orders.view', $clientPermissions);
        $this->assertNotContains('orders.create', $clientPermissions);
    }

    /**
     * Test user permission checking
     */
    public function test_user_permission_checking(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        // Admin should have access to everything (hasPermission returns true for admin regardless)
        $this->assertTrue($admin->hasPermission('users.view'));
        $this->assertTrue($admin->hasPermission('reports.view'));
        // Admin has all permissions, so even nonexistent ones return true
        $this->assertTrue($admin->hasPermission('nonexistent_permission'));
    }

    /**
     * Test user orders relationship
     */
    public function test_user_orders_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->orders());
    }

    /**
     * Test user logs relationship
     */
    public function test_user_logs_relationship(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->logs());
    }
} 


