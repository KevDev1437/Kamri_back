<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $fillable = ['wishlist_id','product_id','options','options_hash'];
    protected $casts = ['options' => 'array'];

    public function wishlist() { return $this->belongsTo(Wishlist::class); }
    public function product()  { return $this->belongsTo(Product::class); }
}
