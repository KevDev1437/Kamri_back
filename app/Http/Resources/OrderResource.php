<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'currency' => $this->currency,
            'totals' => [
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) $this->discount,
                'shipping' => (float) $this->shipping_price,
                'tax' => (float) $this->tax,
                'total' => (float) $this->total,
            ],
            'delivery_method' => $this->delivery_method,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'items' => OrderItemResource::collection($this->items)->resolve(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
