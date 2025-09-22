<?php

use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('creates and shows user cart', function () {
    $u = User::factory()->create();
    $this->withHeaders(authHeaders($u))
        ->getJson('/api/cart')
        ->assertOk()
        ->assertJsonStructure(['id','currency','items','totals']);
});

it('adds product to cart and increases qty if same options', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create(['price' => 19.90, 'stock' => 10]);

    $h = authHeaders($u);
    $this->withHeaders($h)->postJson('/api/cart', [
        'product_id' => $p->id, 'qty' => 2, 'options' => ['size' => 'M']
    ])->assertOk();

    $this->withHeaders($h)->postJson('/api/cart', [
        'product_id' => $p->id, 'qty' => 3, 'options' => ['size' => 'M']
    ])->assertOk()->assertJsonPath('items.0.qty', 5);
});

it('prevents adding more than stock', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create(['price' => 10, 'stock' => 2]);

    $this->withHeaders(authHeaders($u))
        ->postJson('/api/cart', ['product_id' => $p->id, 'qty' => 3])
        ->assertStatus(422);
});

it('updates and removes item', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create(['price' => 10, 'stock' => 10]);

    $h = authHeaders($u);

    $this->withHeaders($h)->postJson('/api/cart', [
        'product_id' => $p->id, 'qty' => 1
    ])->assertOk();

    $cart = Cart::where('user_id', $u->id)->first();
    $item = $cart->items()->first();

    $this->withHeaders($h)->putJson("/api/cart/{$item->id}", ['qty' => 4])
        ->assertOk()
        ->assertJsonPath('items.0.qty', 4);

    $this->withHeaders($h)->deleteJson("/api/cart/{$item->id}")
        ->assertOk()
        ->assertJsonPath('items', []);
});

it('merges client cart into server cart', function () {
    $u = User::factory()->create();
    $p1 = Product::factory()->create(['price' => 10, 'stock' => 10]);
    $p2 = Product::factory()->create(['price' => 20, 'stock' => 10]);

    $this->withHeaders(authHeaders($u))
        ->postJson('/api/cart/merge', [
            'items' => [
                ['product_id' => $p1->id, 'qty' => 2, 'options' => ['color' => 'red']],
                ['product_id' => $p2->id, 'qty' => 1],
            ]
        ])
        ->assertOk()
        ->assertJsonCount(2, 'items');
});
