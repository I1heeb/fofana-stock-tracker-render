@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">User Management</h1>
        
        <p class="mb-4">
            Current User: {{ auth()->user()->name }} 
            @if(auth()->user()->isSuperAdmin())
                <span class="text-red-600 font-bold">(SUPER ADMIN)</span>
            @elseif(auth()->user()->isAdmin())
                <span class="text-purple-600 font-bold">(ADMIN)</span>
            @endif
        </p>

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
            <table class="min-w-full table-auto border">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 border text-left">Name</th>
                        <th class="px-4 py-2 border text-left">Email</th>
                        <th class="px-4 py-2 border text-left">Role</th>
                        <th class="px-4 py-2 border text-left">Status</th>
                        <th class="px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="{{ $user->isSuperAdmin() ? 'bg-red-50' : ($user->isAdmin() ? 'bg-purple-50' : '') }}">
                        <td class="px-4 py-2 border">
                            {{ $user->name }}
                            @if($user->isSuperAdmin())
                                <span class="text-red-500 font-bold">🔥</span>
                            @elseif($user->isAdmin())
                                <span class="text-purple-500 font-bold">👤</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        <td class="px-4 py-2 border">
                            @if($user->isSuperAdmin())
                                <span class="text-red-600 font-bold">SUPER ADMIN</span>
                            @elseif($user->isAdmin())
                                <span class="text-purple-600 font-bold">ADMIN</span>
                            @else
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            @endif
                        </td>
                        <td class="px-4 py-2 border">
                            <span class="text-green-600">Active</span>
                        </td>
                        <td class="px-4 py-2 border">
                            <div class="space-y-1">
                                @if(auth()->user()->canManageUser($user))
                                    <div>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-sm">
                                            Edit
                                        </a>
                                    </div>
                                @endif
                                
                                @if(auth()->user()->canDeleteUser($user))
                                    <div>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Delete user?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                @endif
                                
                                @if(auth()->user()->isSuperAdmin() && $user->isAdmin() && $user->id !== auth()->id())
                                    <div>
                                        @if($user->isSuperAdmin())
                                            <form action="{{ route('admin.users.remove-super-admin', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-orange-600 hover:underline text-sm" onclick="return confirm('Remove super admin?')">
                                                    Remove Super Admin
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.users.make-super-admin', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-purple-600 hover:underline text-sm" onclick="return confirm('Make super admin?')">
                                                    Make Super Admin
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(!auth()->user()->canManageUser($user) && $user->id !== auth()->id())
                                    <span class="text-gray-400 text-sm">Protected</span>
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
