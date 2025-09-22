<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('generates invoice PDF for owner', function () {
    $u = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $u->id, 'number' => 'CMD-2025-000001']);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson("/api/orders/{$order->id}/invoice")
        ->assertOk();

    expect($res->headers->get('Content-Type'))->toContain('application/pdf');
    expect($res->headers->get('Content-Disposition'))->toContain('facture-CMD-2025-000001.pdf');
});

it('denies invoice access to other users', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->withHeaders(authHeaders($me))
        ->getJson("/api/orders/{$order->id}/invoice")
        ->assertStatus(403);
});
