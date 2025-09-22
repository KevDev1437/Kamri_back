<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'title' => $this->product_name,
            'variant' => $this->variant,
            'unitPrice' => (float) $this->unit_price,
            'qty' => (int) $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'image' => $this->image,
        ];
    }
}
