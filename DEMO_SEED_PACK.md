# 🌱 DEV DATA SEED PACK - KAMRI Marketplace

## ✅ **Pack de données de démo complet**

Ce pack génère automatiquement des données de démo pour l'environnement de développement local, permettant de simuler des achats/ventes sur KAMRI Marketplace.

## 🛡️ **Sécurité**

- ✅ **Jamais en production** : Exécution uniquement en `local` ou avec `SEED_DEMO=true`
- ✅ **Vérifications multiples** : Environnement + variable d'env + option `--force`
- ✅ **Transactions DB** : Rollback automatique en cas d'erreur

## 📊 **Données générées**

### **1. Catégories (8)**
- Mode & Accessoires
- Maison & Jardin
- High-tech & Électronique
- Sport & Fitness
- Beauté & Santé
- Jouets & Enfants
- Auto & Moto
- Livres & Médias

### **2. Produits (200)**
- Images Picsum automatiques
- Prix réalistes (5€ - 300€)
- Promotions (30% des produits)
- Stock variable (0-200)
- Descriptions complètes
- SKU uniques

### **3. Utilisateurs (30 + 1 admin)**
- **Admin** : `admin@kamri.test` / `password`
- Utilisateurs avec emails vérifiés
- Noms et profils réalistes

### **4. Adresses (1-2 par utilisateur)**
- Adresses belges réalistes
- Une adresse par défaut pour la livraison
- Labels variés (Maison, Bureau, etc.)

### **5. Commandes (20-60)**
- Statuts variés (pending, paid, shipped, delivered)
- Lignes de commande avec produits
- Totaux calculés (sous-total + TVA + livraison)
- Adresses de livraison et facturation

### **6. Avis (300)**
- Notes 1-5 étoiles
- Commentaires réalistes
- Photos optionnelles (20% des avis)
- Statut "vérifié" (80% des avis)

### **7. Coupons (3)**
- **WELCOME10** : 10% sur tout, min 30€
- **SAVE20** : 20€ fixe sur High-tech, min 50€
- **FREESHIP** : Livraison gratuite, min 25€

## 🚀 **Utilisation**

### **1. Génération complète**
```bash
# Migration + seed complet
php artisan migrate:fresh --seed

# Ou directement le seeder
php artisan demo:seed
```

### **2. Options personnalisées**
```bash
# Plus de produits
php artisan demo:seed --products=500

# Plus d'utilisateurs
php artisan demo:seed --users=80

# Plus de commandes
php artisan demo:seed --orders=120

# Avec avis
php artisan demo:seed --with-reviews

# Combinaison
php artisan demo:seed --products=300 --users=50 --orders=100 --with-reviews
```

### **3. Forcer l'exécution (dangereux)**
```bash
# ⚠️ Seulement pour les tests
php artisan demo:seed --force
```

## 🔍 **Vérification**

### **1. Commande Tinker**
```bash
php artisan tinker

>>> \App\Models\Product::count()
>>> \App\Models\Category::count()
>>> \App\Models\User::count()
>>> \App\Models\Order::count()
>>> \App\Models\Review::count()
```

### **2. Interface web**
- **Produits** : `/products` - Liste avec filtres
- **Détail** : `/product/{id}` - Fiche produit complète
- **Commandes** : `/account/orders` - Historique des commandes
- **Admin** : `admin@kamri.test` / `password`

## 🏗️ **Architecture**

### **Factories créées**
- ✅ `CategoryFactory` - Catégories avec images
- ✅ `ProductFactory` - Produits avec images Picsum
- ✅ `AddressFactory` - Adresses belges réalistes
- ✅ `OrderFactory` - Commandes avec statuts
- ✅ `OrderItemFactory` - Lignes de commande
- ✅ `ReviewFactory` - Avis avec photos

### **Modèles mis à jour**
- ✅ Relations ajoutées si manquantes
- ✅ Scopes pour les requêtes
- ✅ Casts pour les données JSON

### **Seeders**
- ✅ `DemoSeeder` - Seeder principal
- ✅ `DatabaseSeeder` - Intégration conditionnelle
- ✅ `CouponSeeder` - Coupons de test

## 🎯 **Compatibilité frontend**

### **Pages testées**
- ✅ **HomePage** : Produits en vedette, catégories
- ✅ **ProductsPage** : Liste avec filtres et tri
- ✅ **ProductPage** : Détail avec avis et images
- ✅ **OrdersPage** : Historique des commandes
- ✅ **CheckoutPage** : Processus de commande
- ✅ **WishlistPage** : Liste de souhaits

### **Fonctionnalités**
- ✅ **Recherche** : Produits avec noms réalistes
- ✅ **Filtres** : Par catégorie, prix, note
- ✅ **Pagination** : Listes avec beaucoup de données
- ✅ **Images** : Picsum pour tous les produits
- ✅ **Avis** : Système complet avec photos

## 🔧 **Configuration**

### **1. Variables d'environnement**
```env
# .env
APP_ENV=local
SEED_DEMO=true  # Optionnel, pour forcer l'exécution
```

### **2. Base de données**
- ✅ **SQLite** : Support complet
- ✅ **MySQL** : Support complet
- ✅ **PostgreSQL** : Support complet

### **3. Images**
- ✅ **Picsum** : Images automatiques et uniques
- ✅ **Seeds** : Basés sur les noms des produits
- ✅ **Formats** : 600x400 pour les produits, 400x300 pour les catégories

## 📝 **Exemples de données**

### **Produit généré**
```json
{
  "name": "Smartphone Samsung Galaxy",
  "price": 299.99,
  "sale_price": 249.99,
  "stock_quantity": 45,
  "image": "https://picsum.photos/seed/smartphone-samsung-galaxy-1234/600/400",
  "category": "High-tech & Électronique",
  "rating": 4.5,
  "reviews_count": 127
}
```

### **Commande générée**
```json
{
  "number": "CMD-2025-123456",
  "status": "delivered",
  "subtotal": 89.97,
  "shipping_price": 4.99,
  "tax": 19.94,
  "total": 114.90,
  "items": [
    {
      "product_name": "T-shirt Nike",
      "qty": 2,
      "unit_price": 29.99,
      "subtotal": 59.98
    }
  ]
}
```

### **Avis généré**
```json
{
  "rating": 5,
  "comment": "Excellent produit, très satisfait de la qualité et de la livraison rapide.",
  "verified": true,
  "photos": [
    "https://picsum.photos/seed/review-123/400/300"
  ],
  "helpful_count": 12
}
```

## 🎉 **Résultat**

Après exécution, vous disposez d'un marketplace complet avec :
- ✅ **200 produits** avec images et descriptions
- ✅ **8 catégories** organisées
- ✅ **31 utilisateurs** (30 + admin)
- ✅ **60+ commandes** avec historique
- ✅ **300 avis** avec photos
- ✅ **3 coupons** fonctionnels
- ✅ **Interface complète** testable

**Votre environnement de développement est prêt !** 🚀

## 🔗 **Liens utiles**

- **Admin** : `admin@kamri.test` / `password`
- **Codes promo** : `WELCOME10`, `SAVE20`, `FREESHIP`
- **API** : Tous les endpoints testables
- **Frontend** : Toutes les pages fonctionnelles
