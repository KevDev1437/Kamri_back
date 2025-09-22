<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function validate(Request $request, CouponService $couponService)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user(); // Sanctum si connecté
        $result = $couponService->validate(
            $validated['code'],
            $validated['items'],
            $user
        );

        // Ajouter le shipping au contexte pour free_shipping
        if (isset($validated['shipping'])) {
            $validated['items']['shipping'] = $validated['shipping'];
        }

        return response()->json($result);
    }
}
