@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🛡️ Admin Dashboard - User Management</h1>
            <p class="text-gray-600 mt-2">Welcome {{ Auth::user()->name }}! Manage system users and their roles</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.users.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add User
            </a>
            <div class="text-sm text-gray-500">
                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full font-medium">
                    🔑 Admin Access
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->total() }}</p>
                </div>
                <div class="text-4xl text-blue-500">👥</div>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Admin Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->where('role', 'admin')->count() }}</p>
                </div>
                <div class="text-4xl text-red-500">🛡️</div>
            </div>
        </div>

        <!-- Packaging Users -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Packaging Agent</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->where('role', 'packaging_agent')->count() }}</p>
                </div>
                <div class="text-4xl text-yellow-500">📦</div>
            </div>
        </div>

        <!-- Service Client Users -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Service Client</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->where('role', 'service_client')->count() }}</p>
                </div>
                <div class="text-4xl text-green-500">👤</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">🚀 Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.users') }}" class="flex items-center p-4 bg-blue-50 rounded-lg border-2 border-blue-200 transition-colors">
                <span class="text-2xl mr-3">👥</span>
                <div>
                    <p class="font-medium text-blue-900">Manage Users</p>
                    <p class="text-sm text-blue-600">Current page</p>
                </div>
            </a>

            <a href="{{ route('admin.products') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <span class="text-2xl mr-3">📦</span>
                <div>
                    <p class="font-medium text-green-900">Manage Products</p>
                    <p class="text-sm text-green-600">View and edit products</p>
                </div>
            </a>

            <a href="{{ route('admin.orders') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                <span class="text-2xl mr-3">📋</span>
                <div>
                    <p class="font-medium text-yellow-900">Manage Orders</p>
                    <p class="text-sm text-yellow-600">View and process orders</p>
                </div>
            </a>

            <a href="{{ route('admin.reports') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <span class="text-2xl mr-3">📊</span>
                <div>
                    <p class="font-medium text-purple-900">View Reports</p>
                    <p class="text-sm text-purple-600">Sales and analytics</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">All Users ({{ $users->total() }})</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    @if($user->role === 'admin') bg-red-100 text-red-800
                                    @elseif($user->role === 'packaging_agent') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    @if($user->role === 'admin') 🛡️ Admin
                                    @elseif($user->role === 'packaging_agent') 📦 Packaging Agent
                                    @else 👤 Service Client @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if(is_array($user->permissions) && count($user->permissions) > 0)
                                        @foreach($user->permissions as $permission)
                                            <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                {{ str_replace('_', ' ', $permission) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-gray-500">No special permissions</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- Change Role Form -->
                                    <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                                            <option value="service_client" {{ $user->role === 'service_client' ? 'selected' : '' }}>👤 Service Client</option>
                                            <option value="packaging_agent" {{ $user->role === 'packaging_agent' ? 'selected' : '' }}>📦 Packaging Agent</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>🛡️ Admin</option>
                                        </select>
                                    </form>

                                    @if($user->id !== Auth::id())
                                        <!-- Delete User Form -->
                                        <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs bg-red-100 px-2 py-1 rounded">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">Current User</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Role Information -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4">🔑 Role Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg">
                <h4 class="font-medium text-red-800 mb-2">🛡️ Admin</h4>
                <p class="text-sm text-gray-600">Full system access, can manage users, products, orders, and view reports.</p>
            </div>
            <div class="bg-white p-4 rounded-lg">
                <h4 class="font-medium text-blue-800 mb-2">📦 Packaging</h4>
                <p class="text-sm text-gray-600">Can manage orders and products, focused on packaging operations.</p>
            </div>
            <div class="bg-white p-4 rounded-lg">
                <h4 class="font-medium text-green-800 mb-2">👤 Service Client</h4>
                <p class="text-sm text-gray-600">Standard user access, can create and view their own orders.</p>
            </div>
        </div>
    </div>
</div>
@endsection





