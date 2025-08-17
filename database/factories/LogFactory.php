<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LogFactory extends Factory
{
    public function definition(): array
    {
        $types = ['stock', 'order', 'return'];
        $type = $this->faker->randomElement($types);
        
        return [
            'type' => $type,
            'action' => $this->getActionForType($type),
            'description' => $this->faker->sentence(),
            'message' => $this->faker->sentence(),
            'order_id' => $type === 'order' ? Order::factory() : null,
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'user_id' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    private function getActionForType(string $type): string
    {
        return match($type) {
            'stock' => $this->faker->randomElement(['stock_updated', 'stock_added', 'stock_removed']),
            'order' => $this->faker->randomElement(['order_created', 'order_updated', 'order_cancelled']),
            'return' => $this->faker->randomElement(['return_processed', 'return_approved', 'return_rejected']),
        };
    }
}
