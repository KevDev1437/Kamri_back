<?php

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authHeaders(User $u): array {
    $t = $u->createToken('t')->plainTextToken;
    return ['Authorization' => "Bearer $t"];
}

it('applies coupon during checkout and records redemption', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 50.00]);

    // Créer un coupon
    $coupon = Coupon::create([
        'code' => 'CHECKOUT10',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'applies_to' => 'all',
    ]);

    // Créer un panier
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 2,
        'unit_price' => 50.00,
        'subtotal' => 100.00,
    ]);

    // Créer une méthode de livraison
    ShippingMethod::factory()->create([
        'code' => 'standard',
        'label' => 'Livraison standard',
        'price' => 4.99,
    ]);

    $res = $this->withHeaders(authHeaders($user))
        ->postJson('/api/checkout', [
            'deliveryMethod' => 'standard',
            'couponCode' => 'CHECKOUT10',
            'shippingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
            'billingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
        ])
        ->assertCreated()
        ->json();

    // Vérifier que la commande a été créée avec la remise
    $order = Order::find($res['id']);
    expect($order->discount)->toBe(10.0); // 10% de 100€

    // Vérifier que la redemption a été enregistrée
    $redemption = CouponRedemption::where('coupon_id', $coupon->id)
        ->where('user_id', $user->id)
        ->where('order_id', $order->id)
        ->first();

    expect($redemption)->not->toBeNull();
    expect($redemption->amount)->toBe(10.0);
});

it('applies free shipping coupon during checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 30.00]);

    // Créer un coupon free shipping
    $coupon = Coupon::create([
        'code' => 'FREESHIP',
        'type' => 'free_shipping',
        'value' => null,
        'active' => true,
        'applies_to' => 'all',
    ]);

    // Créer un panier
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'unit_price' => 30.00,
        'subtotal' => 30.00,
    ]);

    // Créer une méthode de livraison
    ShippingMethod::factory()->create([
        'code' => 'standard',
        'label' => 'Livraison standard',
        'price' => 4.99,
    ]);

    $res = $this->withHeaders(authHeaders($user))
        ->postJson('/api/checkout', [
            'deliveryMethod' => 'standard',
            'couponCode' => 'FREESHIP',
            'shippingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
            'billingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
        ])
        ->assertCreated()
        ->json();

    // Vérifier que la commande a été créée avec la livraison gratuite
    $order = Order::find($res['id']);
    expect($order->shipping_price)->toBe(0.0); // Livraison gratuite

    // Vérifier que la redemption a été enregistrée
    $redemption = CouponRedemption::where('coupon_id', $coupon->id)
        ->where('user_id', $user->id)
        ->where('order_id', $order->id)
        ->first();

    expect($redemption)->not->toBeNull();
    expect($redemption->amount)->toBe(4.99); // Montant de la livraison
});

it('ignores invalid coupon during checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 30.00]);

    // Créer un panier
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'qty' => 1,
        'unit_price' => 30.00,
        'subtotal' => 30.00,
    ]);

    // Créer une méthode de livraison
    ShippingMethod::factory()->create([
        'code' => 'standard',
        'label' => 'Livraison standard',
        'price' => 4.99,
    ]);

    $res = $this->withHeaders(authHeaders($user))
        ->postJson('/api/checkout', [
            'deliveryMethod' => 'standard',
            'couponCode' => 'INVALID',
            'shippingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
            'billingAddress' => [
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'line1' => 'Rue de la Paix 123',
                'postalCode' => '1000',
                'city' => 'Bruxelles',
                'country' => 'BE',
            ],
        ])
        ->assertCreated()
        ->json();

    // Vérifier que la commande a été créée sans remise
    $order = Order::find($res['id']);
    expect($order->discount)->toBe(0.0);
    expect($order->shipping_price)->toBe(4.99);

    // Vérifier qu'aucune redemption n'a été enregistrée
    $redemptionCount = CouponRedemption::count();
    expect($redemptionCount)->toBe(0);
});
