<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'product' => [
                'id'    => $this->product->id,
                'name'  => $this->product->name ?? ('Produit #'.$this->product->id),
                'image' => $this->product->image ?? null,
                'price' => (float) ($this->product->price ?? 0),
                'inStock' => isset($this->product->stock) ? (int)$this->product->stock > 0 : true
            ],
            'options' => $this->options ?? [],
            'addedAt' => $this->created_at?->toISOString(),
        ];
    }
}
