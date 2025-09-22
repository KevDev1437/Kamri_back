<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseVerificationService
{
    public function userHasPurchasedProduct(User $user, int $productId): bool
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->where('order_items.product_id', $productId)
            ->whereIn('orders.status', ['paid', 'processing', 'shipped', 'delivered'])
            ->exists();
    }
}
