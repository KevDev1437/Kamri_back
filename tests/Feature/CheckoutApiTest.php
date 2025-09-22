<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('creates order with cart items and clears cart', function () {
    $u = User::factory()->create();
    $p1 = Product::factory()->create(['price' => 20, 'stock' => 10]);
    $p2 = Product::factory()->create(['price' => 30, 'stock' => 5]);

    $cart = Cart::factory()->create(['user_id' => $u->id]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $p1->id, 'qty' => 2, 'unit_price' => 20]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $p2->id, 'qty' => 1, 'unit_price' => 30]);

    ShippingMethod::factory()->create(['code' => 'standard', 'price' => 4.99, 'active' => true]);
    Coupon::factory()->create(['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10, 'min_subtotal' => 50, 'active' => true]);

    $payload = [
        'email' => 'test@example.com',
        'shippingAddress' => ['firstName' => 'Jean', 'lastName' => 'Dupont', 'line1' => 'Rue 1', 'city' => 'Bruxelles', 'postalCode' => '1000', 'country' => 'BE'],
        'billingAddress' => ['firstName' => 'Jean', 'lastName' => 'Dupont', 'line1' => 'Rue 1', 'city' => 'Bruxelles', 'postalCode' => '1000', 'country' => 'BE'],
        'deliveryMethod' => ['code' => 'standard'],
        'coupon' => 'WELCOME10',
        'paymentIntentId' => 'pi_12345'
    ];

    $res = $this->withHeaders(authHeaders($u))
        ->postJson('/api/checkout', $payload)
        ->assertOk()
        ->json();

    expect($res['number'])->toStartWith('CMD-');
    expect($res['status'])->toBe('paid');
    expect($res['totals']['subtotal'])->toBe(70.0); // 2*20 + 1*30
    expect($res['totals']['discount'])->toBe(7.0); // 10% de 70
    expect($res['totals']['shipping'])->toBe(4.99);
    expect($res['totals']['tax'])->toBe(13.23); // 21% de (70-7)
    expect($res['totals']['total'])->toBe(81.22); // 70-7+4.99+13.23

    // Vérifier que le panier est vidé
    expect($cart->fresh()->items)->toHaveCount(0);

    // Vérifier que le stock est décrémenté
    expect($p1->fresh()->stock)->toBe(8); // 10-2
    expect($p2->fresh()->stock)->toBe(4); // 5-1
});
