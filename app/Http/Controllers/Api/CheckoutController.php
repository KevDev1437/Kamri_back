<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingMethod;
use App\Services\OrderNumberGenerator;
use App\Services\TotalsService;
use App\Services\CouponService;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct() { $this->middleware('auth:sanctum'); }

    public function placeOrder(PlaceOrderRequest $req) {
        $user = $req->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id], ['currency' => 'EUR']);
        $items = $cart->items()->with('product')->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Panier vide'], 422);
        }

        $delivery = ShippingMethod::where('code', $req->input('deliveryMethod.code'))->firstOrFail();
        $country = strtoupper(data_get($req->input('shippingAddress'), 'country', 'BE'));

        if (!$delivery->isAvailableForCountry($country)) {
            return response()->json(['message' => 'Méthode de livraison indisponible'], 422);
        }

        // subtotal from cart
        $subtotal = 0.0;
        foreach ($items as $ci) $subtotal += (float) $ci->unit_price * (int) $ci->qty;

        // coupon
        $couponCode = $req->input('coupon');
        $discount = 0.0;
        $coupon = null;
        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper($couponCode))->first();
            if ($coupon && $coupon->isValidFor($subtotal)) {
                $discount = $coupon->computeDiscount($subtotal);
            }
        }

        $shippingPrice = (float) $delivery->price;
        $vatRate = ($country === 'FR') ? 0.20 : 0.21;

        // Revalidation du coupon si fourni (sécurité)
        $finalDiscount = $discount;
        $shippingDiscount = 0;
        if ($couponCode = $req->input('couponCode')) {
            $couponService = app(CouponService::class);
            $cartItems = $cart->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->unit_price,
            ])->toArray();
            $cartItems['shipping'] = $shippingPrice;

            $couponResult = $couponService->validate($couponCode, $cartItems, $user);

            if ($couponResult['success']) {
                $finalDiscount = $couponResult['discount'];
                $shippingDiscount = $couponResult['shippingDiscount'];
            }
        }

        $totals = TotalsService::compute($subtotal, $shippingPrice, $finalDiscount, $vatRate);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'number' => \App\Services\OrderNumberService::generate(),
                'status' => $req->input('paymentIntentId') ? 'paid' : 'pending',
                'currency' => 'EUR',
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'shipping_price' => $totals['shipping'] - $shippingDiscount,
                'tax' => $totals['tax'],
                'total' => $totals['total'],
                'delivery_method' => [
                    'code' => $delivery->code, 'label' => $delivery->label,
                    'eta' => $delivery->eta, 'price' => (float) $delivery->price
                ],
                'shipping_address' => $req->input('shippingAddress'),
                'billing_address' => $req->input('billingAddress'),
                'payment_intent_id' => $req->input('paymentIntentId'),
                'meta' => ['coupon' => $coupon?->code],
            ]);

            foreach ($items as $ci) {
                // Stock
                if (isset($ci->product->stock)) {
                    if ($ci->product->stock < $ci->qty) {
                        throw new \RuntimeException('Stock insuffisant pour: '.$ci->product->name);
                    }
                    $ci->product->decrement('stock', $ci->qty);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $ci->product_id,
                    'product_name' => $ci->product_name,
                    'product_image' => $ci->product_image,
                    'unit_price' => $ci->unit_price,
                    'qty' => $ci->qty,
                    'subtotal' => round($ci->unit_price * $ci->qty, 2),
                    'options' => $ci->options,
                    'options_hash' => $ci->options_hash,
                ]);
            }

            // Si paymentIntentId fourni, on lie l'order au payment et on vérifie Stripe
            if ($req->filled('paymentIntentId')) {
                $stripe = \App\Services\StripeService::make();
                $pi = $stripe->retrieveIntent($req->input('paymentIntentId'));

                $payment = \App\Models\Payment::firstOrCreate(
                    ['intent_id' => $pi->id],
                    [
                        'user_id' => $user->id,
                        'amount' => round($totals['total'], 2),
                        'currency' => 'EUR',
                        'status' => $pi->status,
                    ]
                );
                $payment->order_id = $order->id;
                $payment->save();

                if (in_array($pi->status, ['succeeded','processing','requires_capture'], true)) {
                    $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_id' => $payment->id]);
                    dispatch(new \App\Jobs\ProcessOrderPaid($order->id));
                }
            }

            // Enregistrer la redemption du coupon si applicable
            if ($couponCode && isset($couponResult) && $couponResult['success']) {
                $coupon = \App\Models\Coupon::where('code', strtoupper($couponCode))->first();
                if ($coupon) {
                    $couponService->recordRedemption(
                        $coupon,
                        $user,
                        $order->id,
                        $finalDiscount + $shippingDiscount
                    );
                }
            }

            // Vide panier
            $cart->items()->delete();

            DB::commit();
            return new OrderResource($order->load('items'));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Erreur lors de la création de la commande'], 500);
        }
    }
}
