<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists only my addresses', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    Address::factory()->for($me)->create(['label' => 'Mine']);
    Address::factory()->for($other)->create(['label' => 'Other']);

    $token = $me->createToken('test')->plainTextToken;

    $res = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/addresses')
        ->assertOk()
        ->json();

    expect(collect($res['data'] ?? $res)->pluck('label'))->toContain('Mine')->not->toContain('Other');
});

it('creates address and can set as default shipping/billing', function () {
    $me = User::factory()->create();
    $token = $me->createToken('test')->plainTextToken;

    $payload = [
        'label' => 'Maison',
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'line1' => 'Rue de la Paix 123',
        'postal_code' => '1000',
        'city' => 'Bruxelles',
        'country' => 'BE',
        'is_default_shipping' => true,
        'is_default_billing' => true,
    ];

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/addresses', $payload)
        ->assertCreated();

    $addr = Address::where('user_id', $me->id)->first();
    expect($addr->is_default_shipping)->toBeTrue();
    expect($addr->is_default_billing)->toBeTrue();
});

it('enforces single default per type', function () {
    $me = User::factory()->create();
    $token = $me->createToken('test')->plainTextToken;

    $a = Address::factory()->for($me)->create(['is_default_shipping' => true]);
    $b = Address::factory()->for($me)->create();

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson("/api/addresses/{$b->id}/default-shipping")
        ->assertOk();

    $a->refresh(); $b->refresh();

    expect($a->is_default_shipping)->toBeFalse();
    expect($b->is_default_shipping)->toBeTrue();
});

it('reassigns default on delete', function () {
    $me = User::factory()->create();
    $token = $me->createToken('test')->plainTextToken;

    $a = Address::factory()->for($me)->create(['is_default_shipping' => true]);
    $b = Address::factory()->for($me)->create();
    $c = Address::factory()->for($me)->create();

    $this->withHeader('Authorization', "Bearer $token")
        ->deleteJson("/api/addresses/{$a->id}")
        ->assertOk();

    // one of b/c becomes default shipping
    $me->refresh();
    $defaults = $me->addresses()->where('is_default_shipping', true)->count();
    expect($defaults)->toBe(1);
});
