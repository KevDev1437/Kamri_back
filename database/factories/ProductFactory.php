<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->words(3, true)) . ' ' . fake()->unique()->numberBetween(1, 9999);
        $price = fake()->randomFloat(2, 5, 300);
        $salePrice = fake()->boolean(30) ? $price - fake()->randomFloat(2, 5, 50) : null;

        $seed = Str::slug($name) . '-' . fake()->numberBetween(1, 9999);
        $image = "https://picsum.photos/seed/{$seed}/600/400";

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'short_description' => fake()->sentence(10),
            'price' => $price,
            'sale_price' => $salePrice,
            'sku' => 'SKU-' . fake()->unique()->numberBetween(100000, 999999),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'image' => $image,
            'images' => [
                "https://picsum.photos/seed/{$seed}-1/600/400",
                "https://picsum.photos/seed/{$seed}-2/600/400",
                "https://picsum.photos/seed/{$seed}-3/600/400",
            ],
            'category_id' => null, // Sera défini dans le seeder
            'is_featured' => fake()->boolean(10), // 10% chance d'être en vedette
            'is_active' => true,
            'weight' => fake()->randomFloat(2, 0.1, 10),
            'dimensions' => [
                'length' => fake()->numberBetween(10, 100),
                'width' => fake()->numberBetween(10, 100),
                'height' => fake()->numberBetween(5, 50),
            ],
            'meta_title' => $name . ' - KAMRI Marketplace',
            'meta_description' => fake()->sentence(15),
        ];
    }
}
