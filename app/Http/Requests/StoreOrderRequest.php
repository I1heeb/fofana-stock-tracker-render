<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Filtrer les items avec quantité > 0 avant validation
        $filteredItems = collect($this->items ?? [])
            ->filter(fn($item) => isset($item['quantity']) && $item['quantity'] > 0)
            ->values()
            ->toArray();

        $this->merge([
            'items' => $filteredItems
        ]);
    }

    public function rules(): array
    {
        return [
            'bordereau_number' => [
                'required',
                'string',
                'regex:/^\d{12}$/',
                'unique:orders,bordereau_number'
            ],
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $product = Product::find($value);
                    $quantity = $this->input(str_replace('product_id', 'quantity', $attribute));

                    if ($product && $quantity > $product->stock_quantity) {
                        $fail("Insufficient stock for {$product->name}. Available: {$product->stock_quantity}");
                    }
                },
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'bordereau_number.required' => 'Le numéro de bordereau est obligatoire.',
            'bordereau_number.regex' => 'Le numéro de bordereau doit contenir exactement 12 chiffres.',
            'bordereau_number.unique' => 'Ce numéro de bordereau existe déjà.',
            'items.required' => 'At least one item with quantity > 0 is required.',
            'items.min' => 'At least one item with quantity > 0 is required.',
            'items.*.product_id.exists' => 'Selected product does not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}