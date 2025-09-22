<?php

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('validates percentage coupon successfully', function () {
    Coupon::create([
        'code' => 'WELCOME10',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'min_subtotal' => 30.00,
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'WELCOME10',
        'items' => [
            ['product_id' => 1, 'qty' => 2, 'price' => 29.99],
            ['product_id' => 2, 'qty' => 1, 'price' => 10.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeTrue();
    expect($res['type'])->toBe('percentage');
    expect($res['value'])->toBe(10.0);
    expect($res['eligibleSubtotal'])->toBe(69.98);
    expect($res['discount'])->toBe(7.0); // 10% de 69.98
});

it('validates fixed coupon successfully', function () {
    Coupon::create([
        'code' => 'SAVE20',
        'type' => 'fixed',
        'value' => 20.00,
        'active' => true,
        'min_subtotal' => 50.00,
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'SAVE20',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 60.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeTrue();
    expect($res['type'])->toBe('fixed');
    expect($res['discount'])->toBe(20.0);
});

it('validates free shipping coupon successfully', function () {
    Coupon::create([
        'code' => 'FREESHIP',
        'type' => 'free_shipping',
        'value' => null,
        'active' => true,
        'min_subtotal' => 25.00,
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'FREESHIP',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 30.00],
        ],
        'shipping' => 4.99,
    ])->assertOk()->json();

    expect($res['success'])->toBeTrue();
    expect($res['type'])->toBe('free_shipping');
    expect($res['shippingDiscount'])->toBe(4.99);
});

it('rejects invalid coupon code', function () {
    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'INVALID',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 30.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeFalse();
    expect($res['message'])->toBe('Code promo invalide');
});

it('rejects inactive coupon', function () {
    Coupon::create([
        'code' => 'INACTIVE',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => false,
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'INACTIVE',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 30.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeFalse();
    expect($res['message'])->toBe('Ce code promo n\'est plus valide');
});

it('rejects expired coupon', function () {
    Coupon::create([
        'code' => 'EXPIRED',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'starts_at' => now()->subDays(30),
        'ends_at' => now()->subDays(1),
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'EXPIRED',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 30.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeFalse();
    expect($res['message'])->toBe('Ce code promo n\'est plus valide');
});

it('rejects coupon when minimum subtotal not met', function () {
    Coupon::create([
        'code' => 'MIN30',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'min_subtotal' => 30.00,
        'applies_to' => 'all',
    ]);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'MIN30',
        'items' => [
            ['product_id' => 1, 'qty' => 1, 'price' => 20.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeFalse();
    expect($res['message'])->toContain('Minimum d\'achat');
});

it('validates coupon with product targeting', function () {
    $product = Product::factory()->create();

    $coupon = Coupon::create([
        'code' => 'PRODUCT10',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'applies_to' => 'products',
    ]);

    $coupon->products()->attach($product->id);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'PRODUCT10',
        'items' => [
            ['product_id' => $product->id, 'qty' => 1, 'price' => 30.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeTrue();
    expect($res['eligibleSubtotal'])->toBe(30.0);
});

it('rejects coupon when product not eligible', function () {
    $product = Product::factory()->create();
    $otherProduct = Product::factory()->create();

    $coupon = Coupon::create([
        'code' => 'PRODUCT10',
        'type' => 'percentage',
        'value' => 10.00,
        'active' => true,
        'applies_to' => 'products',
    ]);

    $coupon->products()->attach($product->id);

    $res = $this->postJson('/api/coupons/validate', [
        'code' => 'PRODUCT10',
        'items' => [
            ['product_id' => $otherProduct->id, 'qty' => 1, 'price' => 30.00],
        ],
    ])->assertOk()->json();

    expect($res['success'])->toBeFalse();
    expect($res['message'])->toBe('Ce code promo ne s\'applique pas aux articles de votre panier');
});
