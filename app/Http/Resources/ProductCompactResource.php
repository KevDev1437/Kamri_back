<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCompactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'price'        => (float) $this->price,
            'oldPrice'     => $this->sale_price ? (float) $this->sale_price : null,
            'image'        => $this->image ?? null,
            'rating'       => (float) ($this->rating ?? 0),
            'reviewsCount' => (int) ($this->reviews_count ?? 0),
            'inStock'      => (bool) ($this->stock_quantity > 0),
            'ecoScore'     => (int) ($this->eco_score ?? 0),
            'promo'        => (bool) ($this->is_on_sale ?? false),
        ];
    }
}
