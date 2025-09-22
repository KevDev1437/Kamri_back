<?php

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('allows user to report review', function () {
    $owner = User::factory()->create();
    $reporter = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'reported_count' => 0]);

    $res = $this->withHeaders(authHeaders($reporter))
        ->postJson("/api/reviews/{$review->id}/report", [
            'reason' => 'Contenu inapproprié'
        ])
        ->assertOk()
        ->json();

    expect($res['success'])->toBeTrue();

    $review->refresh();
    expect($review->reported_count)->toBe(1);
});

it('handles double report gracefully', function () {
    $owner = User::factory()->create();
    $reporter = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $owner->id, 'reported_count' => 1]);

    // Premier signalement
    $this->withHeaders(authHeaders($reporter))
        ->postJson("/api/reviews/{$review->id}/report")
        ->assertOk();

    // Deuxième signalement
    $res = $this->withHeaders(authHeaders($reporter))
        ->postJson("/api/reviews/{$review->id}/report")
        ->assertOk()
        ->json();

    expect($res['alreadyReported'])->toBeTrue();

    $review->refresh();
    expect($review->reported_count)->toBe(1); // Pas d'incrémentation
});
