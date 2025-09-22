<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first() ?? Product::factory();
        $qty = fake()->numberBetween(1, 3);
        $unitPrice = $product->sale_price ?? $product->price;

        return [
            'order_id' => null, // Sera défini dans le seeder
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => $product->image,
            'unit_price' => $unitPrice,
            'qty' => $qty,
            'subtotal' => $unitPrice * $qty,
            'options' => fake()->boolean(30) ? [
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                'color' => fake()->randomElement(['Rouge', 'Bleu', 'Vert', 'Noir', 'Blanc']),
            ] : null,
            'options_hash' => '',
        ];
    }
}
