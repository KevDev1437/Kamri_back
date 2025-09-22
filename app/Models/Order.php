<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id','number','status','currency','subtotal','discount','shipping_price','tax','total',
        'delivery_method','shipping_address','billing_address','payment_intent_id','meta'
    ];

    protected $casts = [
        'delivery_method' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'meta' => 'array',
    ];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
}
