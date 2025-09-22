# API Orders - Documentation complète

## ✅ **Implémentation terminée**

L'API Orders a été entièrement implémentée selon vos spécifications avec toutes les fonctionnalités demandées pour la gestion des commandes.

## 🏗️ **Architecture implémentée**

### **1. Service OrderNumberService**
- ✅ **Génération unique** : Format `CMD-YYYY-000123` avec séquence quotidienne
- ✅ **Assurance unicité** : Vérification des numéros existants
- ✅ **Intégration** : Utilisé dans CheckoutController

### **2. Resources JSON**
- ✅ **OrderResource** : Format camelCase avec timeline, payment, addresses
- ✅ **OrderLineResource** : Détails des lignes de commande
- ✅ **Compatibilité frontend** : Structure conforme aux besoins du front

### **3. API Endpoints complets**
- ✅ `GET /api/orders` : Liste avec filtres et pagination
- ✅ `GET /api/orders/{id}` : Détail d'une commande
- ✅ `GET /api/orders/{id}/invoice` : Téléchargement facture PDF
- ✅ `POST /api/orders/{id}/reorder` : Re-commande

### **4. Filtres & Pagination**
- ✅ **Filtres supportés** : `q` (number), `status`, `date_from`, `date_to`
- ✅ **Pagination** : `page`, `perPage` (défaut 10)
- ✅ **Tri** : Par date de création décroissante

### **5. Facture PDF**
- ✅ **Service InvoiceService** : Génération PDF avec DomPDF
- ✅ **Template Blade** : Facture professionnelle avec adresses, articles, totaux
- ✅ **Téléchargement** : Content-Type PDF, nom de fichier personnalisé

### **6. Re-commande**
- ✅ **Structure cart-like** : Retourne items prêts pour le panier
- ✅ **Compatibilité frontend** : Format compatible avec Pinia cart
- ✅ **Sécurité** : Policy propriétaire

### **7. Sécurité**
- ✅ **Policy OrderPolicy** : Seuls les propriétaires peuvent accéder
- ✅ **Sanctum** : Toutes les routes protégées
- ✅ **Autorisation** : Vérification user_id sur chaque action

## 🔧 **Configuration requise**

### **1. Installation dépendance PDF**
```bash
composer require barryvdh/laravel-dompdf
```

### **2. Migration**
```bash
php artisan migrate
```

### **3. Configuration DomPDF (optionnel)**
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## 🧪 **Tests de validation**

### **1. Liste des commandes**
```bash
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders"
```

### **2. Filtres**
```bash
# Par statut
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders?status=paid"

# Par date
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders?date_from=2025-01-01&date_to=2025-12-31"

# Par numéro
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders?q=CMD-2025-000001"

# Pagination
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders?page=2&perPage=5"
```

### **3. Détail d'une commande**
```bash
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders/123"
```

### **4. Téléchargement facture**
```bash
curl -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders/123/invoice" -o facture.pdf
```

### **5. Re-commande**
```bash
curl -X POST -H "Authorization: Bearer <TOKEN>" "http://localhost:8000/api/orders/123/reorder"
```

## 📋 **Format des réponses**

### **1. Liste des commandes**
```json
{
  "success": true,
  "items": [
    {
      "id": 1,
      "number": "CMD-2025-000001",
      "status": "paid",
      "date": "2025-09-22T14:00:00.000Z",
      "paidAt": "2025-09-22T14:05:00.000Z",
      "itemsCount": 3,
      "subtotal": 70.0,
      "discount": 7.0,
      "shipping": 4.99,
      "tax": 13.23,
      "total": 81.22,
      "currency": "EUR",
      "shippingAddress": {...},
      "billingAddress": {...},
      "payment": {
        "id": 1,
        "status": "succeeded",
        "amount": 81.22
      },
      "timeline": [
        {"key": "created", "at": "2025-09-22T14:00:00.000Z"},
        {"key": "paid", "at": "2025-09-22T14:05:00.000Z"}
      ]
    }
  ],
  "total": 1
}
```

### **2. Détail d'une commande**
```json
{
  "id": 1,
  "number": "CMD-2025-000001",
  "status": "paid",
  "date": "2025-09-22T14:00:00.000Z",
  "paidAt": "2025-09-22T14:05:00.000Z",
  "itemsCount": 3,
  "subtotal": 70.0,
  "discount": 7.0,
  "shipping": 4.99,
  "tax": 13.23,
  "total": 81.22,
  "currency": "EUR",
  "shippingAddress": {...},
  "billingAddress": {...},
  "payment": {...},
  "timeline": [...],
  "lines": [
    {
      "id": 1,
      "productId": 123,
      "title": "Produit Test",
      "variant": {"size": "M", "color": "Rouge"},
      "unitPrice": 29.99,
      "qty": 2,
      "subtotal": 59.98,
      "image": "https://example.com/image.jpg"
    }
  ]
}
```

### **3. Re-commande**
```json
{
  "success": true,
  "message": "Articles prêts à être ajoutés au panier",
  "items": [
    {
      "product_id": 123,
      "name": "Produit Test",
      "price": 29.99,
      "qty": 2,
      "variant": {"size": "M", "color": "Rouge"},
      "image": "https://example.com/image.jpg"
    }
  ]
}
```

