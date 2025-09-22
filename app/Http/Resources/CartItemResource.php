<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $subtotal = (float) $this->unit_price * (int) $this->qty;

        return [
            'id' => $this->id,
            'product' => [
                'id'    => $this->product_id,
                'name'  => $this->product_name,
                'image' => $this->product_image,
            ],
            'options'    => $this->options ?? [],
            'unitPrice'  => (float) $this->unit_price,
            'qty'        => (int) $this->qty,
            'subtotal'   => round($subtotal, 2)
        ];
    }
}
