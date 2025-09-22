# API Reviews - Documentation complète

## ✅ **Implémentation terminée**

L'API Reviews a été entièrement implémentée selon vos spécifications avec toutes les fonctionnalités demandées pour la gestion des avis produits.

## 🏗️ **Architecture implémentée**

### **1. Tables de base de données**
- ✅ **`reviews`** : Avis avec rating, commentaire, photos, statuts
- ✅ **`review_votes`** : Votes "utile" avec contrainte unique
- ✅ **`review_reports`** : Signalements avec contrainte unique
- ✅ **Relations** : Product hasMany Reviews, User hasMany Reviews

### **2. Services métier**
- ✅ **PurchaseVerificationService** : Vérification acheteur uniquement
- ✅ **ReviewSummaryService** : Cache des résumés (average, counts, withPhotosCount)
- ✅ **Invalidation cache** : Automatique à la création d'avis

### **3. API Endpoints complets**
- ✅ `GET /api/products/{product}/reviews` : Liste avec filtres et pagination (public)
- ✅ `POST /api/products/{product}/reviews` : Création d'avis (auth + acheteur uniquement)
- ✅ `POST /api/reviews/{review}/helpful` : Vote "utile" (auth, anti-double)
- ✅ `POST /api/reviews/{review}/report` : Signalement (auth, anti-double)

### **4. Filtres & Tri**
- ✅ **Filtres** : `rating` (1-5), `with_photos` (true/false)
- ✅ **Tri** : `recent` (défaut), `top` (helpful_count), `rating_desc`, `rating_asc`
- ✅ **Pagination** : `page`, `perPage` (défaut 10)

### **5. Upload de photos**
- ✅ **Validation** : Max 5 photos, 3MB chacune, formats JPG/PNG/WebP
- ✅ **Stockage** : `Storage::disk('public')` dans `reviews/{productId}/`
- ✅ **URLs** : Génération automatique d'URLs publiques

### **6. Sécurité & Validation**
- ✅ **Acheteur uniquement** : Vérification via orders/order_items
- ✅ **Anti-double vote** : Contrainte unique sur review_votes
- ✅ **Anti-double signalement** : Contrainte unique sur review_reports
- ✅ **Sanctum** : Protection des actions sensibles

## 🔧 **Configuration requise**

### **1. Migration**
```bash
php artisan migrate
```

### **2. Storage link**
```bash
php artisan storage:link
```

### **3. Configuration cache (optionnel)**
```bash
# Dans .env
CACHE_DRIVER=redis  # ou file
```

## 🧪 **Tests de validation**

### **1. Liste des avis (public)**
```bash
curl "http://localhost:8000/api/products/12/reviews"
```

### **2. Filtres et tri**
```bash
# Par note
curl "http://localhost:8000/api/products/12/reviews?rating=5"

# Avec photos
curl "http://localhost:8000/api/products/12/reviews?with_photos=true"

# Tri par popularité
curl "http://localhost:8000/api/products/12/reviews?sort=top"

# Tri par note décroissante
curl "http://localhost:8000/api/products/12/reviews?sort=rating_desc"

# Pagination
curl "http://localhost:8000/api/products/12/reviews?page=2&perPage=5"
```

### **3. Création d'avis (auth + acheteur)**
```bash
curl -X POST "http://localhost:8000/api/products/12/reviews" \
  -H "Authorization: Bearer <TOKEN>" \
  -F rating=5 \
  -F comment="Excellent produit, très satisfait de la qualité !" \
  -F anonymous=false \
  -F photos[]=@/path/photo1.jpg \
  -F photos[]=@/path/photo2.jpg
```

### **4. Vote "utile" (auth)**
```bash
curl -X POST "http://localhost:8000/api/reviews/55/helpful" \
  -H "Authorization: Bearer <TOKEN>"
```

