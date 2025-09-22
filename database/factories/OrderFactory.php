<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'number' => 'CMD-' . now()->format('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'canceled']),
            'currency' => 'EUR',
            'subtotal' => $this->faker->randomFloat(2, 10, 200),
            'discount' => 0,
            'shipping_price' => $this->faker->randomFloat(2, 0, 20),
            'tax' => $this->faker->randomFloat(2, 0, 50),
            'total' => $this->faker->randomFloat(2, 20, 300),
            'delivery_method' => [
                'code' => 'standard',
                'label' => 'Livraison Standard',
                'eta' => '2-3 jours',
                'price' => 4.99
            ],
            'shipping_address' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postalCode' => $this->faker->postcode(),
                'country' => 'BE'
            ],
            'billing_address' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postalCode' => $this->faker->postcode(),
                'country' => 'BE'
            ],
            'payment_intent_id' => null,
            'meta' => null,
        ];
    }
}
