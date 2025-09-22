<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'number' => 'CMD-' . now()->format('Y') . '-' . fake()->unique()->numberBetween(100000, 999999),
            'status' => fake()->randomElement(['pending', 'paid', 'processing', 'shipped', 'delivered', 'canceled']),
            'currency' => 'EUR',
            'subtotal' => 0, // Sera calculé dans le seeder
            'discount' => 0,
            'shipping_price' => fake()->randomElement([0, 4.99, 6.99, 9.99]),
            'tax' => 0, // Sera calculé dans le seeder
            'total' => 0, // Sera calculé dans le seeder
            'paid_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-2 months', 'now') : null,
            'delivery_method' => [
                'code' => fake()->randomElement(['standard', 'express', 'pickup']),
                'label' => fake()->randomElement(['Livraison standard', 'Livraison express', 'Retrait en magasin']),
                'eta' => fake()->randomElement(['2-3 jours', '24h', '1h']),
                'price' => fake()->randomFloat(2, 0, 9.99),
            ],
            'shipping_address' => [
                'firstName' => fake()->firstName(),
                'lastName' => fake()->lastName(),
                'line1' => fake()->streetAddress(),
                'postalCode' => (string) fake()->numberBetween(1000, 9999),
                'city' => fake()->city(),
                'country' => fake()->randomElement(['BE', 'FR', 'DE', 'NL', 'LU']),
            ],
            'billing_address' => [
                'firstName' => fake()->firstName(),
                'lastName' => fake()->lastName(),
                'line1' => fake()->streetAddress(),
                'postalCode' => (string) fake()->numberBetween(1000, 9999),
                'city' => fake()->city(),
                'country' => fake()->randomElement(['BE', 'FR', 'DE', 'NL', 'LU']),
            ],
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
