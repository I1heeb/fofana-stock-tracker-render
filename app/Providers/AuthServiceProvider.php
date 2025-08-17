<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Supplier;
use App\Policies\UserPolicy;
use App\Policies\ProductPolicy;
use App\Policies\OrderPolicy;
use App\Policies\SupplierPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
        Order::class => OrderPolicy::class,
        Supplier::class => SupplierPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Log::info('AuthServiceProvider boot method called');

        $this->registerPolicies();

        \Log::info('Policies registered', ['policies' => $this->policies]);

        // Test if policy is actually registered
        $registeredPolicy = Gate::getPolicyFor(User::class);
        \Log::info('Policy check after registration', [
            'policy' => $registeredPolicy ? get_class($registeredPolicy) : 'null'
        ]);

        // Define admin-users gate - Only admins can access admin panel
        Gate::define('admin-users', function ($user) {
            return $user->isAdmin();
        });
    }
}


