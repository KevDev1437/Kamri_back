<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'rating', 'comment', 'anonymous', 'verified', 'helpful_count'
    ];

    protected $casts = [
        'anonymous' => 'boolean',
        'verified' => 'boolean',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }

    public function photos() { return $this->hasMany(ReviewPhoto::class); }
    public function helpfulVotes() { return $this->hasMany(ReviewHelpfulVote::class); }
    public function reports() { return $this->hasMany(ReviewReport::class); }
}
