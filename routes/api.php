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
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
    Route::delete('/wishlist', [WishlistController::class, 'clear']); // optionnel
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle']); // optionnel

    // Cart
    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart', [CartController::class, 'add']);
    Route::put('/cart/{item}', [CartController::class, 'updateItem']);
    Route::delete('/cart/{item}', [CartController::class, 'removeItem']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/merge', [CartController::class, 'merge']);
});

// Reviews (GET public, POST protégés)
Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index']);
Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->middleware('auth:sanctum');

Route::post('/reviews/{review}/helpful', [ReviewActionsController::class, 'helpful'])->middleware('auth:sanctum');
Route::post('/reviews/{review}/report', [ReviewActionsController::class, 'report'])->middleware('auth:sanctum');

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
