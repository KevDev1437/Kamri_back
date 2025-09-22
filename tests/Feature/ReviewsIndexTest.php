<?php

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists reviews with pagination and summary', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 4]);

    $res = $this->getJson("/api/products/{$product->id}/reviews")
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['items'])->toHaveCount(2);
    expect($res['total'])->toBe(2);
    expect($res['average'])->toBeFloat();
    expect($res['counts'])->toHaveKeys(['5', '4', '3', '2', '1']);
    expect($res['counts']['5'])->toBe(1);
    expect($res['counts']['4'])->toBe(1);
});

it('filters reviews by rating', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 3]);

    $res = $this->getJson("/api/products/{$product->id}/reviews?rating=5")
        ->assertOk()
        ->json();

    expect($res['items'])->toHaveCount(1);
    expect($res['items'][0]['rating'])->toBe(5);
});

it('filters reviews with photos', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'photos' => ['photo1.jpg']]);
    Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'photos' => null]);

    $res = $this->getJson("/api/products/{$product->id}/reviews?with_photos=true")
        ->assertOk()
        ->json();

    expect($res['items'])->toHaveCount(1);
    expect($res['items'][0]['photos'])->toHaveCount(1);
});

it('sorts reviews correctly', function () {
    $product = Product::factory()->create();
    $user = User::factory()->create();

    $review1 = Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 3]);
    $review2 = Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);

    $res = $this->getJson("/api/products/{$product->id}/reviews?sort=rating_desc")
        ->assertOk()
        ->json();

    expect($res['items'][0]['rating'])->toBe(5);
    expect($res['items'][1]['rating'])->toBe(3);
});
