<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(1),
            'label' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 0, 20),
            'eta' => $this->faker->randomElement(['1-2 jours', '2-3 jours', '3-5 jours', '24-48h']),
            'active' => true,
            'countries' => ['BE', 'FR', 'NL', 'DE', 'LU'],
        ];
    }
}
