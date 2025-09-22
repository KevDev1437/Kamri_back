<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCompactResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Eager-load minimal pour la grille
        $products = $user->wishlistProducts()
            ->select(['products.id','products.name','products.price','products.sale_price','products.image','products.rating','products.reviews_count','products.stock_quantity','products.eco_score','products.is_featured'])
            ->get();

        return ProductCompactResource::collection($products);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
        ]);

        $productId = (int) $data['product_id'];

        // Empêcher les doublons (grâce à la contrainte unique, try-catch safe)
        if ($user->wishlistProducts()->where('product_id', $productId)->exists()) {
            throw ValidationException::withMessages([
                'product_id' => 'Ce produit est déjà dans votre wishlist.',
            ]);
        }

        $user->wishlistProducts()->attach($productId);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté à la wishlist',
            'count'   => $user->wishlistProducts()->count(),
        ], 201);
    }

    public function destroy(Request $request, Product $product)
    {
        $user = $request->user();
        $user->wishlistProducts()->detach($product->id);

        return response()->json([
            'success' => true,
            'message' => 'Produit retiré de la wishlist',
            'count'   => $user->wishlistProducts()->count(),
        ]);
    }

    public function clear(Request $request)
    {
        $user = $request->user();
        $user->wishlistProducts()->detach();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist vidée',
            'count'   => 0,
        ]);
    }

    // Optionnel : toggle add/remove
    public function toggle(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
        ]);

        $productId = (int) $data['product_id'];
        $exists = $user->wishlistProducts()->where('product_id', $productId)->exists();

        if ($exists) {
            $user->wishlistProducts()->detach($productId);
            $message = 'Produit retiré de la wishlist';
        } else {
            $user->wishlistProducts()->attach($productId);
            $message = 'Produit ajouté à la wishlist';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'count'   => $user->wishlistProducts()->count(),
            'active'  => !$exists,
        ]);
    }
}
