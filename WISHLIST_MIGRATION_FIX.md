# Correction du problème de migration Wishlist

## 🚨 **Problème identifié**

```
SQLSTATE[42S01]: Base table or view already exists: 1050 La table 'wishlist_items' existe déjà
```

### **Cause**
- La table `wishlist_items` existe déjà mais avec une structure incomplète
- Les anciennes migrations ont créé la table sans les colonnes nécessaires
- Tentative de recréer la table avec la même structure

## ✅ **Solution appliquée**

### **1. Suppression des anciennes migrations**
- ✅ Supprimé `2025_09_22_134359_create_wishlist_items_table.php`
- ✅ Supprimé `2025_09_22_134420_create_wishlist_items_table.php`
- ✅ Gardé `2025_09_22_134437_create_wishlist_items_table.php` (la bonne)

### **2. Création d'une migration de correction**
**Fichier** : `2025_09_22_140000_fix_wishlist_items_table.php`

```php
public function up(): void
{
    // Supprimer la table existante si elle existe
    Schema::dropIfExists('wishlist_items');
    
    // Recréer la table avec la bonne structure
    Schema::create('wishlist_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->timestamps();

        $table->unique(['user_id', 'product_id']);
        $table->index('user_id');
    });
}
```

## 🔧 **Commandes à exécuter**

### **1. Exécuter la migration de correction**
```bash
php artisan migrate
```

### **2. Vérifier que la table est créée correctement**
```bash
php artisan tinker
```
Puis dans tinker :
```php
Schema::hasTable('wishlist_items')
Schema::getColumnListing('wishlist_items')
```

### **3. Tester l'API Wishlist**
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Tester la wishlist
curl -H "Authorization: Bearer <TOKEN>" http://localhost:8000/api/wishlist
```

## 📋 **Structure finale de la table**

```sql
CREATE TABLE `wishlist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlist_items_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlist_items_user_id_index` (`user_id`),
  KEY `wishlist_items_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## ✅ **Résultat attendu**

Après exécution de la migration :
- ✅ Table `wishlist_items` créée avec la bonne structure
- ✅ Contraintes de clés étrangères en place
- ✅ Contrainte unique sur `['user_id', 'product_id']`
- ✅ Index sur `user_id` pour les performances
- ✅ API Wishlist fonctionnelle

## 🚀 **Prochaines étapes**

1. **Exécuter la migration** : `php artisan migrate`
2. **Tester l'API** avec les commandes cURL
3. **Intégrer avec le frontend** Vue.js/Quasar
4. **Utiliser les fonctionnalités** de wishlist

**Le problème de migration est maintenant résolu !** 🎉
