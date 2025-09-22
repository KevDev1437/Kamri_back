<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function h(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('shows empty wishlist and adds/toggles items', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create(['price' => 19.99, 'stock' => 10]);

    $this->withHeaders(h($u))->getJson('/api/wishlist')
        ->assertOk()
        ->assertJsonPath('count', 0);

    $this->withHeaders(h($u))->postJson('/api/wishlist', [
        'product_id' => $p->id, 'options' => ['size' => 'M']
    ])->assertOk()->assertJsonPath('count', 1);

    // toggle remove
    $this->withHeaders(h($u))->postJson('/api/wishlist/toggle', [
        'product_id' => $p->id, 'options' => ['size' => 'M']
    ])->assertOk()->assertJsonPath('count', 0);

    // toggle add
    $this->withHeaders(h($u))->postJson('/api/wishlist/toggle', [
        'product_id' => $p->id, 'options' => ['size' => 'M']
    ])->assertOk()->assertJsonPath('count', 1);
});

it('merges client wishlist', function () {
    $u = User::factory()->create();
    $p1 = Product::factory()->create();
    $p2 = Product::factory()->create();

    $this->withHeaders(h($u))->postJson('/api/wishlist/merge', [
        'items' => [
            ['product_id' => $p1->id, 'options' => ['color' => 'red']],
            ['product_id' => $p2->id],
        ]
    ])->assertOk()->assertJsonPath('count', 2);
});

it('moves to cart and clears wishlist items', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create(['stock' => 5, 'price' => 10]);

    // add to wishlist
    $this->withHeaders(h($u))->postJson('/api/wishlist', ['product_id' => $p->id])->assertOk();

    $wishlist = Wishlist::where('user_id', $u->id)->first();
    $item = $wishlist->items()->first();

    // move single
    $this->withHeaders(h($u))->postJson('/api/wishlist/move-to-cart', [
        'item_id' => $item->id
    ])->assertOk()->assertJsonPath('count', 0);
});

it('clears wishlist', function () {
    $u = User::factory()->create();
    $p = Product::factory()->create();

    $this->withHeaders(h($u))->postJson('/api/wishlist', ['product_id' => $p->id])->assertOk();
    $this->withHeaders(h($u))->deleteJson('/api/wishlist')->assertOk()->assertJsonPath('count', 0);
});
