<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Models\Payment;

class StripeService
{
    public function __construct(private StripeClient $client) {}

    public static function make(): self {
        return new self(new StripeClient(config('stripe.secret')));
    }

    public function createIntent(int $amountCents, string $currency = 'EUR', array $metadata = [], ?string $idempotencyKey = null): array {
        $params = [
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'metadata' => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ];
        $opts = $idempotencyKey ? ['idempotency_key' => $idempotencyKey] : [];
        $pi = $this->client->paymentIntents->create($params, $opts);
        return ['id' => $pi->id, 'client_secret' => $pi->client_secret, 'status' => $pi->status];
    }

    public function retrieveIntent(string $intentId) {
        return $this->client->paymentIntents->retrieve($intentId);
    }

    public function findOrCreatePaymentRecord(?int $userId, string $intentId, int $amountCents, string $currency = 'EUR'): Payment {
        $amount = round($amountCents / 100, 2);
        return Payment::firstOrCreate(
            ['intent_id' => $intentId],
            ['user_id' => $userId, 'amount' => $amount, 'currency' => strtoupper($currency)]
        );
    }
}
