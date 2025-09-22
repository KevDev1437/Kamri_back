<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('lists only my orders with pagination', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    Order::factory()->create(['user_id' => $me->id, 'number' => 'CMD-2025-000001']);
    Order::factory()->create(['user_id' => $me->id, 'number' => 'CMD-2025-000002']);
    Order::factory()->create(['user_id' => $other->id, 'number' => 'CMD-2025-000003']);

    $res = $this->withHeaders(authHeaders($me))
        ->getJson('/api/orders')
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['items'])->toHaveCount(2);
    expect($res['total'])->toBe(2);
    expect(collect($res['items'])->pluck('number'))->toContain('CMD-2025-000001', 'CMD-2025-000002');
});

it('filters orders by status', function () {
    $u = User::factory()->create();

    Order::factory()->create(['user_id' => $u->id, 'status' => 'paid']);
    Order::factory()->create(['user_id' => $u->id, 'status' => 'pending']);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson('/api/orders?status=paid')
        ->assertOk()
        ->json();

    expect($res['items'])->toHaveCount(1);
    expect($res['items'][0]['status'])->toBe('paid');
});

it('filters orders by date range', function () {
    $u = User::factory()->create();

    Order::factory()->create(['user_id' => $u->id, 'created_at' => now()->subDays(5)]);
    Order::factory()->create(['user_id' => $u->id, 'created_at' => now()->subDays(2)]);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson('/api/orders?date_from=' . now()->subDays(3)->format('Y-m-d'))
        ->assertOk()
        ->json();

    expect($res['items'])->toHaveCount(1);
});

it('searches orders by number', function () {
    $u = User::factory()->create();

    Order::factory()->create(['user_id' => $u->id, 'number' => 'CMD-2025-000001']);
    Order::factory()->create(['user_id' => $u->id, 'number' => 'CMD-2025-000002']);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson('/api/orders?q=000001')
        ->assertOk()
        ->json();

    expect($res['items'])->toHaveCount(1);
    expect($res['items'][0]['number'])->toBe('CMD-2025-000001');
});
