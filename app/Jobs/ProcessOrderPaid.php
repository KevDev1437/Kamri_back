<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderPaid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (!$order || $order->status !== 'paid') {
            return;
        }

        // Incrémenter l'usage du coupon si présent
        if ($order->meta && isset($order->meta['coupon'])) {
            $coupon = \App\Models\Coupon::where('code', $order->meta['coupon'])->first();
            if ($coupon) {
                $coupon->increment('used_count');
            }
        }

        // TODO: Envoyer email de confirmation
        // Mail::to($order->user->email)->send(new OrderConfirmationMail($order));

        // TODO: Notifier les services externes (inventory, shipping, etc.)
    }
}
