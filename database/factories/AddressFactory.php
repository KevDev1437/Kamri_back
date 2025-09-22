<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null, // Sera défini dans le seeder
            'label' => fake()->randomElement(['Maison', 'Bureau', 'Parents', 'Appartement']),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'line1' => fake()->streetAddress(),
            'line2' => fake()->boolean(30) ? 'Boîte ' . fake()->numberBetween(1, 30) : null,
            'postal_code' => (string) fake()->numberBetween(1000, 9999),
            'city' => fake()->city(),
            'country' => fake()->randomElement(['BE', 'FR', 'DE', 'NL', 'LU']),
            'phone' => '+32 ' . fake()->numberBetween(400000000, 499999999),
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ];
    }
}
