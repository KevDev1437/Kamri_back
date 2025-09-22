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
});

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
