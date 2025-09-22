<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('creates payment intent and stores in database', function () {
    $u = User::factory()->create();

    $res = $this->withHeaders(authHeaders($u))
        ->postJson('/api/payments/create-intent', [
            'amount' => 6490, // 64.90€
            'currency' => 'EUR',
            'metadata' => ['cart' => 'abc123']
        ])
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['intent_id'])->toStartWith('pi_');
    expect($res['client_secret'])->toStartWith('pi_');
    expect($res['status'])->toBeString();

    // Vérifier que le payment est stocké en DB
    $payment = Payment::where('intent_id', $res['intent_id'])->first();
    expect($payment)->not->toBeNull();
    expect($payment->user_id)->toBe($u->id);
    expect($payment->amount)->toBe(64.90);
    expect($payment->currency)->toBe('EUR');
});

it('respects idempotency key', function () {
    $u = User::factory()->create();
    $headers = array_merge(authHeaders($u), ['X-Idempotency-Key' => 'test-key-123']);

    $res1 = $this->withHeaders($headers)
        ->postJson('/api/payments/create-intent', ['amount' => 1000])
        ->assertOk()
        ->json();

    $res2 = $this->withHeaders($headers)
        ->postJson('/api/payments/create-intent', ['amount' => 1000])
        ->assertOk()
        ->json();

    // Même intent_id avec la même clé d'idempotence
    expect($res1['intent_id'])->toBe($res2['intent_id']);
});
