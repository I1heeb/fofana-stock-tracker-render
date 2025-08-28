@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add User
            </a>
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

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                @if($user->role === 'admin') bg-purple-100 text-purple-800
                                @elseif($user->role === 'packaging_agent') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                @if($user->role === 'admin')
                                    Admin
                                @elseif($user->role === 'packaging_agent')
                                    Packaging Agent
                                @else
                                    Service Client
                                @endif
                            </span>
                            @if($user->isSuperAdmin())
                                <br><span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                    SUPER ADMIN
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900">
                                    Edit
                                </a>
                                
                                @if($user->id !== auth()->id() && $user->canBeDeleted())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                                
                                @if(auth()->user()->isSuperAdmin() && $user->isAdmin() && $user->id !== auth()->id())
                                    @if($user->isSuperAdmin())
                                        <form action="{{ route('admin.users.remove-super-admin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Remove super admin?')">
                                            @csrf
                                            <button type="submit" class="text-orange-600 hover:text-orange-900">
                                                Remove Super Admin
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.make-super-admin', $user) }}" method="POST" class="inline" onsubmit="return confirm('Make super admin?')">
                                            @csrf
                                            <button type="submit" class="text-purple-600 hover:text-purple-900">
                                                Make Super Admin
                                            </button>
                                        </form>
                                    @endif
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
