<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $req) {
        $sig = $req->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($req->getContent(), $sig, $secret);
        } catch (\Throwable $e) {
            Log::warning('[Stripe] Webhook signature invalid', ['error' => $e->getMessage()]);
            return response()->json(['received' => true], 400);
        }

        $type = $event['type'] ?? null;
        $data = $event['data']['object'] ?? null;

        if (!$type || !$data) return response()->json(['received' => true]);

        try {
            if ($type === 'payment_intent.succeeded') {
                $intentId = $data['id'];
                $payment = Payment::where('intent_id', $intentId)->first();
                if ($payment) {
                    // idempotence : marquage déjà fait ?
                    if (($payment->meta['processed'] ?? false) === true) {
                        return response()->json(['received' => true]);
                    }

                    $payment->mark('succeeded', ['meta' => array_merge($payment->meta ?? [], ['processed' => true])]);

                    // Lier la commande si déjà créée (B6 – placeOrder envoie paymentIntentId)
                    if ($payment->order_id) {
                        $order = Order::find($payment->order_id);
                        if ($order && $order->status !== 'paid') {
                            $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_id' => $payment->id]);
                            dispatch(new \App\Jobs\ProcessOrderPaid($order->id));
                        }
                    }
                }
            }
            else if ($type === 'payment_intent.payment_failed') {
                $intentId = $data['id'];
                $payment = Payment::where('intent_id', $intentId)->first();
                if ($payment) {
                    $payment->mark('failed', ['last_error' => $data['last_payment_error'] ?? null]);
                    if ($payment->order_id) {
                        Order::where('id', $payment->order_id)->update(['status' => 'failed']);
                    }
                }
            }
            else if ($type === 'charge.refunded') {
                $charge = $data;
                $intentId = $charge['payment_intent'] ?? null;
                if ($intentId) {
                    $payment = Payment::where('intent_id', $intentId)->first();
                    if ($payment) {
                        $payment->mark('refunded', ['meta' => array_merge($payment->meta ?? [], ['refund' => $charge])]);
                        if ($payment->order_id) {
                            Order::where('id', $payment->order_id)->update(['status' => 'canceled']);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('[Stripe] Webhook handling error', ['type' => $type, 'error' => $e->getMessage()]);
        }

        return response()->json(['received' => true]);
    }
}
