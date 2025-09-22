<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('prevents owner from voting their own review', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);

    $this->withHeaders(authHeaders($user))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertStatus(403);
});

it('allows non-owner to vote helpful', function () {
    $owner = User::factory()->create();
    $voter = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'helpful_count' => 0]);

    $res = $this->withHeaders(authHeaders($voter))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();
    expect($res['helpfulCount'])->toBe(1);
});

it('handles double vote gracefully', function () {
    $owner = User::factory()->create();
    $voter = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'helpful_count' => 1]);

    // Premier vote
    $this->withHeaders(authHeaders($voter))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertOk();

    // Deuxième vote
    $res = $this->withHeaders(authHeaders($voter))
        ->postJson("/api/reviews/{$review->id}/helpful")
        ->assertOk()
        ->json();

    expect($res['alreadyVoted'])->toBeTrue();
    expect($res['helpfulCount'])->toBe(1); // Pas d'incrémentation
});
