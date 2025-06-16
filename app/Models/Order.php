<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'customer_id',
        'status',
        // 'total_price',
        'payment_method_id',
        'payment_status',
        'number',
        'coupon_code',
        'total_before_discount',
        'discount',
        'total_after_discount',
    ];

    // protected $casts = [
    //     'status' => 'string',
    // ];

    //
    protected static function booted()
    {
        static::creating(function (Order $order) {
            $order->number = Order::getNextOrderNumber();
        });
    }
    public static function getNextOrderNumber()
    {
        $year = now()->year;

        return DB::transaction(function () use ($year) {
            $lastNumber = Order::whereYear('created_at', $year)->max('number');

            if ($lastNumber) {
                $sequence = (int) substr($lastNumber, 4) + 1;
            } else {
                $sequence = 1;
            }
            return $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
        });
    }


    // Relationships

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }
    public function billingAddress()
    {
        return $this->hasOne(OrderAddress::class)->where("type", '=', 'billing');
    }
    public function shippingAddress()
    {
        return $this->hasOne(OrderAddress::class)->where("type", '=', 'shipping');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'order_items',
            'order_id',
            'product_id'
        )
            ->using(OrderItem::class)    // Pivot custom model (optional)
            //->as('order_item')   // Rename pivot
            ->withPivot([
                'quantity',
                'price',
                'product_name'
            ]);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
