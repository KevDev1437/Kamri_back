<?php

/**
 * Script de vérification des données de démo
 * Usage: php scripts/check-demo-data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Vérification des données de démo KAMRI Marketplace\n";
echo "====================================================\n\n";

try {
    // Vérifier les catégories
    $categoriesCount = \App\Models\Category::count();
    echo "📁 Catégories : $categoriesCount\n";

    if ($categoriesCount > 0) {
        $hotCategories = \App\Models\Category::where('is_hot', true)->count();
        echo "   • Catégories populaires : $hotCategories\n";
    }

    // Vérifier les produits
    $productsCount = \App\Models\Product::count();
    echo "🛍️ Produits : $productsCount\n";

    if ($productsCount > 0) {
        $featuredProducts = \App\Models\Product::where('is_featured', true)->count();
        $inStockProducts = \App\Models\Product::where('stock_quantity', '>', 0)->count();
        $onSaleProducts = \App\Models\Product::whereNotNull('sale_price')->count();

        echo "   • Produits en vedette : $featuredProducts\n";
        echo "   • Produits en stock : $inStockProducts\n";
        echo "   • Produits en promotion : $onSaleProducts\n";
    }

    // Vérifier les utilisateurs
    $usersCount = \App\Models\User::count();
    echo "👥 Utilisateurs : $usersCount\n";

    if ($usersCount > 0) {
        $adminUser = \App\Models\User::where('email', 'admin@kamri.test')->first();
        echo "   • Admin : " . ($adminUser ? "✅ Créé" : "❌ Manquant") . "\n";

        $verifiedUsers = \App\Models\User::whereNotNull('email_verified_at')->count();
        echo "   • Emails vérifiés : $verifiedUsers\n";
    }

    // Vérifier les adresses
    if (class_exists(\App\Models\Address::class)) {
        $addressesCount = \App\Models\Address::count();
        echo "🏠 Adresses : $addressesCount\n";

        if ($addressesCount > 0) {
            $defaultShipping = \App\Models\Address::where('is_default_shipping', true)->count();
            echo "   • Adresses par défaut livraison : $defaultShipping\n";
        }
    }

    // Vérifier les commandes
    if (class_exists(\App\Models\Order::class)) {
        $ordersCount = \App\Models\Order::count();
        echo "📦 Commandes : $ordersCount\n";

        if ($ordersCount > 0) {
            $paidOrders = \App\Models\Order::where('status', 'paid')->count();
            $deliveredOrders = \App\Models\Order::where('status', 'delivered')->count();
            $totalRevenue = \App\Models\Order::where('status', 'paid')->sum('total');

            echo "   • Commandes payées : $paidOrders\n";
            echo "   • Commandes livrées : $deliveredOrders\n";
            echo "   • Chiffre d'affaires : " . number_format($totalRevenue, 2) . "€\n";
        }
    }

    // Vérifier les lignes de commande
    if (class_exists(\App\Models\OrderItem::class)) {
        $orderItemsCount = \App\Models\OrderItem::count();
        echo "📋 Lignes de commande : $orderItemsCount\n";
    }

    // Vérifier les avis
    if (class_exists(\App\Models\Review::class)) {
        $reviewsCount = \App\Models\Review::count();
        echo "⭐ Avis : $reviewsCount\n";

        if ($reviewsCount > 0) {
            $verifiedReviews = \App\Models\Review::where('verified', true)->count();
            $reviewsWithPhotos = \App\Models\Review::whereNotNull('photos')->count();
            $avgRating = \App\Models\Review::avg('rating');

            echo "   • Avis vérifiés : $verifiedReviews\n";
            echo "   • Avis avec photos : $reviewsWithPhotos\n";
            echo "   • Note moyenne : " . number_format($avgRating, 1) . "/5\n";
        }
    }

    // Vérifier les coupons
    if (class_exists(\App\Models\Coupon::class)) {
        $couponsCount = \App\Models\Coupon::count();
        echo "🎫 Coupons : $couponsCount\n";

        if ($couponsCount > 0) {
            $activeCoupons = \App\Models\Coupon::where('active', true)->count();
            echo "   • Coupons actifs : $activeCoupons\n";

            $demoCoupons = ['WELCOME10', 'SAVE20', 'FREESHIP'];
            foreach ($demoCoupons as $code) {
                $coupon = \App\Models\Coupon::where('code', $code)->first();
                echo "   • $code : " . ($coupon ? "✅" : "❌") . "\n";
            }
        }
    }

    echo "\n✅ Vérification terminée !\n";
    echo "\n🔑 Compte admin : admin@kamri.test / password\n";
    echo "🎫 Codes promo : WELCOME10, SAVE20, FREESHIP\n";
    echo "🌐 Frontend : http://localhost:9000\n";
    echo "🔧 API : http://localhost:8000/api\n";

} catch (\Exception $e) {
    echo "\n❌ Erreur lors de la vérification :\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
