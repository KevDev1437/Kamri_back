<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percentage',
                'value' => 10,
                'min_subtotal' => 50,
                'active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6)
            ]
        );
    }
}
