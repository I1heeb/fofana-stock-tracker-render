<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'packaging';
    }

    public function rules(): array
    {
        return [
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['required', 'integer', 'exists:orders,id'],
            'status' => ['required', Rule::in(['in_progress', 'packed', 'out', 'canceled', 'returned'])],
        ];
    }

    public function messages(): array
    {
        return [
            'order_ids.required' => 'Please select at least one order.',
            'order_ids.array' => 'Invalid order selection format.',
            'order_ids.min' => 'Please select at least one order.',
            'order_ids.*.exists' => 'One or more selected orders do not exist.',
            'status.required' => 'Please select a status to update to.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $orders = Order::findMany($this->order_ids);
            
            // Check if any orders have invalid status transitions
            foreach ($orders as $order) {
                if (!$this->isValidStatusTransition($order->status, $this->status)) {
                    $validator->errors()->add(
                        'order_ids', 
                        "Order #{$order->id} cannot be changed from {$order->status} to {$this->status}"
                    );
                }
            }
        });
    }

    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // Allow cancellation/return from any status
        if (in_array($newStatus, ['canceled', 'returned'])) {
            return true;
        }

        // Define valid status transitions
        $validTransitions = [
            'in_progress' => ['packed'],
            'packed' => ['out'],
            'out' => [],
            'canceled' => [],
            'returned' => [],
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }
} 