### **5. Signalement (auth)**
```bash
curl -X POST "http://localhost:8000/api/reviews/55/report" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Contenu inapproprié"}'
```

## 📋 **Format des réponses**

### **1. Liste des avis**
```json
{
  "success": true,
  "items": [
    {
      "id": 1,
      "productId": 12,
      "rating": 5,
      "comment": "Excellent produit, très satisfait !",
      "createdAt": "2025-09-22T14:00:00.000Z",
      "verified": true,
      "photos": [
        "http://localhost:8000/storage/reviews/12/photo1.jpg",
        "http://localhost:8000/storage/reviews/12/photo2.jpg"
      ],
      "helpfulCount": 15,
      "reported": false,
      "helpfulVoted": false,
      "user": {
        "name": "Jean Dupont",
        "initials": "JD",
        "isAnonymous": false
      }
    }
  ],
  "total": 25,
  "average": 4.3,
  "counts": {
    "5": 15,
    "4": 7,
    "3": 2,
    "2": 1,
    "1": 0
  },
  "withPhotosCount": 8
}
```

### **2. Création d'avis**
```json
{
  "id": 26,
  "productId": 12,
  "rating": 5,
  "comment": "Excellent produit, très satisfait !",
  "createdAt": "2025-09-22T14:00:00.000Z",
  "verified": true,
  "photos": [
    "http://localhost:8000/storage/reviews/12/photo1.jpg"
  ],
  "helpfulCount": 0,
  "reported": false,
  "helpfulVoted": false,
  "user": {
    "name": "Jean Dupont",
    "initials": "JD",
    "isAnonymous": false
  }
}
```

### **3. Vote "utile"**
```json
{
  "success": true,
  "helpfulCount": 16
}
```

### **4. Vote déjà effectué**
```json
{
  "success": true,
  "alreadyVoted": true,
  "helpfulCount": 15
}
```

### **5. Signalement**
```json
{
  "success": true
}
```

### **6. Signalement déjà effectué**
```json
{
  "success": true,
  "alreadyReported": true
}
```

## 🎯 **Compatibilité frontend**

L'API est parfaitement compatible avec le frontend Vue.js/Quasar existant :

### **Endpoints utilisés par le frontend :**
- ✅ `GET /api/products/{id}/reviews` → **RatingsSummary.vue** et **ReviewList.vue**
- ✅ `POST /api/products/{id}/reviews` → **ReviewForm.vue** (création avec photos)
- ✅ `POST /api/reviews/{id}/helpful` → **ReviewItem.vue** (bouton "Utile")
- ✅ `POST /api/reviews/{id}/report` → **ReviewItem.vue** (bouton "Signaler")

### **Format JSON compatible :**
- ✅ **camelCase** : Tous les champs en camelCase
- ✅ **Résumé** : `average`, `counts`, `withPhotosCount` pour RatingsSummary
- ✅ **Liste** : `items`, `total` pour ReviewList avec pagination
- ✅ **Actions** : `helpfulVoted`, `reported` pour l'état des boutons
- ✅ **Photos** : URLs absolues pour l'affichage
- ✅ **Utilisateur** : `name`, `initials`, `isAnonymous` pour l'affichage

## 🔄 **Intégration avec Orders (B8)**

### **Vérification acheteur :**
- ✅ **PurchaseVerificationService** : Vérifie via `orders` + `order_items`
- ✅ **Statuts valides** : `paid`, `processing`, `shipped`, `delivered`
- ✅ **Performance** : Requête optimisée avec jointures

### **Flux complet :**
1. **Achat** : Commande créée avec statut `paid`
2. **Avis** : Utilisateur peut laisser un avis (vérifié automatiquement)
3. **Frontend** : Affichage "Acheteur vérifié" dans l'avis

## 📊 **Fonctionnalités avancées**

### **1. Cache intelligent**
- ✅ **ReviewSummaryService** : Cache 5 minutes pour average/counts
- ✅ **Invalidation** : Automatique à la création d'avis
- ✅ **Performance** : Évite les recalculs coûteux

