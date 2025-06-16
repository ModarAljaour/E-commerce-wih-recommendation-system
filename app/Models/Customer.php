<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'email_verified_at', 'phone', 'password'];

    //protected $hidden = ['password'];

    protected $casts = ['email_verified_at' => 'datetime'];

    // Relationships
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'customers_coupons')
            ->withPivot('expireDate')
            ->withTimestamps();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
