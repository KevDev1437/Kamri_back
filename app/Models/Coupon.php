<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'type', 'value', 'active', 'starts_at', 'ends_at',
        'min_subtotal', 'max_redemptions', 'per_user_limit', 'applies_to', 'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_subtotal' => 'decimal:2',
        'active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function isActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $now = Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function withinDateRange(): bool
    {
        return $this->isActive();
    }

    public function getRemainingRedemptions(): ?int
    {
        if (!$this->max_redemptions) {
            return null;
        }

        $used = $this->redemptions()->count();
        return max(0, $this->max_redemptions - $used);
    }

    public function getUserRedemptionsCount(?User $user): int
    {
        if (!$user || !$this->per_user_limit) {
            return 0;
        }

        return $this->redemptions()->where('user_id', $user->id)->count();
    }
}
