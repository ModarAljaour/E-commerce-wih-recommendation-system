<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'brand_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
        'discount_price',
        'price',
        'stock',
    ];

    // Getter && Setter :
    public function getFinalPriceAttribute()
    {
        return $this->discount_price ?
            $this->price - ($this->price * $this->discount_price / 100)
            : $this->price;
    }



    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
