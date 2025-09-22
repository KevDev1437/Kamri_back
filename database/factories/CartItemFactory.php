<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'cart_id' => Cart::factory(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_image' => $product->image,
            'unit_price' => $product->price,
            'qty' => $this->faker->numberBetween(1, 5),
            'options' => null,
            'options_hash' => '',
        ];
    }
}
