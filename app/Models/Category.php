<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'is_hot',
        'parent_id',
        'description',
        'sort_order'
    ];

    protected $casts = [
        'is_hot' => 'boolean',
    ];

    // Relation parent-enfant pour les catégories
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Relation avec les produits
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope pour les catégories principales
    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    // Scope pour les catégories populaires
    public function scopeHot($query)
    {
        return $query->where('is_hot', true);
    }

    public function coupons()
    {
        return $this->belongsToMany(\App\Models\Coupon::class);
    }
}
