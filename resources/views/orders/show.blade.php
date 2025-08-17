@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <div class="flex space-x-2">
            <a href="{{ route('orders.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                ← Retour aux commandes
            </a>
            @can('update', $order)
                <a href="{{ route('orders.edit', $order) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    ✏️ Modifier
                </a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Order Information -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Order Information</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">👤 Client:</span>
                    <span class="font-medium">{{ $order->user->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">📧 Email:</span>
                    <span class="font-medium">{{ $order->user->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">📋 Bordereau:</span>
                    <span class="font-medium font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ $order->bordereau_number ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">📊 Statut:</span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        @if($order->status === 'completed') bg-green-100 text-green-800
                        @elseif($order->status === 'out') bg-blue-100 text-blue-800
                        @elseif($order->status === 'packed') bg-purple-100 text-purple-800
                        @elseif($order->status === 'processing') bg-orange-100 text-orange-800
                        @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                        @elseif($order->status === 'returned') bg-gray-100 text-gray-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @switch($order->status)
                            @case('pending') 🕐 En attente @break
                            @case('processing') ⚙️ En traitement @break
                            @case('packed') 📦 Emballé @break
                            @case('out') 🚚 Expédié @break
                            @case('completed') ✅ Terminé @break
                            @case('cancelled') ❌ Annulé @break
                            @case('returned') 🔄 Retourné @break
                            @default {{ ucfirst($order->status) }}
                        @endswitch
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">📅 Créé le:</span>
                    <span class="font-medium">{{ $order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">🔄 Modifié le:</span>
                    <span class="font-medium">{{ $order->updated_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">💰 Montant total:</span>
                    <span class="font-bold text-lg text-green-600">${{ number_format($order->total_amount ?? 0, 2) }}</span>
                </div>
                @if($order->notes)
                    <div class="pt-3 border-t">
                        <span class="text-gray-600">📝 Notes:</span>
                        <p class="mt-1 text-sm text-gray-700 bg-gray-50 p-2 rounded">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Update -->
        @if(auth()->user()->isAdmin() || auth()->user()->isPackagingAgent())
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-3">🔄 Modifier le statut</h3>
                
                <form action="{{ route('orders.update', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>🕐 En attente</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>⚙️ En traitement</option>
                            <option value="packed" {{ $order->status == 'packed' ? 'selected' : '' }}>📦 Emballé</option>
                            <option value="out" {{ $order->status == 'out' ? 'selected' : '' }}>🚚 Expédié</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>✅ Terminé</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>❌ Annulé</option>
                            <option value="returned" {{ $order->status == 'returned' ? 'selected' : '' }}>↩️ Retourné</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        🔄 Mettre à jour le statut
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Order Items</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php $grandTotal = 0; @endphp
                    @foreach($order->orderItems as $item)
                    @php $subtotal = $item->quantity * $item->price; $grandTotal += $subtotal; @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                            <div class="text-sm text-gray-500">{{ $item->product->description }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->product->sku }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->quantity }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${{ number_format($item->price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ number_format($subtotal, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                            Grand Total:
                        </td>
                        <td class="px-6 py-3 text-sm font-bold text-gray-900">
                            ${{ number_format($grandTotal, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <!-- Activity Log -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Activity Log</h3>
        </div>
        <div class="p-6">
            @if($order->logs->count() > 0)
                <div class="space-y-4">
                    @foreach($order->logs->sortByDesc('created_at') as $log)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $log->action }}
                                    </p>
                                <span class="text-xs text-gray-500">
                                        {{ $log->created_at ? $log->created_at->format('M d, Y H:i:s') : 'Just now' }}
                                </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ $log->description }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    By: {{ $log->user->name ?? 'System' }}
                                </p>
                                @if($log->old_value || $log->new_value)
                                    <div class="mt-2 text-xs">
                                        @if($log->old_value)
                                            <span class="text-red-600">From: {{ json_encode($log->old_value) }}</span>
                                        @endif
                                        @if($log->new_value)
                                            <span class="text-green-600 ml-2">To: {{ json_encode($log->new_value) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No activity recorded for this order.</p>
            @endif
        </div>
    </div>
    <!-- Actions spéciales -->
    @can('update', $order)
        @if($order->status === 'out')
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-yellow-800 mb-2">🚚 Commande expédiée</h4>
                <p class="text-sm text-yellow-700 mb-3">
                    Cette commande a été expédiée. Vous pouvez la marquer comme terminée ou retournée si nécessaire.
                </p>
                <div class="flex gap-2">
                    <form action="{{ route('orders.update', $order) }}" method="POST" class="inline">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors">
                            ✅ Marquer comme terminé
                        </button>
                    </form>
                    <form action="{{ route('orders.update', $order) }}" method="POST" class="inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir marquer cette commande comme retournée ?')">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="returned">
                        <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm font-medium transition-colors">
                            🔄 Marquer comme retourné
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endcan
</div>
@endsection
