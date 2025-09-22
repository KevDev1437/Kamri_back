<?php

namespace App\Services;

class TotalsService
{
    public static function compute(float $subtotal, float $shipping, ?float $discount, float $vatRate = 0.21): array
    {
        $discount = max(0, (float) $discount);
        $afterDiscount = max(0, $subtotal - $discount);
        $tax = round($afterDiscount * $vatRate, 2);
        $total = round($afterDiscount + $tax + $shipping, 2);

        return [
            'subtotal' => round($subtotal,2),
            'discount' => $discount,
            'shipping' => round($shipping,2),
            'tax' => $tax,
            'total' => $total
        ];
    }
}
