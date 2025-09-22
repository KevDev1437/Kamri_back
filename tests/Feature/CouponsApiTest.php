<?php

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('validates coupon with sufficient subtotal', function () {
    $u = User::factory()->create();

    Coupon::factory()->create([
        'code' => 'WELCOME10',
        'type' => 'percentage',
        'value' => 10,
        'min_subtotal' => 50,
        'active' => true
    ]);

    $res = $this->withHeaders(authHeaders($u))
        ->postJson('/api/coupons/validate', [
            'code' => 'WELCOME10',
            'subtotal' => 60
        ])
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['coupon']['discount'])->toBe(6.0); // 10% de 60
});

it('rejects coupon with insufficient subtotal', function () {
    $u = User::factory()->create();

    Coupon::factory()->create([
        'code' => 'WELCOME10',
        'type' => 'percentage',
        'value' => 10,
        'min_subtotal' => 50,
        'active' => true
    ]);

    $this->withHeaders(authHeaders($u))
        ->postJson('/api/coupons/validate', [
            'code' => 'WELCOME10',
            'subtotal' => 40
        ])
        ->assertStatus(422);
});
