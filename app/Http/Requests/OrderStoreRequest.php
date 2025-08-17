<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'packaging';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bordereau_number' => [
                'required',
                'string',
                'regex:/^\d{12}$/',
                'unique:orders,bordereau_number'
            ],
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $product = \App\Models\Product::find($value);
                    $quantity = $this->input(str_replace('product_id', 'quantity', $attribute));

                    if ($product && $quantity > $product->stock_quantity) {
                        $fail("Insufficient stock for product {$product->name}. Available: {$product->stock_quantity}");
                    }
                },
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bordereau_number.required' => 'Le numéro de bordereau est obligatoire.',
            'bordereau_number.regex' => 'Le numéro de bordereau doit contenir exactement 12 chiffres.',
            'bordereau_number.unique' => 'Ce numéro de bordereau existe déjà.',
            'items.required' => 'At least one item is required for the order.',
            'items.*.product_id.exists' => 'One or more selected products do not exist.',
            'items.*.quantity.min' => 'Quantity must be at least 1 for all items.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'quantity',
            'items.*.notes' => 'item notes',
        ];
    }
} 