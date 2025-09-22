<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('handles payment_intent.succeeded webhook', function () {
    $u = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $u->id, 'status' => 'pending']);
    $payment = Payment::factory()->create([
        'user_id' => $u->id,
        'order_id' => $order->id,
        'intent_id' => 'pi_test_123',
        'status' => 'processing'
    ]);

    // Simuler un webhook payment_intent.succeeded
    $webhookPayload = [
        'id' => 'evt_test_webhook',
        'object' => 'event',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'object' => 'payment_intent',
                'status' => 'succeeded',
                'amount' => 6490,
                'currency' => 'eur'
            ]
        ]
    ];

    // Pour les tests, on peut bypasser la vérification de signature
    $res = $this->postJson('/api/payments/webhook', $webhookPayload)
        ->assertOk();

    // Vérifier que le payment et l'order sont mis à jour
    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe('succeeded');
    expect($order->status)->toBe('paid');
    expect($order->paid_at)->not->toBeNull();
    expect($order->payment_id)->toBe($payment->id);
});

it('handles payment_intent.payment_failed webhook', function () {
    $u = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $u->id, 'status' => 'pending']);
    $payment = Payment::factory()->create([
        'user_id' => $u->id,
        'order_id' => $order->id,
        'intent_id' => 'pi_test_456',
        'status' => 'processing'
    ]);

    $webhookPayload = [
        'id' => 'evt_test_webhook',
        'object' => 'event',
        'type' => 'payment_intent.payment_failed',
        'data' => [
            'object' => [
                'id' => 'pi_test_456',
                'object' => 'payment_intent',
                'status' => 'failed',
                'last_payment_error' => ['message' => 'Card declined']
            ]
        ]
    ];

    $res = $this->postJson('/api/payments/webhook', $webhookPayload)
        ->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe('failed');
    expect($payment->last_error)->toHaveKey('message');
    expect($order->status)->toBe('failed');
});
