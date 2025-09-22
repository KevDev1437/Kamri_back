<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Coupon pourcentage - tous les produits
        Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10.00,
            'active' => true,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->addDays(30),
            'min_subtotal' => 30.00,
            'max_redemptions' => 1000,
            'per_user_limit' => 1,
            'applies_to' => 'all',
            'notes' => 'Coupon de bienvenue 10%',
        ]);

        // Coupon fixe - catégorie High-tech
        $highTechCategory = Category::where('name', 'like', '%tech%')->first();
        if ($highTechCategory) {
            $coupon = Coupon::create([
                'code' => 'SAVE20',
                'type' => 'fixed',
                'value' => 20.00,
                'active' => true,
                'starts_at' => now()->subDays(15),
                'ends_at' => now()->addDays(15),
                'min_subtotal' => 50.00,
                'max_redemptions' => 500,
                'per_user_limit' => 2,
                'applies_to' => 'categories',
                'notes' => '20€ de réduction sur la High-tech',
            ]);

            $coupon->categories()->attach($highTechCategory->id);
        }

        // Coupon livraison gratuite
        Coupon::create([
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'value' => null,
            'active' => true,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(7),
            'min_subtotal' => 25.00,
            'max_redemptions' => 2000,
            'per_user_limit' => 3,
            'applies_to' => 'all',
            'notes' => 'Livraison gratuite',
        ]);

        // Coupon expiré (pour les tests)
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percentage',
            'value' => 15.00,
            'active' => true,
            'starts_at' => now()->subDays(30),
            'ends_at' => now()->subDays(1),
            'min_subtotal' => 20.00,
            'max_redemptions' => 100,
            'per_user_limit' => 1,
            'applies_to' => 'all',
            'notes' => 'Coupon expiré pour les tests',
        ]);

        // Coupon inactif (pour les tests)
        Coupon::create([
            'code' => 'INACTIVE',
            'type' => 'fixed',
            'value' => 5.00,
            'active' => false,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(10),
            'min_subtotal' => 15.00,
            'max_redemptions' => 50,
            'per_user_limit' => 1,
            'applies_to' => 'all',
            'notes' => 'Coupon inactif pour les tests',
        ]);
    }
}
