<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function show(Request $request)
    {
        $cart = $this->getOrCreateCart($request->user()->id);
        $cart->load('items');
        return new CartResource($cart);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'qty'        => ['required','integer','min:1','max:99'],
            'options'    => ['nullable','array'],
        ]);

        $userId = $request->user()->id;
        $cart = $this->getOrCreateCart($userId);

        $product = Product::findOrFail($data['product_id']);

        // Stock
        if (isset($product->stock) && $product->stock < $data['qty']) {
            return response()->json(['message' => 'Stock insuffisant'], 422);
        }

        // options_hash trié
        $options = $data['options'] ?? [];
        $optionsSorted = $this->sortOptions($options);
        $optionsHash = md5(json_encode($optionsSorted));

        DB::beginTransaction();
        try {
            // Existe déjà ?
            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('options_hash', $optionsHash)
                ->first();

            if ($item) {
                $newQty = min(($item->qty + $data['qty']), 99);
                if (isset($product->stock) && $product->stock < $newQty) {
                    return response()->json(['message' => 'Stock insuffisant'], 422);
                }
                $item->update(['qty' => $newQty]);
            } else {
                CartItem::create([
                    'cart_id'       => $cart->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name ?? ('Produit #'.$product->id),
                    'product_image' => $product->image ?? null,
                    'unit_price'    => $product->price, // prix gelé
                    'qty'           => $data['qty'],
                    'options'       => $optionsSorted ?: null,
                    'options_hash'  => $optionsHash
                ]);
            }

            DB::commit();

            $cart->load('items');
            return new CartResource($cart);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Erreur addition panier'], 500);
        }
    }

    public function updateItem(Request $request, CartItem $item)
    {
        $data = $request->validate([
            'qty' => ['required','integer','min:1','max:99']
        ]);

        $this->authorizeItem($request, $item);

        $product = $item->product;
        if (isset($product->stock) && $product->stock < $data['qty']) {
            return response()->json(['message' => 'Stock insuffisant'], 422);
        }

        $item->update(['qty' => $data['qty']]);

        $item->cart->load('items');
        return new CartResource($item->cart);
    }

    public function removeItem(Request $request, CartItem $item)
    {
        $this->authorizeItem($request, $item);
        $cart = $item->cart;
        $item->delete();
        $cart->load('items');
        return new CartResource($cart);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request->user()->id);
        $cart->items()->delete();
        $cart->load('items');
        return new CartResource($cart);
    }

    public function merge(Request $request)
    {
        $data = $request->validate([
            'items' => ['required','array','max:200'],
            'items.*.product_id' => ['required','integer','exists:products,id'],
            'items.*.qty' => ['required','integer','min:1','max:99'],
            'items.*.options' => ['nullable','array'],
        ]);

        $cart = $this->getOrCreateCart($request->user()->id);

        DB::beginTransaction();
        try {
            foreach ($data['items'] as $row) {
                $product = Product::find($row['product_id']);
                if (!$product) { continue; }

                $optionsSorted = $this->sortOptions($row['options'] ?? []);
                $optionsHash = md5(json_encode($optionsSorted));

                $existing = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->where('options_hash', $optionsHash)
                    ->first();

                $qty = min((int) $row['qty'], 99);
                if (isset($product->stock) && $product->stock < $qty) {
                    $qty = (int) $product->stock;
                    if ($qty <= 0) continue;
                }

                if ($existing) {
                    $newQty = min($existing->qty + $qty, 99);
                    if (isset($product->stock) && $product->stock < $newQty) {
                        $newQty = (int) $product->stock;
                    }
                    $existing->update(['qty' => $newQty]);
                } else {
                    CartItem::create([
                        'cart_id'       => $cart->id,
                        'product_id'    => $product->id,
                        'product_name'  => $product->name ?? ('Produit #'.$product->id),
                        'product_image' => $product->image ?? null,
                        'unit_price'    => $product->price,
                        'qty'           => $qty,
                        'options'       => $optionsSorted ?: null,
                        'options_hash'  => $optionsHash
                    ]);
                }
            }

            DB::commit();
            $cart->load('items');
            return new CartResource($cart);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Erreur lors du merge panier'], 500);
        }
    }

    private function getOrCreateCart(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId], ['currency' => 'EUR']);
    }

    private function sortOptions(array $options): array
    {
        if (empty($options)) return [];
        ksort($options);
        // normaliser valeurs scalaires en string
        return array_map(fn($v) => is_scalar($v) ? (string) $v : $v, $options);
    }

    private function authorizeItem(Request $request, CartItem $item): void
    {
        if ($item->cart->user_id !== $request->user()->id) {
            abort(403, 'Accès refusé');
        }
    }
}
