<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->paragraph(3),
            'photos' => null,
            'helpful_count' => $this->faker->numberBetween(0, 50),
            'reported_count' => 0,
            'verified' => true,
            'anonymous' => $this->faker->boolean(10), // 10% chance of being anonymous
            'status' => 'published',
        ];
    }
}
