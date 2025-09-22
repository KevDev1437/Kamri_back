<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        $itemsCount = $this->whenLoaded('items', function () {
            return $this->items->sum('qty');
        }, 0);

        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'date' => $this->created_at?->toIso8601String(),
            'paidAt' => $this->paid_at?->toIso8601String(),
            'itemsCount' => (int) $itemsCount,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'shipping' => (float) $this->shipping_price,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'shippingAddress' => $this->shipping_address,
            'billingAddress' => $this->billing_address,
            'payment' => $this->when($this->payment, function () {
                return [
                    'id' => $this->payment->id,
                    'status' => $this->payment->status,
                    'amount' => (float) $this->payment->amount,
                ];
            }),
            'timeline' => $this->defaultTimeline(),
            'lines' => OrderLineResource::collection($this->whenLoaded('items')),
        ];
    }

    private function defaultTimeline(): array
    {
        // Simple timeline basée sur le statut
        $map = [
            'pending'   => ['created'],
            'paid'      => ['created', 'paid'],
            'processing'=> ['created', 'paid', 'processing'],
            'shipped'   => ['created', 'paid', 'shipped'],
            'delivered' => ['created', 'paid', 'shipped', 'delivered'],
            'canceled'  => ['created', 'canceled'],
            'failed'    => ['created', 'failed'],
        ];

        $statuses = $map[$this->status] ?? ['created'];

        return array_map(function($key) {
            $dates = [
                'created' => $this->created_at?->toIso8601String(),
                'paid' => $this->paid_at?->toIso8601String(),
                'processing' => $this->meta['processing_at'] ?? null,
                'shipped' => $this->meta['shipped_at'] ?? null,
                'delivered' => $this->meta['delivered_at'] ?? null,
                'canceled' => $this->meta['canceled_at'] ?? null,
                'failed' => $this->meta['failed_at'] ?? null,
            ];

            return [
                'key' => $key,
                'at' => $dates[$key] ?? null,
            ];
        }, $statuses);
    }
}
