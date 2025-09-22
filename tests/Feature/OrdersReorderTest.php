<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('returns reorder items for owner', function () {
    $u = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $u->id]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => 1,
        'product_name' => 'Test Product',
        'unit_price' => 29.99,
        'qty' => 2,
        'subtotal' => 59.98
    ]);

    $res = $this->withHeaders(authHeaders($u))
        ->postJson("/api/orders/{$order->id}/reorder")
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['items'])->toHaveCount(1);
    expect($res['items'][0])->toMatchArray([
        'product_id' => 1,
        'name' => 'Test Product',
        'price' => 29.99,
        'qty' => 2,
    ]);
});

it('denies reorder access to other users', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->withHeaders(authHeaders($me))
        ->postJson("/api/orders/{$order->id}/reorder")
        ->assertStatus(403);
});
