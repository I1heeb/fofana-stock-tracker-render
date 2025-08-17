<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusUpdateRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                Rule::in([
                    Order::STATUS_IN_PROGRESS,
                    Order::STATUS_PACKED,
                    Order::STATUS_OUT,
                    Order::STATUS_CANCELED,
                    Order::STATUS_RETURNED,
                ]),
                function ($attribute, $value, $fail) {
                    $order = $this->route('order');
                    
                    // Prevent skipping statuses (except for cancel/return)
                    if (!in_array($value, [Order::STATUS_CANCELED, Order::STATUS_RETURNED])) {
                        $currentStatus = $order->status;
                        $validTransitions = [
                            Order::STATUS_IN_PROGRESS => [Order::STATUS_PACKED],
                            Order::STATUS_PACKED => [Order::STATUS_OUT],
                            Order::STATUS_OUT => [Order::STATUS_CANCELED, Order::STATUS_RETURNED],
                        ];

                        if (!isset($validTransitions[$currentStatus]) || 
                            !in_array($value, $validTransitions[$currentStatus])) {
                            $fail("Invalid status transition from {$currentStatus} to {$value}");
                        }
                    }
                },
            ],
            'notes' => 'nullable|string|max:1000',
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
            'status.in' => 'The status must be one of: in_progress, packed, out, canceled, or returned.',
        ];
    }
} 