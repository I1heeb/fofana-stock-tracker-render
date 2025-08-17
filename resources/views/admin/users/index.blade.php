@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">User Management</h2>
            <p class="text-gray-600 mt-1">Manage system users and their permissions</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary">
            + Add User
        </a>
    </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="modern-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="hover:bg-gradient-to-r hover:from-yellow-50 hover:to-blue-50 transition-all duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-yellow-400 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-blue-900">{{ $user->name }}</div>
                                        <div class="text-sm text-blue-600">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full shadow-sm
                                        @if($user->role === 'admin') bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800
                                        @elseif($user->role === 'packaging_agent') bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800
                                        @else bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 @endif">
                                        @if($user->role === 'admin')
                                            🛡️ Admin
                                        @elseif($user->role === 'packaging_agent')
                                            📦 Packaging Agent
                                        @else
                                            👤 Service Client
                                        @endif
                                    </span>
                                    @if($user->isSuperAdmin())
                                        <span class="px-2 py-1 inline-flex text-xs leading-4 font-bold rounded-full bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300">
                                            👑 SUPER ADMIN
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $user->is_active ?? true ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ?? true ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex space-x-3">
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="text-mustard-600 hover:text-mustard-700 font-medium">
                                            ✏️ Edit
                                        </a>

                                        @if($user->id !== auth()->id())
                                            @if($user->canBeDeleted())
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('Are you sure you want to delete this user?')"
                                                            class="text-red-600 hover:text-red-800 px-3 py-1 rounded-md hover:bg-red-100 transition-all">
                                                        🗑️ Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 px-3 py-1">🔒 Protected</span>
                                            @endif
                                        @endif
                                    </div>

                                    @if(auth()->user()->isSuperAdmin() && $user->isAdmin() && $user->id !== auth()->id())
                                        <div class="flex space-x-2">
                                            @if($user->isSuperAdmin())
                                                <form action="{{ route('admin.users.remove-super-admin', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Remove super admin privileges from {{ $user->name }}?')"
                                                            class="text-orange-600 hover:text-orange-800 px-2 py-1 text-xs rounded-md hover:bg-orange-100 transition-all">
                                                        👑➖ Remove Super Admin
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.make-super-admin', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Make {{ $user->name }} a super admin?')"
                                                            class="text-purple-600 hover:text-purple-800 px-2 py-1 text-xs rounded-md hover:bg-purple-100 transition-all">
                                                        👑➕ Make Super Admin
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-blue-200 bg-gradient-to-r from-blue-50 to-yellow-50">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


