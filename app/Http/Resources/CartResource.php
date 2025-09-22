<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = CartItemResource::collection($this->items)->resolve();

        $subtotal = collect($items)->sum(fn($i) => $i['subtotal']);
        $vatRate  = (float) (config('cart.vat_rate', env('VAT_RATE', 0.21)));
        $tax      = round($subtotal * $vatRate, 2);
        $discount = 0.0; // coupons en B8
        $total    = round($subtotal + $tax - $discount, 2);

        return [
            'id'       => $this->id,
            'currency' => $this->currency,
            'items'    => $items,
            'totals'   => [
                'subtotal' => round($subtotal, 2),
                'tax'      => $tax,
                'discount' => $discount,
                'total'    => $total,
            ]
        ];
    }
}
