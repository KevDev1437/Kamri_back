<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    public static function generate(): string
    {
        $year = now()->format('Y');

        // Récupérer le dernier numéro de l'année
        $lastOrder = Order::where('number', 'like', "CMD-{$year}-%")
            ->orderBy('number', 'desc')
            ->first();

        if ($lastOrder) {
            // Extraire le numéro de séquence
            $lastNumber = (int) substr($lastOrder->number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return "CMD-{$year}-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
