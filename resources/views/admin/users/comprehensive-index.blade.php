@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                <p class="text-gray-600 mt-1">
                    @if(auth()->user()->isSuperAdmin())
                        <span class="text-red-600 font-semibold">🔥 SUPER ADMIN</span> - Full system access
                    @else
                        <span class="text-blue-600 font-semibold">👤 ADMIN</span> - Limited access
                    @endif
                </p>
            </div>
            @if(auth()->user()->canManageUser(new App\Models\User()))
                <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + Add User
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Role Legend -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Role Hierarchy:</h3>
            <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                    🔥 SUPER ADMIN - Can manage all users including admins
                </span>
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800">
                    👤 ADMIN - Can manage packaging agents and service clients
                </span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    📦 PACKAGING AGENT - Warehouse operations
                </span>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    🛒 SERVICE CLIENT - Customer access
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role & Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr class="{{ $user->isSuperAdmin() ? 'bg-red-50' : ($user->isAdmin() ? 'bg-purple-50' : '') }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full {{ $user->isSuperAdmin() ? 'bg-red-500' : ($user->isAdmin() ? 'bg-purple-500' : 'bg-blue-500') }} flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if($user->isSuperAdmin())
                                            <span class="ml-2 text-red-500">🔥</span>
                                        @elseif($user->isAdmin())
                                            <span class="ml-2 text-purple-500">👤</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->isSuperAdmin())
                                <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                    🔥 SUPER ADMIN
                                </span>
                            @elseif($user->role === 'admin')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    👤 ADMIN
                                </span>
                            @elseif($user->role === 'packaging_agent')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    📦 PACKAGING AGENT
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    🛒 SERVICE CLIENT
                                </span>
                            @endif
                            <br>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 mt-1 inline-block">
                                ✅ Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($user->isSuperAdmin())
                                <div class="text-red-600 font-semibold">🔥 ALL PERMISSIONS</div>
                                <div class="text-gray-500">Can manage all users & admins</div>
                            @elseif($user->isAdmin())
                                <div class="text-purple-600 font-semibold">👤 ADMIN PERMISSIONS</div>
                                <div class="text-gray-500">Can manage lower roles</div>
                            @else
                                <div class="text-gray-600">Standard user permissions</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex flex-col space-y-2">
                                @if(auth()->user()->canManageUser($user))
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 text-xs">
                                            ✏️ Edit
                                        </a>
                                        
                                        @if(auth()->user()->canDeleteUser($user))
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(auth()->user()->isSuperAdmin() && $user->isAdmin() && $user->id !== auth()->id())
                                    <div class="flex space-x-2">
                                        @if($user->isSuperAdmin())
                                            <form action="{{ route('admin.users.remove-super-admin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Remove super admin from {{ $user->name }}?')">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:text-orange-900 text-xs">
                                                    👑➖ Remove Super Admin
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.make-super-admin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Make {{ $user->name }} super admin?')">
                                                @csrf
                                                <button type="submit" class="text-purple-600 hover:text-purple-900 text-xs">
                                                    👑➕ Make Super Admin
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(!auth()->user()->canManageUser($user))
                                    <span class="text-gray-400 text-xs">🔒 Protected</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
