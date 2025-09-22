<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Models\Coupon;

class CouponsController extends Controller
{
    public function __construct() { $this->middleware('auth:sanctum'); }

    public function validateCode(ValidateCouponRequest $req) {
        $code = strtoupper($req->input('code'));
        $subtotal = (float) $req->input('subtotal');
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValidFor($subtotal)) {
            return response()->json(['success' => false, 'message' => 'Coupon invalide'], 422);
        }

        $discount = $coupon->computeDiscount($subtotal);
        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->code, 'type' => $coupon->type, 'value' => (float) $coupon->value,
                'discount' => (float) $discount
            ]
        ]);
    }
}
