<?php

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

it('lists reviews with summary', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 4]);

    $res = $this->getJson("/api/products/{$product->id}/reviews")->assertOk()->json();

    expect($res)->toHaveKeys(['items','total','average','counts']);
    expect($res['total'])->toBe(2);
    expect($res['average'])->toBeFloat();
    expect($res['counts']['5'])->toBe(1);
});

it('creates a review with photos', function () {
    Storage::fake('public');
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $payload = [
        'rating' => 5,
        'comment'=> str_repeat('Excellent ', 3),
        'anonymous' => false,
    ];

    $res = $this->withHeaders(array_merge(authHeaders($user), ['Accept' => 'application/json']))
        ->post("/api/products/{$product->id}/reviews", $payload, ['photos' => [
            UploadedFile::fake()->image('p1.jpg'),
            UploadedFile::fake()->image('p2.jpg'),
        ]]);

    $this->assertDatabaseHas('reviews', ['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);
});

it('votes helpful once', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertOk();

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertStatus(422);
});

it('reports once', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create();

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/reviews/{$review->id}/report", ['reason' => 'spam'])
        ->assertOk();

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/reviews/{$review->id}/report")
        ->assertStatus(422);
});
