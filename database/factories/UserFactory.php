<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement([User::ROLE_PACKAGING_AGENT, User::ROLE_SERVICE_CLIENT]),
        ];
    }

    /**
     * Set the role for the user.
     */
    public function withRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Indicate that the user is a packaging user.
     */
    public function packaging(): static
    {
        return $this->withRole(User::ROLE_PACKAGING_AGENT);
    }

    /**
     * Indicate that the user is a service client user.
     */
    public function serviceClient(): static
    {
        return $this->withRole(User::ROLE_SERVICE_CLIENT);
    }
}
