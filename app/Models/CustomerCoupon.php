<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCoupon extends Model
{
    //$table = 'customers_coupons';

    protected $fillable = [
        'customer_id',
        'coupon_id',
        'order_id',
        'is_used',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
