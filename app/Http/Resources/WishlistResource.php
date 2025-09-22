<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request): array
    {
        $items = WishlistItemResource::collection($this->items()->with('product')->latest()->get())->resolve();

        return [
            'id'    => $this->id,
            'count' => count($items),
            'items' => $items
        ];
    }
}