### **2. Upload de photos**
- ✅ **Validation stricte** : Formats, taille, nombre limité
- ✅ **Stockage organisé** : `reviews/{productId}/photo.jpg`
- ✅ **URLs publiques** : Génération automatique via Storage

### **3. Anti-double actions**
- ✅ **Votes** : Contrainte unique sur `(review_id, user_id)`
- ✅ **Signalements** : Contrainte unique sur `(review_id, user_id)`
- ✅ **Gestion gracieuse** : Retour d'info sans erreur

### **4. Filtres avancés**
- ✅ **Par note** : Filtrage 1-5 étoiles
- ✅ **Avec photos** : Filtrage des avis avec images
- ✅ **Tri multiple** : Récent, populaire, par note

## 🛡️ **Sécurité implémentée**

### **1. Acheteur uniquement**
```php
public function userHasPurchasedProduct(User $user, int $productId): bool
{
    return DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.user_id', $user->id)
        ->where('order_items.product_id', $productId)
        ->whereIn('orders.status', ['paid', 'processing', 'shipped', 'delivered'])
        ->exists();
}
```

### **2. Validation stricte**
```php
'rating' => ['required', 'integer', 'min:1', 'max:5'],
'comment' => ['required', 'string', 'min:20', 'max:2000'],
'photos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
```

### **3. Protection des actions**
- ✅ **Sanctum** : Toutes les actions sensibles protégées
- ✅ **Vérification propriétaire** : Impossible de voter son propre avis
- ✅ **Anti-double** : Contraintes de base de données

## 🚀 **Fonctionnalités métier**

### **1. Système de notation**
- ✅ **Rating 1-5** : Étoiles avec validation
- ✅ **Résumé automatique** : Moyenne et répartition
- ✅ **Cache** : Performance optimisée

### **2. Gestion des photos**
- ✅ **Upload multiple** : Jusqu'à 5 photos par avis
- ✅ **Validation** : Formats et taille limités
- ✅ **Stockage** : Organisation par produit

### **3. Interactions sociales**
- ✅ **Vote "utile"** : Système de popularité
- ✅ **Signalement** : Modération communautaire
- ✅ **Anti-double** : Prévention des abus

### **4. Anonymat**
- ✅ **Option anonyme** : Affichage "Acheteur vérifié"
- ✅ **Vérification** : Badge "Acheteur vérifié" automatique
- ✅ **Flexibilité** : Choix de l'utilisateur

## 🎉 **Résumé**

L'API Reviews est maintenant complètement implémentée et prête pour la production. Elle offre :

- ✅ **Gestion complète** : Liste, création, votes, signalements
- ✅ **Sécurité renforcée** : Acheteur uniquement, anti-double
- ✅ **Filtres avancés** : Note, photos, tri, pagination
- ✅ **Upload photos** : Validation et stockage sécurisé
- ✅ **Cache intelligent** : Performance optimisée
- ✅ **Compatibilité frontend** : Format JSON camelCase
- ✅ **Tests complets** : Couverture de tous les cas d'usage

**L'implémentation est terminée et prête pour la production !** 🚀

## 🔗 **Routes finales**

```php
// Public
Route::get('/products/{product}/reviews', [ReviewsController::class, 'index']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products/{product}/reviews', [ReviewsController::class, 'store']);
    Route::post('/reviews/{review}/helpful', [ReviewsController::class, 'voteHelpful']);
    Route::post('/reviews/{review}/report', [ReviewsController::class, 'report']);
});
```

## 📝 **Messages d'erreur**

- **403** : "Seuls les acheteurs peuvent laisser un avis."
- **403** : "Impossible de voter son propre avis."
- **422** : Messages de validation détaillés
- **409** : Gestion gracieuse des doublons (dans les réponses JSON)
