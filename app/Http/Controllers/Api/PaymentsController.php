<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function __construct() { $this->middleware('auth:sanctum'); }

    public function createIntent(Request $req) {
        $validated = $req->validate([
            'amount' => ['required','integer','min:50'], // 0.50€
            'currency' => ['nullable','string','size:3'],
            'metadata' => ['nullable','array'],
        ]);
        $user = $req->user();
        $currency = strtoupper($validated['currency'] ?? config('stripe.currency', 'EUR'));
        $metadata = $validated['metadata'] ?? [];
        $idempotency = $req->header('X-Idempotency-Key');

        $stripe = \App\Services\StripeService::make();
        $res = $stripe->createIntent($validated['amount'], $currency, $metadata, $idempotency);
        $payment = $stripe->findOrCreatePaymentRecord($user->id, $res['id'], $validated['amount'], $currency);
        $payment->mark($res['status']);

        return response()->json([
            'success' => true,
            'intent_id' => $res['id'],
            'client_secret' => $res['client_secret'],
            'status' => $res['status'],
        ]);
    }
}
