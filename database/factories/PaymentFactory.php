<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'provider' => 'stripe',
            'intent_id' => 'pi_' . $this->faker->unique()->regexify('[a-zA-Z0-9]{24}'),
            'status' => $this->faker->randomElement([
                'requires_payment_method', 'requires_confirmation', 'processing',
                'succeeded', 'requires_action', 'canceled', 'failed', 'refunded'
            ]),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'currency' => 'EUR',
            'last_error' => null,
            'meta' => null,
        ];
    }
}
