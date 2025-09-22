<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Product;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->paragraphs(2, true),
            'helpful_count' => fake()->numberBetween(0, 50),
            'verified' => fake()->boolean(80), // 80% des avis sont vérifiés
            'anonymous' => fake()->boolean(10), // 10% sont anonymes
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
