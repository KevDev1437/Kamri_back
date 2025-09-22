<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'product_name', 'product_image',
        'unit_price', 'qty', 'options', 'options_hash'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'options' => 'array',
        'qty' => 'integer'
    ];

    public function cart() { return $this->belongsTo(Cart::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
