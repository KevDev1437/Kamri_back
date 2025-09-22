<?php

use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('returns shipping methods for country', function () {
    $u = User::factory()->create();

    ShippingMethod::factory()->create(['code' => 'standard', 'active' => true, 'countries' => ['BE','FR']]);
    ShippingMethod::factory()->create(['code' => 'express', 'active' => true, 'countries' => ['BE','FR']]);
    ShippingMethod::factory()->create(['code' => 'local', 'active' => true, 'countries' => ['US']]);

    $res = $this->withHeaders(authHeaders($u))
        ->getJson('/api/shipping/methods?country=BE')
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['methods'])->toHaveCount(2);
    expect(collect($res['methods'])->pluck('code'))->toContain('standard', 'express');
});
