<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function validate(string $code, array $cartItems, ?User $user = null): array
    {
        // Normaliser le code (uppercase)
        $code = strtoupper(trim($code));

        // Trouver le coupon
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Code promo invalide'
            ];
        }

        // Vérifier si le coupon est actif
        if (!$coupon->isActive()) {
            return [
                'success' => false,
                'message' => 'Ce code promo n\'est plus valide'
            ];
        }

        // Vérifier les limites globales
        if ($coupon->max_redemptions) {
            $remaining = $coupon->getRemainingRedemptions();
            if ($remaining <= 0) {
                return [
                    'success' => false,
                    'message' => 'Ce code promo a atteint sa limite d\'utilisation'
                ];
            }
        }

        // Vérifier les limites par utilisateur
        if ($user && $coupon->per_user_limit) {
            $userRedemptions = $coupon->getUserRedemptionsCount($user);
            if ($userRedemptions >= $coupon->per_user_limit) {
                return [
                    'success' => false,
                    'message' => 'Vous avez déjà utilisé ce code promo le nombre maximum de fois'
                ];
            }
        }

        // Calculer le sous-total éligible
        $eligibleSubtotal = $this->calculateEligibleSubtotal($coupon, $cartItems);

        if ($eligibleSubtotal <= 0) {
            return [
                'success' => false,
                'message' => 'Ce code promo ne s\'applique pas aux articles de votre panier'
            ];
        }

        // Vérifier le minimum de sous-total
        if ($coupon->min_subtotal && $eligibleSubtotal < $coupon->min_subtotal) {
            return [
                'success' => false,
                'message' => "Minimum d'achat de " . number_format($coupon->min_subtotal, 2) . "€ requis"
            ];
        }

        // Calculer la remise
        $discount = $this->calculateDiscount($coupon, $eligibleSubtotal);
        $shippingDiscount = 0;

        if ($coupon->type === 'free_shipping') {
            // Pour free_shipping, on retourne le montant de la livraison
            $shippingDiscount = $cartItems['shipping'] ?? 0;
        }

        return [
            'success' => true,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'eligibleSubtotal' => round($eligibleSubtotal, 2),
            'discount' => round($discount, 2),
            'shippingDiscount' => round($shippingDiscount, 2),
            'message' => 'Coupon appliqué'
        ];
    }

    private function calculateEligibleSubtotal(Coupon $coupon, array $cartItems): float
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            if ($this->isItemEligible($coupon, $item)) {
                $subtotal += $item['price'] * $item['qty'];
            }
        }

        return $subtotal;
    }

    private function isItemEligible(Coupon $coupon, array $item): bool
    {
        if ($coupon->applies_to === 'all') {
            return true;
        }

        if ($coupon->applies_to === 'products') {
            return $coupon->products()->where('product_id', $item['product_id'])->exists();
        }

        if ($coupon->applies_to === 'categories') {
            $product = \App\Models\Product::find($item['product_id']);
            if (!$product) {
                return false;
            }

            return $coupon->categories()->where('category_id', $product->category_id)->exists();
        }

        return false;
    }

    private function calculateDiscount(Coupon $coupon, float $eligibleSubtotal): float
    {
        switch ($coupon->type) {
            case 'percentage':
                return $eligibleSubtotal * ($coupon->value / 100);

            case 'fixed':
                return min($coupon->value, $eligibleSubtotal);

            case 'free_shipping':
                return 0; // La remise shipping est gérée séparément

            default:
                return 0;
        }
    }

    public function recordRedemption(Coupon $coupon, ?User $user, ?int $orderId, float $amount): void
    {
        DB::table('coupon_redemptions')->insert([
            'coupon_id' => $coupon->id,
            'user_id' => $user?->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
