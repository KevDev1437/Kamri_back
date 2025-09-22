<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $user): array {
    $token = $user->createToken('test')->plainTextToken;
    return ['Authorization' => "Bearer $token"];
}

it('lists only my wishlist products', function () {
    $me = User::factory()->create();
    $other = User::factory()->create();

    $p1 = Product::factory()->create();
    $p2 = Product::factory()->create();

    $me->wishlistProducts()->attach($p1->id);
    $other->wishlistProducts()->attach($p2->id);

    $res = $this->withHeaders(authHeaders($me))
        ->getJson('/api/wishlist')
        ->assertOk()
        ->json();

    $ids = collect($res['data'] ?? $res)->pluck('id');
    expect($ids)->toContain($p1->id)->not->toContain($p2->id);
});

it('adds product to wishlist once', function () {
    $me = User::factory()->create();
    $p = Product::factory()->create();

    $this->withHeaders(authHeaders($me))
        ->postJson('/api/wishlist', ['product_id' => $p->id])
        ->assertCreated();

    // duplicate
    $this->withHeaders(authHeaders($me))
        ->postJson('/api/wishlist', ['product_id' => $p->id])
        ->assertStatus(422); // déjà présent
});

it('removes product from wishlist', function () {
    $me = User::factory()->create();
    $p = Product::factory()->create();

    $me->wishlistProducts()->attach($p->id);

    $this->withHeaders(authHeaders($me))
        ->deleteJson("/api/wishlist/{$p->id}")
        ->assertOk();

    $this->assertDatabaseMissing('wishlist_items', [
        'user_id' => $me->id, 'product_id' => $p->id
    ]);
});

it('can clear wishlist', function () {
    $me = User::factory()->create();
    $p1 = Product::factory()->create();
    $p2 = Product::factory()->create();

    $me->wishlistProducts()->attach([$p1->id, $p2->id]);

    $this->withHeaders(authHeaders($me))
        ->deleteJson('/api/wishlist')
        ->assertOk();

    $this->assertDatabaseCount('wishlist_items', 0);
});

it('can toggle product in wishlist', function () {
    $me = User::factory()->create();
    $p = Product::factory()->create();

    // Add
    $res = $this->withHeaders(authHeaders($me))
        ->postJson('/api/wishlist/toggle', ['product_id' => $p->id])
        ->assertOk()
        ->json();

    expect($res['active'])->toBeTrue();
    expect($res['count'])->toBe(1);

    // Remove
    $res = $this->withHeaders(authHeaders($me))
        ->postJson('/api/wishlist/toggle', ['product_id' => $p->id])
        ->assertOk()
        ->json();

    expect($res['active'])->toBeFalse();
    expect($res['count'])->toBe(0);
});
