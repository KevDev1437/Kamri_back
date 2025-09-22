# API Coupons & Promotions - Documentation complète

## ✅ **Implémentation terminée**

L'API Coupons & Promotions a été entièrement implémentée selon vos spécifications avec toutes les fonctionnalités demandées pour la gestion des codes promo.

## 🏗️ **Architecture implémentée**

### **1. Tables de base de données**
- ✅ **`coupons`** : Codes promo avec types, contraintes, ciblage
- ✅ **`coupon_product`** : Ciblage par produits spécifiques
- ✅ **`coupon_category`** : Ciblage par catégories
- ✅ **`coupon_redemptions`** : Suivi des utilisations

### **2. Types de coupons supportés**
- ✅ **`percentage`** : Remise en pourcentage (ex: 10%)
- ✅ **`fixed`** : Remise fixe en euros (ex: 20€)
- ✅ **`free_shipping`** : Livraison gratuite

### **3. Contraintes et limites**
- ✅ **Dates** : `starts_at` / `ends_at` pour la validité
- ✅ **Minimum panier** : `min_subtotal` requis
- ✅ **Limites globales** : `max_redemptions` total
- ✅ **Limites utilisateur** : `per_user_limit` par utilisateur
- ✅ **Activation** : `active` boolean

### **4. Ciblage avancé**
- ✅ **Tous les produits** : `applies_to = 'all'`
- ✅ **Produits spécifiques** : `applies_to = 'products'` + pivot
- ✅ **Catégories** : `applies_to = 'categories'` + pivot

### **5. API Endpoints**
- ✅ `POST /api/coupons/validate` : Validation publique des coupons
- ✅ **Intégration checkout** : Revalidation sécurisée dans `/api/checkout`

### **6. Sécurité**
- ✅ **Revalidation backend** : Anti-triche au moment du checkout
- ✅ **Tracking complet** : Enregistrement des redemptions
- ✅ **Limites respectées** : Vérification des contraintes

## 🔧 **Configuration requise**

### **1. Migration**
```bash
php artisan migrate
```

### **2. Seeders (optionnel)**
```bash
php artisan db:seed --class=CouponSeeder
```

## 🧪 **Tests de validation**

### **1. Validation d'un coupon (public)**
```bash
curl -X POST "http://localhost:8000/api/coupons/validate" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "WELCOME10",
    "items": [
      {"product_id": 1, "qty": 2, "price": 29.99},
      {"product_id": 5, "qty": 1, "price": 59.00}
    ],
    "shipping": 4.99
  }'
```

### **2. Validation avec utilisateur connecté**
```bash
curl -X POST "http://localhost:8000/api/coupons/validate" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SAVE20",
    "items": [
      {"product_id": 1, "qty": 1, "price": 60.00}
    ]
  }'
```

### **3. Test des différents types**
```bash
# Coupon pourcentage
curl -X POST "http://localhost:8000/api/coupons/validate" \
  -H "Content-Type: application/json" \
  -d '{"code": "WELCOME10", "items": [{"product_id": 1, "qty": 1, "price": 50.00}]}'

# Coupon fixe
curl -X POST "http://localhost:8000/api/coupons/validate" \
  -H "Content-Type: application/json" \
  -d '{"code": "SAVE20", "items": [{"product_id": 1, "qty": 1, "price": 60.00}]}'

# Livraison gratuite
curl -X POST "http://localhost:8000/api/coupons/validate" \
  -H "Content-Type: application/json" \
  -d '{"code": "FREESHIP", "items": [{"product_id": 1, "qty": 1, "price": 30.00}], "shipping": 4.99}'
```

## 📋 **Format des réponses**

### **1. Validation réussie (pourcentage)**
```json
{
  "success": true,
  "code": "WELCOME10",
  "type": "percentage",
  "value": 10,
  "eligibleSubtotal": 118.98,
  "discount": 11.90,
  "shippingDiscount": 0,
  "message": "Coupon appliqué"
}
```

### **2. Validation réussie (fixe)**
```json
{
  "success": true,
  "code": "SAVE20",
  "type": "fixed",
  "value": 20,
  "eligibleSubtotal": 60.00,
  "discount": 20.00,
  "shippingDiscount": 0,
  "message": "Coupon appliqué"
}
```

### **3. Validation réussie (livraison gratuite)**
```json
{
  "success": true,
  "code": "FREESHIP",
  "type": "free_shipping",
  "value": null,
  "eligibleSubtotal": 30.00,
  "discount": 0,
  "shippingDiscount": 4.99,
  "message": "Coupon appliqué"
}
```

### **4. Validation échouée**
```json
{
  "success": false,
  "message": "Code promo invalide"
}
```

### **5. Messages d'erreur spécifiques**
```json
// Coupon expiré
{"success": false, "message": "Ce code promo n'est plus valide"}

// Minimum non atteint
{"success": false, "message": "Minimum d'achat de 30.00€ requis"}

// Limite utilisateur atteinte
{"success": false, "message": "Vous avez déjà utilisé ce code promo le nombre maximum de fois"}

// Limite globale atteinte
{"success": false, "message": "Ce code promo a atteint sa limite d'utilisation"}

// Produit non éligible
{"success": false, "message": "Ce code promo ne s'applique pas aux articles de votre panier"}
```

## 🎯 **Compatibilité frontend**

L'API est parfaitement compatible avec le frontend Vue.js/Quasar existant :

### **Endpoints utilisés par le frontend :**
- ✅ `POST /api/coupons/validate` → **CheckoutSidebar.vue** (validation en temps réel)
- ✅ **Intégration checkout** → **CheckoutPage.vue** (revalidation sécurisée)

