<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'usage_per_user',
        'expires_at',
        'is_active',
    ];

    // Relationships
    public function customerCoupons()
    {
        return $this->hasMany(CustomerCoupon::class);
    }
}
