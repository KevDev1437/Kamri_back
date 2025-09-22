<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'image' => 'https://picsum.photos/seed/' . Str::slug($name) . '/400/300',
            'is_hot' => fake()->boolean(20), // 20% chance d'être "hot"
            'parent_id' => null,
            'description' => fake()->paragraph(2),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
