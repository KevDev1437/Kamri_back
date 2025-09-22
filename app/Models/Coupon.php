<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code','type','value','min_subtotal','max_uses','used_count','starts_at','ends_at','active'];
    protected $casts = ['starts_at' => 'datetime','ends_at' => 'datetime'];

    public function isValidFor(float $subtotal): bool {
        if (!$this->active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at && now()->gt($this->ends_at)) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        if ($this->min_subtotal && $subtotal < $this->min_subtotal) return false;
        return true;
    }

    public function computeDiscount(float $subtotal): float {
        return $this->type === 'percentage' ? round($subtotal * ($this->value / 100), 2) : min($this->value, $subtotal);
    }
}
