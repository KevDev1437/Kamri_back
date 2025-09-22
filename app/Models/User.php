<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'postal_code',
        'city',
        'country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'wishlist_items')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function cart()
    {
        return $this->hasOne(\App\Models\Cart::class);
    }

    public function wishlist()
    {
        return $this->hasOne(\App\Models\Wishlist::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }


}
