<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        try {
            \Log::info('UserManagementController::index - Starting method');
            \Log::info('UserManagementController::index - Memory usage', ['memory' => memory_get_usage(true)]);
            \Log::info('UserManagementController::index - Execution time', ['time' => microtime(true)]);

            // Check admin access
            if (!auth()->user()->isAdmin()) {
                \Log::warning('UserManagementController::index - Access denied', [
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'is_admin' => auth()->user()->isAdmin()
                ]);
                abort(403, 'Access denied. Admin privileges required.');
            }

            \Log::info('UserManagementController::index - Admin access confirmed');

            $users = User::latest()->paginate(15);
            \Log::info('UserManagementController::index - Users loaded', ['count' => $users->count()]);

            \Log::info('UserManagementController::index - Attempting to load view: admin.users.safe-version');
            return view('admin.users.safe-version', compact('users'));

        } catch (\Exception $e) {
            \Log::error('UserManagementController::index - Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'controller' => 'UserManagementController',
                'method' => 'index'
            ], 500);
        }
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => User::getDefaultPermissions($validated['role']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }
        
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Check authorization using policy
        if (!$currentUser->can('update', $user)) {
            abort(403, 'Access denied. You cannot update this user.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT])],
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? User::getDefaultPermissions($validated['role']),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        // Use the new protection logic
        if (!$user->canBeDeletedBy(auth()->user())) {
            return back()->with('error', 'You do not have permission to delete this user. Super admins cannot delete each other.');
        }

        $userName = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', "User {$userName} deleted successfully.");
    }

    public function toggleStatus(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
    }

    public function updateRole(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT])],
        ]);

        $user->update([
            'role' => $validated['role'],
            'permissions' => User::getDefaultPermissions($validated['role']),
        ]);

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * View password for a user (Super Admin only)
     */
    public function viewPassword(User $user)
    {
        if (!auth()->user()->canViewPasswords()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        return response()->json([
            'user' => $user->name,
            'email' => $user->email,
            'password' => $user->plain_password ?? 'Password not stored in plain text'
        ]);
    }

    /**
     * Change password for any user (Super Admin only)
     */
    public function changePassword(Request $request, User $user)
    {
        if (!auth()->user()->canChangeAnyPassword()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
            'plain_password' => $request->new_password, // Store for super admin viewing
        ]);

        return back()->with('success', "Password changed successfully for {$user->name}.");
    }

    /**
     * Show advanced user creation form (Super Admin only)
     */
    public function createAdvanced()
    {
        if (!auth()->user()->canCreateCustomAccounts()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        $allPermissions = [
            'manage_users' => 'Manage Users',
            'manage_admins' => 'Manage Admins',
            'manage_super_admins' => 'Manage Super Admins',
            'manage_products' => 'Manage Products',
            'manage_orders' => 'Manage Orders',
            'manage_suppliers' => 'Manage Suppliers',
            'view_reports' => 'View Reports',
            'manage_system' => 'Manage System',
            'super_admin' => 'Super Admin Access'
        ];

        return view('admin.users.create-advanced', compact('allPermissions'));
    }

    /**
     * Store advanced user with custom permissions (Super Admin only)
     */
    public function storeAdvanced(Request $request)
    {
        if (!auth()->user()->canCreateCustomAccounts()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,packaging_agent,service_client',
            'permissions' => 'array',
            'is_super_admin' => 'boolean'
        ]);

        // Only super admins can create other super admins
        $isSuperAdmin = false;
        if ($request->boolean('is_super_admin') && auth()->user()->isSuperAdmin()) {
            $isSuperAdmin = true;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password, // Store for super admin viewing
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_super_admin' => $isSuperAdmin,
        ]);

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created successfully with custom permissions.");
    }

    /**
     * Make user super admin (Super Admin only)
     */
    public function makeSuperAdmin(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        $user->update(['is_super_admin' => true]);

        return back()->with('success', "{$user->name} is now a Super Admin.");
    }

    /**
     * Remove super admin status (Super Admin only)
     */
    public function removeSuperAdmin(User $user)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        // Prevent removing super admin from yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove super admin status from yourself.');
        }

        $user->update(['is_super_admin' => false]);

        return back()->with('success', "Super Admin status removed from {$user->name}.");
    }
}







