<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function show(Request $request)
    {
        $wishlist = $this->getOrCreateWishlist($request->user()->id);
        return new WishlistResource($wishlist);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'options'    => ['nullable','array']
        ]);

        $wishlist = $this->getOrCreateWishlist($request->user()->id);
        $product  = Product::findOrFail($data['product_id']);

        $options = $data['options'] ?? [];
        $hash = $this->hashOptions($options);

        WishlistItem::firstOrCreate(
            [
                'wishlist_id'  => $wishlist->id,
                'product_id'   => $product->id,
                'options_hash' => $hash
            ],
            [
                'options' => $this->sortOptions($options) ?: null
            ]
        );

        return new WishlistResource($wishlist->fresh());
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'options'    => ['nullable','array']
        ]);

        $wishlist = $this->getOrCreateWishlist($request->user()->id);
        $product  = Product::findOrFail($data['product_id']);
        $hash     = $this->hashOptions($data['options'] ?? []);

        $item = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $product->id)
            ->where('options_hash', $hash)
            ->first();

        if ($item) {
            $item->delete();
        } else {
            WishlistItem::create([
                'wishlist_id'  => $wishlist->id,
                'product_id'   => $product->id,
                'options'      => $this->sortOptions($data['options'] ?? []) ?: null,
                'options_hash' => $hash
            ]);
        }

        return new WishlistResource($wishlist->fresh());
    }

    public function removeItem(Request $request, WishlistItem $item)
    {
        $this->authorizeItem($request, $item);
        $wishlist = $item->wishlist;
        $item->delete();
        return new WishlistResource($wishlist->fresh());
    }

    public function clear(Request $request)
    {
        $wishlist = $this->getOrCreateWishlist($request->user()->id);
        $wishlist->items()->delete();
        return new WishlistResource($wishlist->fresh());
    }

    public function merge(Request $request)
    {
        $data = $request->validate([
            'items' => ['required','array','max:500'],
            'items.*.product_id' => ['required','integer','exists:products,id'],
            'items.*.options'    => ['nullable','array'],
        ]);

        $wishlist = $this->getOrCreateWishlist($request->user()->id);

        DB::beginTransaction();
        try {
            foreach ($data['items'] as $row) {
                $product = Product::find($row['product_id']);
                if (!$product) continue;

                $hash = $this->hashOptions($row['options'] ?? []);
                WishlistItem::firstOrCreate(
                    [
                        'wishlist_id'  => $wishlist->id,
                        'product_id'   => $product->id,
                        'options_hash' => $hash
                    ],
                    [
                        'options' => $this->sortOptions($row['options'] ?? []) ?: null
                    ]
                );
            }
            DB::commit();
            return new WishlistResource($wishlist->fresh());
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Erreur merge wishlist'], 500);
        }
    }

    public function moveToCart(Request $request)
    {
        $data = $request->validate([
            'item_id' => ['nullable','integer','exists:wishlist_items,id'],
            'all'     => ['nullable','boolean']
        ]);

        $wishlist = $this->getOrCreateWishlist($request->user()->id);
        $cart     = $this->getOrCreateCart($request->user()->id);

        // Déplacer un seul item
        if (!empty($data['item_id'])) {
            $item = WishlistItem::findOrFail($data['item_id']);
            $this->authorizeItem($request, $item);
            $this->addToCartOrIncrement($cart, $item->product, 1, $item->options ?? []);
            $item->delete();
            return new WishlistResource($wishlist->fresh());
        }

        // Déplacer tous
        if (!empty($data['all'])) {
            foreach ($wishlist->items()->with('product')->get() as $wi) {
                $this->addToCartOrIncrement($cart, $wi->product, 1, $wi->options ?? []);
                $wi->delete();
            }
            return new WishlistResource($wishlist->fresh());
        }

        return response()->json(['message' => 'Paramètres invalides'], 422);
    }

    // Helpers
    private function getOrCreateWishlist(int $userId): Wishlist
    {
        return Wishlist::firstOrCreate(['user_id' => $userId]);
    }

    private function getOrCreateCart(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId], ['currency' => 'EUR']);
    }

    private function addToCartOrIncrement(Cart $cart, Product $product, int $qty, array $options = []): void
    {
        // Stock
        if (isset($product->stock) && $product->stock < 1) return;

        $optionsSorted = $this->sortOptions($options);
        $hash = md5(json_encode($optionsSorted));

        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('options_hash', $hash)
            ->first();

        $qty = max(1, min($qty, 99));
        if (isset($product->stock)) {
            $max = (int) $product->stock;
            if ($existing) $qty = min($existing->qty + $qty, $max, 99);
            else $qty = min($qty, $max);
            if ($qty <= 0) return;
        } else {
            if ($existing) $qty = min($existing->qty + $qty, 99);
        }

        if ($existing) {
            $existing->update(['qty' => $qty]);
        } else {
            CartItem::create([
                'cart_id'       => $cart->id,
                'product_id'    => $product->id,
                'product_name'  => $product->name ?? ('Produit #'.$product->id),
                'product_image' => $product->image ?? null,
                'unit_price'    => $product->price ?? 0,
                'qty'           => $qty,
                'options'       => $optionsSorted ?: null,
                'options_hash'  => $hash
            ]);
        }
    }

    private function sortOptions(array $options): array
    {
        if (empty($options)) return [];
        ksort($options);
        return array_map(fn($v) => is_scalar($v) ? (string) $v : $v, $options);
    }

    private function hashOptions(array $options): string
    {
        return md5(json_encode($this->sortOptions($options)));
    }

    private function authorizeItem(Request $request, WishlistItem $item): void
    {
        if ($item->wishlist->user_id !== $request->user()->id) {
            abort(403, 'Accès refusé');
        }
    }
}
