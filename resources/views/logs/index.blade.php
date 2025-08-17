@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-navy-900">Activity Logs</h2>
            <p class="text-gray-600 mt-1">Track all system activities and changes</p>
        </div>
    </div>

    @if($logs->count() > 0)
        <div class="modern-card overflow-hidden">
            <ul class="divide-y divide-gray-200">
                @foreach($logs as $log)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($log->type === 'success') bg-green-100 text-green-800
                                        @elseif($log->type === 'error') bg-red-100 text-red-800
                                        @elseif($log->type === 'warning') bg-yellow-100 text-yellow-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($log->type) }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $log->action }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $log->description ?? $log->message }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        @if($log->user)
                                            by {{ $log->user->name }} • 
                                        @endif
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : 'Just now' }}
                                        @if($log->order)
                                            • Order #{{ $log->order->order_number }}
                                        @endif
                                        @if($log->product)
                                            • {{ $log->product->name }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($log->quantity)
                                    <span class="text-sm text-gray-500">Qty: {{ $log->quantity }}</span>
                                @endif
                                @can('delete', $log)
                                    <form method="POST" action="{{ route('logs.destroy', $log) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm"
                                                onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-gray-500 text-lg">No activity logs found.</div>
        </div>
    @endif
</div>
@endsection


