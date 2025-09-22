<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'sku',
        'stock_quantity',
        'image',
        'images',
        'category_id',
        'is_featured',
        'is_active',
        'weight',
        'dimensions',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'dimensions' => 'array',
    ];

    // Relation avec la catégorie
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scope pour les produits en vedette
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope pour les produits actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour les produits en stock
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Accesseur pour le prix effectif (avec ou sans promotion)
    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    // Accesseur pour vérifier si le produit est en promotion
    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    public function wishedBy()
    {
        return $this->belongsToMany(\App\Models\User::class, 'wishlist_items')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }
}
