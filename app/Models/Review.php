<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'rating', 'comment', 'photos', 
        'helpful_count', 'reported_count', 'verified', 'anonymous', 'status'
    ];

    protected $casts = [
        'photos' => 'array',
        'verified' => 'boolean',
        'anonymous' => 'boolean',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function votes() { return $this->hasMany(ReviewVote::class); }
    public function reports() { return $this->hasMany(ReviewReport::class); }
}