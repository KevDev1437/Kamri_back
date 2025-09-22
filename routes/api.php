<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\LiveStreamController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ReviewActionsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\CouponsController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\ReviewsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test de connectivité
Route::get('/test', function () {
    return response()->json([
        'message' => 'API KAMRI connectée 🎉',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Routes d'authentification (publiques)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Adresses
    Route::apiResource('addresses', AddressController::class)->only(['index','store','show','update','destroy']);
    Route::post('addresses/{address}/default-shipping', [AddressController::class, 'setDefaultShipping']);
    Route::post('addresses/{address}/default-billing', [AddressController::class, 'setDefaultBilling']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'show']);
    Route::post('/wishlist', [WishlistController::class, 'add']);
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']);
    Route::delete('/wishlist/{item}', [WishlistController::class, 'removeItem']);
    Route::delete('/wishlist', [WishlistController::class, 'clear']);
    Route::post('/wishlist/merge', [WishlistController::class, 'merge']);
    Route::post('/wishlist/move-to-cart', [WishlistController::class, 'moveToCart']);

    // Cart
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/{item}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/merge', [CartController::class, 'merge']);

    // Checkout
    Route::get('/shipping/methods', [ShippingController::class, 'index']);
    Route::post('/coupons/validate', [CouponsController::class, 'validateCode']);
    Route::post('/checkout', [CheckoutController::class, 'placeOrder']);

    // Payments
    Route::post('/payments/create-intent', [PaymentsController::class, 'createIntent']);

    // Orders
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/orders/{order}', [OrdersController::class, 'show'])->can('view', 'order');
    Route::get('/orders/{order}/invoice', [OrdersController::class, 'invoice'])->can('view', 'order');
    Route::post('/orders/{order}/reorder', [OrdersController::class, 'reorder'])->can('view', 'order');

    // Reviews
    Route::post('/products/{product}/reviews', [ReviewsController::class, 'store']);
    Route::post('/reviews/{review}/helpful', [ReviewsController::class, 'voteHelpful']);
    Route::post('/reviews/{review}/report', [ReviewsController::class, 'report']);
});

// Reviews (GET public, POST protégés)
Route::get('/products/{product}/reviews', [ReviewsController::class, 'index']);


// Webhooks (public)
Route::post('/payments/webhook', StripeWebhookController::class);

// Catégories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/hot', [CategoryController::class, 'hot']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Produits
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Articles du magazine
Route::get('/magazine', [ArticleController::class, 'index']);
Route::get('/magazine/{id}', [ArticleController::class, 'show']);

// Live streaming
Route::get('/live', [LiveStreamController::class, 'index']);
Route::get('/live/scheduled', [LiveStreamController::class, 'scheduled']);