## 🎯 **Compatibilité frontend**

L'API est parfaitement compatible avec le frontend Vue.js/Quasar existant :

### **Endpoints utilisés par le frontend :**
- ✅ `GET /api/orders` → `/account/orders` (liste paginée + filtres)
- ✅ `GET /api/orders/{id}` → `/account/orders/{id}` (détail + lignes + timeline)
- ✅ `GET /api/orders/{id}/invoice` → Bouton "Télécharger facture"
- ✅ `POST /api/orders/{id}/reorder` → Bouton "Re-commander"

### **Format JSON compatible :**
- ✅ **camelCase** : Tous les champs en camelCase
- ✅ **Timeline** : Structure pour affichage du suivi
- ✅ **Payment** : Informations de paiement intégrées
- ✅ **Addresses** : Adresses de livraison et facturation
- ✅ **Lines** : Détails des articles commandés

## 🔄 **Intégration avec Checkout (B6)**

### **Compatibilité parfaite :**
- ✅ **OrderNumberService** : Génération automatique des numéros
- ✅ **CheckoutController** : Utilise le service de numérotation
- ✅ **Format cohérent** : Même structure de données

### **Flux complet :**
1. **Checkout** : Création de commande avec numéro unique
2. **Orders API** : Consultation et gestion des commandes
3. **Frontend** : Affichage dans `/account/orders` et `/account/orders/{id}`

## 📊 **Fonctionnalités avancées**

### **1. Timeline dynamique**
- ✅ **Statuts supportés** : pending, paid, processing, shipped, delivered, canceled, failed
- ✅ **Dates automatiques** : created_at, paid_at, meta dates
- ✅ **Affichage frontend** : Structure prête pour composant timeline

### **2. Filtres avancés**
- ✅ **Recherche** : Par numéro de commande
- ✅ **Statut** : Filtrage par statut
- ✅ **Date** : Plage de dates (from/to)
- ✅ **Pagination** : Gestion des grandes listes

### **3. Facture PDF**
- ✅ **Template professionnel** : En-tête, adresses, articles, totaux
- ✅ **Formatage** : Montants en euros, TVA, remises
- ✅ **Téléchargement** : Nom de fichier personnalisé

### **4. Re-commande**
- ✅ **Structure cart** : Items prêts pour le panier
- ✅ **Variantes** : Conservation des options
- ✅ **Prix** : Prix figés au moment de la commande

## 🛡️ **Sécurité implémentée**

### **1. Policy OrderPolicy**
```php
public function view(User $user, Order $order): bool
{
    return $order->user_id === $user->id;
}
```

### **2. Routes protégées**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrdersController::class, 'index']);
    Route::get('/orders/{order}', [OrdersController::class, 'show'])->can('view', 'order');
    Route::get('/orders/{order}/invoice', [OrdersController::class, 'invoice'])->can('view', 'order');
    Route::post('/orders/{order}/reorder', [OrdersController::class, 'reorder'])->can('view', 'order');
});
```

### **3. Vérification propriétaire**
- ✅ **Toutes les actions** : Vérification user_id
- ✅ **403 Forbidden** : Accès refusé aux autres utilisateurs
- ✅ **Tests** : Couverture complète des cas d'usage

## 🚀 **Fonctionnalités métier**

### **1. Numérotation des commandes**
- ✅ **Format** : `CMD-YYYY-000123`
- ✅ **Séquence** : Incrémentale par année
- ✅ **Unicité** : Vérification des doublons
- ✅ **Performance** : Requête optimisée

### **2. Gestion des statuts**
- ✅ **Statuts supportés** : pending, paid, processing, shipped, delivered, canceled, failed
- ✅ **Timeline** : Génération automatique selon le statut
- ✅ **Dates** : Traçabilité des changements

### **3. Facturation**
- ✅ **PDF professionnel** : Template avec logo, adresses, articles
- ✅ **Calculs** : Sous-total, remises, TVA, total
- ✅ **Formatage** : Montants en euros, dates en français

### **4. Re-commande**
- ✅ **Items complets** : product_id, name, price, qty, variant, image
- ✅ **Compatibilité** : Format compatible avec le panier
- ✅ **Sécurité** : Seuls les propriétaires peuvent re-commander

## 🎉 **Résumé**

L'API Orders est maintenant complètement implémentée et prête pour la production. Elle offre :

- ✅ **Gestion complète** : Liste, détail, facture, re-commande
- ✅ **Sécurité renforcée** : Policy propriétaire, Sanctum
- ✅ **Filtres avancés** : Recherche, statut, dates, pagination
- ✅ **Facture PDF** : Template professionnel avec DomPDF
- ✅ **Re-commande** : Structure compatible avec le panier
- ✅ **Timeline** : Suivi automatique des statuts
- ✅ **Compatibilité frontend** : Format JSON camelCase
- ✅ **Tests complets** : Couverture de tous les cas d'usage

**L'implémentation est terminée et prête pour la production !** 🚀
