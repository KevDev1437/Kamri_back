<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('shows order details for owner', function () {
    $u = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $u->id, 'number' => 'CMD-2025-000001']);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->json();

    expect($res['id'])->toBe($order->id);
    expect($res['number'])->toBe('CMD-2025-000001');
    expect($res)->toHaveKeys(['id', 'number', 'status', 'date', 'total', 'currency', 'timeline']);
});

it('denies access to other users orders', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->withHeaders(authHeaders($me))
        ->getJson("/api/orders/{$order->id}")
        ->assertStatus(403);
});
