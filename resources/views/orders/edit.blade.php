@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">✏️ Modifier {{ $order->order_number ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <a href="{{ route('orders.show', $order) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    ← Retour à la commande
                </a>
            </div>

            <form action="{{ route('orders.update', $order) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            📊 Statut de la commande
                        </label>
                        <select name="status" id="status"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>🕐 En attente</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>⚙️ En traitement</option>
                            <option value="packed" {{ $order->status == 'packed' ? 'selected' : '' }}>📦 Emballé</option>
                            <option value="out" {{ $order->status == 'out' ? 'selected' : '' }}>🚚 Expédié</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>✅ Terminé</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>❌ Annulé</option>
                            <option value="returned" {{ $order->status == 'returned' ? 'selected' : '' }}>🔄 Retourné</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            📝 Notes sur la commande
                        </label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Ajouter des notes sur cette commande...">{{ old('notes', $order->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('orders.show', $order) }}"
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            ❌ Annuler
                        </a>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium transition-colors">
                            ✅ Mettre à jour la commande
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection