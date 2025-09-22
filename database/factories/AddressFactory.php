<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->randomElement(['Maison','Bureau','Parents']),
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'line1' => fake()->streetAddress(),
            'line2' => null,
            'postal_code' => '1000',
            'city' => 'Bruxelles',
            'country' => 'BE',
            'phone' => '+324' . fake()->randomNumber(7, true),
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ];
    }
}