### **Format JSON compatible :**
- ✅ **camelCase** : Tous les champs en camelCase
- ✅ **Structure** : `success`, `code`, `type`, `value`, `eligibleSubtotal`, `discount`, `shippingDiscount`, `message`
- ✅ **Types** : `percentage`, `fixed`, `free_shipping`
- ✅ **Messages** : Messages d'erreur explicites en français

## 🔄 **Intégration avec Checkout (B7)**

### **Revalidation sécurisée :**
- ✅ **CouponService** : Revalidation côté backend au moment du checkout
- ✅ **Anti-triche** : Recalcul avec les données réelles du panier
- ✅ **Tracking** : Enregistrement automatique des redemptions

### **Flux complet :**
1. **Frontend** : Validation en temps réel dans CheckoutSidebar
2. **Checkout** : Revalidation sécurisée côté backend
3. **Commande** : Application de la remise et enregistrement
4. **Tracking** : Suivi des utilisations dans coupon_redemptions

## 📊 **Fonctionnalités avancées**

### **1. Ciblage intelligent**
- ✅ **Tous produits** : Application universelle
- ✅ **Produits spécifiques** : Ciblage précis via pivot
- ✅ **Catégories** : Ciblage par catégorie de produits

### **2. Contraintes flexibles**
- ✅ **Dates** : Activation et expiration configurables
- ✅ **Minimum** : Montant minimum de panier
- ✅ **Limites** : Globales et par utilisateur

### **3. Types de remise**
- ✅ **Pourcentage** : Calcul automatique sur le sous-total éligible
- ✅ **Fixe** : Montant fixe (plafonné au sous-total)
- ✅ **Livraison gratuite** : Remise sur les frais de port

### **4. Sécurité renforcée**
- ✅ **Revalidation** : Double vérification au checkout
- ✅ **Tracking** : Suivi complet des utilisations
- ✅ **Limites** : Respect des contraintes en temps réel

## 🛡️ **Sécurité implémentée**

### **1. Revalidation backend**
```php
// Dans CheckoutController
$couponResult = $couponService->validate($couponCode, $cartItems, $user);
if ($couponResult['success']) {
    $finalDiscount = $couponResult['discount'];
    $shippingDiscount = $couponResult['shippingDiscount'];
}
```

### **2. Tracking des redemptions**
```php
$couponService->recordRedemption(
    $coupon,
    $user,
    $order->id,
    $finalDiscount + $shippingDiscount
);
```

### **3. Vérification des limites**
- ✅ **Globales** : Comptage dans `coupon_redemptions`
- ✅ **Utilisateur** : Comptage filtré par `user_id`
- ✅ **Temps réel** : Vérification à chaque validation

## 🚀 **Fonctionnalités métier**

### **1. Gestion des coupons**
- ✅ **Types multiples** : Pourcentage, fixe, livraison gratuite
- ✅ **Ciblage** : Tous, produits, catégories
- ✅ **Contraintes** : Dates, minimums, limites

### **2. Calculs automatiques**
- ✅ **Sous-total éligible** : Calcul basé sur le ciblage
- ✅ **Remises** : Calcul selon le type de coupon
- ✅ **Livraison** : Gestion séparée pour free_shipping

### **3. Tracking complet**
- ✅ **Redemptions** : Enregistrement de chaque utilisation
- ✅ **Montants** : Suivi des montants réellement déduits
- ✅ **Utilisateurs** : Association avec les commandes

### **4. Intégration checkout**
- ✅ **Revalidation** : Sécurité au moment de la commande
- ✅ **Application** : Intégration dans les totaux
- ✅ **Enregistrement** : Tracking automatique

## 🎉 **Résumé**

L'API Coupons & Promotions est maintenant complètement implémentée et prête pour la production. Elle offre :

- ✅ **Types complets** : Pourcentage, fixe, livraison gratuite
- ✅ **Ciblage avancé** : Tous, produits, catégories
- ✅ **Contraintes flexibles** : Dates, minimums, limites
- ✅ **Sécurité renforcée** : Revalidation backend, anti-triche
- ✅ **Tracking complet** : Suivi des utilisations
- ✅ **Compatibilité frontend** : Format JSON camelCase
- ✅ **Tests complets** : Couverture de tous les cas d'usage

**L'implémentation est terminée et prête pour la production !** 🚀

## 🔗 **Routes finales**

```php
// Public
Route::post('/coupons/validate', [CouponsController::class, 'validate']);

// Intégration dans checkout (auth)
Route::post('/checkout', [CheckoutController::class, 'placeOrder']);
```

## 📝 **Exemples de coupons**

### **Coupons de test inclus :**
- ✅ **WELCOME10** : 10% sur tout, min 30€, 1 utilisation/user
- ✅ **SAVE20** : 20€ fixe sur High-tech, min 50€, 2 utilisations/user
- ✅ **FREESHIP** : Livraison gratuite, min 25€, 3 utilisations/user
- ✅ **EXPIRED** : Coupon expiré (pour les tests)
- ✅ **INACTIVE** : Coupon inactif (pour les tests)

## 🧪 **Tests de validation**

### **Cas de succès :**
- ✅ Validation coupon pourcentage
- ✅ Validation coupon fixe
- ✅ Validation livraison gratuite
- ✅ Ciblage par produit
- ✅ Ciblage par catégorie

### **Cas d'échec :**
- ✅ Code invalide
- ✅ Coupon inactif
- ✅ Coupon expiré
- ✅ Minimum non atteint
- ✅ Limite utilisateur atteinte
- ✅ Limite globale atteinte
- ✅ Produit non éligible

### **Intégration checkout :**
- ✅ Application coupon lors du checkout
- ✅ Enregistrement des redemptions
- ✅ Gestion livraison gratuite
- ✅ Ignorer coupon invalide
