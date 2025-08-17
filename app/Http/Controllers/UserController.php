<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Admin and Packaging Agent can view users
        if (!$user->isAdmin() && !$user->isPackagingAgent()) {
            abort(403, 'Access denied.');
        }
        
        $users = User::paginate(15);
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        $currentUser = auth()->user();
        
        // Admin and Packaging Agent can view user details
        if (!$currentUser->isAdmin() && !$currentUser->isPackagingAgent()) {
            abort(403, 'Access denied.');
        }
        
        return view('users.show', compact('user'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Admin and Packaging Agent can create users
        if (!$user->isAdmin() && !$user->isPackagingAgent()) {
            abort(403, 'Access denied.');
        }
        
        $roles = [
            User::ROLE_ADMIN => 'Administrator',
            User::ROLE_PACKAGING_AGENT => 'Packaging Agent',
            User::ROLE_SERVICE_CLIENT => 'Service Client',
        ];
        
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Admin and Packaging Agent can create users
        if (!$user->isAdmin() && !$user->isPackagingAgent()) {
            abort(403, 'Access denied.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT])],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'permissions' => User::getDefaultPermissions($validated['role']),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    // ONLY ADMINS can edit users
    public function edit(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can edit users.');
        }
        
        $roles = [
            User::ROLE_ADMIN => 'Administrator',
            User::ROLE_PACKAGING_AGENT => 'Packaging Agent',
            User::ROLE_SERVICE_CLIENT => 'Service Client',
        ];
        
        return view('users.edit', compact('user', 'roles'));
    }

    // ONLY ADMINS can update users
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Use policy for authorization
        if (!$currentUser->can('update', $user)) {
            abort(403, 'Access denied. You cannot update this user.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT])],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'permissions' => User::getDefaultPermissions($validated['role']),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // ONLY ADMINS can delete users
    public function destroy(User $user)
    {
        $currentUser = auth()->user();

        if (!$currentUser->isAdmin()) {
            abort(403, 'Only administrators can delete users.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Super admin protection
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super admin cannot be deleted.');
        }

        // Only super admin can delete other admins
        if ($user->isAdmin() && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Only super admin can delete other admins.');
        }

        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin user.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function makeSuperAdmin(User $user)
    {
        $currentUser = auth()->user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Only super admin can promote other users to super admin.');
        }

        if (!$user->isAdmin()) {
            return back()->with('error', 'User must be an admin first.');
        }

        $user->update(['is_super_admin' => true]);

        return back()->with('success', $user->name . ' is now a super admin.');
    }

    public function removeSuperAdmin(User $user)
    {
        $currentUser = auth()->user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Only super admin can remove super admin privileges.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot remove your own super admin privileges.');
        }

        $user->update(['is_super_admin' => false]);

        return back()->with('success', $user->name . ' is no longer a super admin.');
    }
}


