<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 5, 100),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'minimum_stock' => $this->faker->numberBetween(5, 20),
            'low_stock_threshold' => $this->faker->numberBetween(10, 30),
            'sku' => strtoupper($this->faker->unique()->bothify('???-###')),
        ];
    }
} 
