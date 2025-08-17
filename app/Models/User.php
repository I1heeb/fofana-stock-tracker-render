<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_PACKAGING_AGENT = 'packaging_agent';
    const ROLE_SERVICE_CLIENT = 'service_client';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPackagingAgent(): bool
    {
        return $this->role === self::ROLE_PACKAGING_AGENT;
    }

    public function isServiceClient(): bool
    {
        return $this->role === self::ROLE_SERVICE_CLIENT;
    }

    public function isSuperAdmin()
    {
        return (bool) $this->is_super_admin;
    }

    public function canDeleteAdmin()
    {
        return $this->isSuperAdmin();
    }

    public function canBeDeleted()
    {
        return !$this->isSuperAdmin();
    }

    /**
     * Get role display name for UI
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'admin',
            'packaging_agent' => 'packaging',
            'packaging' => 'packaging', // Support for legacy format
            'service_client' => 'service',
            'service' => 'service', // Support for legacy format
            default => $this->role ?? 'unknown'
        };
    }
    // Permission constants
    const PERMISSIONS = [
        'products' => [
            'view' => 'products.view',
            'create' => 'products.create',
            'update' => 'products.update',
            'delete' => 'products.delete',
            'manage_stock' => 'products.manage_stock',
        ],
        'orders' => [
            'view' => 'orders.view',
            'create' => 'orders.create',
            'update' => 'orders.update',
            'delete' => 'orders.delete',
            'update_status' => 'orders.update_status',
            'bulk_operations' => 'orders.bulk_operations',
        ],
        'logs' => [
            'view' => 'logs.view',
            'delete' => 'logs.delete',
        ],
        'reports' => [
            'view' => 'reports.view',
            'export' => 'reports.export',
        ],
        'users' => [
            'view' => 'users.view',
            'create' => 'users.create',
            'update' => 'users.update',
            'delete' => 'users.delete',
            'manage_permissions' => 'users.manage_permissions',
        ],
        'system' => [
            'settings' => 'system.settings',
            'telescope' => 'system.telescope',
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_super_admin',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
    ];

    /**
     * Get the orders created by the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }

        $permissions = $this->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions ?? []);
    }

    /**
     * Get role display name in French
     */


    /**
     * Get default permissions for role
     */
    public static function getDefaultPermissions(string $role): array
    {
        return match($role) {
            self::ROLE_ADMIN => collect(self::PERMISSIONS)->flatten()->values()->toArray(), // Accès complet à tout
            
            self::ROLE_PACKAGING_AGENT => [
                // Products - lecture seule et gestion du stock uniquement (PAS d'édition)
                self::PERMISSIONS['products']['view'],
                self::PERMISSIONS['products']['manage_stock'],

                // Orders - accès complet
                self::PERMISSIONS['orders']['view'],
                self::PERMISSIONS['orders']['create'],
                self::PERMISSIONS['orders']['update'],
                self::PERMISSIONS['orders']['delete'],
                self::PERMISSIONS['orders']['update_status'],
                self::PERMISSIONS['orders']['bulk_operations'],

                // Logs and Reports - accès complet
                self::PERMISSIONS['logs']['view'],
                self::PERMISSIONS['logs']['delete'],
                self::PERMISSIONS['reports']['view'],
                self::PERMISSIONS['reports']['export'],

                // PAS d'accès à la gestion des utilisateurs (admin panel)
                // PAS d'accès aux fonctions edit/delete des utilisateurs
            ],
            
            self::ROLE_SERVICE_CLIENT => [
                // Products - lecture seule
                self::PERMISSIONS['products']['view'],

                // Orders - lecture seule
                self::PERMISSIONS['orders']['view'],

                // Logs and Reports - lecture seule
                self::PERMISSIONS['logs']['view'],
                self::PERMISSIONS['reports']['view'],

                // AUCUNE permission de création/modification/suppression
                // AUCUN accès au panel admin
            ],
            
            default => []
        };
    }
}



















