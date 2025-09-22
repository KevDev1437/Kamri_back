<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = ['code','label','price','eta','active','countries'];
    protected $casts = ['countries' => 'array'];

    public function isAvailableForCountry(?string $country): bool {
        if (!$this->active) return false;
        if (!$this->countries || count($this->countries) === 0) return true;
        return $country ? in_array(strtoupper($country), $this->countries, true) : true;
    }
}
