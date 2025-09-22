<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('refuses review from non-buyer', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Excellent produit, très satisfait !',
        ])
        ->assertStatus(403);
});

it('allows review from buyer', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $product = Product::factory()->create();

    // Créer une commande payée avec ce produit
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 1
    ]);

    $res = $this->withHeaders(authHeaders($user))
        ->postJson("/api/products/{$product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Excellent produit, très satisfait !',
            'anonymous' => false,
        ])
        ->assertStatus(201)
        ->json();

    expect($res['rating'])->toBe(5);
    expect($res['comment'])->toBe('Excellent produit, très satisfait !');
    expect($res['verified'])->toBeTrue();
    expect($res['user']['isAnonymous'])->toBeFalse();
});

it('uploads photos with review', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $product = Product::factory()->create();

    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'qty' => 1
    ]);

    $file1 = UploadedFile::fake()->image('photo1.jpg');
    $file2 = UploadedFile::fake()->image('photo2.jpg');

    $res = $this->withHeaders(authHeaders($user))
        ->post("/api/products/{$product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Excellent produit avec photos !',
            'photos' => [$file1, $file2],
        ])
        ->assertStatus(201);

    $review = Review::latest()->first();
    expect($review->photos)->toHaveCount(2);
    expect($review->photos[0])->toContain("reviews/{$product->id}/");
});
