<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product_name,
                'image' => $this->product_image,
            ],
            'qty' => (int) $this->qty,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'options' => $this->options ?? [],
        ];
    }
}
