<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->word() . $this->faker->numberBetween(10, 99)),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => $this->faker->randomFloat(2, 5, 50),
            'min_subtotal' => $this->faker->randomFloat(2, 0, 100),
            'max_uses' => $this->faker->numberBetween(10, 1000),
            'used_count' => 0,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addMonths(6),
            'active' => true,
        ];
    }
}
