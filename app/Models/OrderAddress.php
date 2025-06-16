<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class OrderAddress extends Model
{
    use HasApiTokens;

    protected $fillable = ['order_id', 'name', 'phone', 'type', 'email', 'address', 'city', 'state', 'postal_code', 'country'];


    // public function getCountryNameAttribute()
    // {
    //     return Countries::exists(strtoupper($this->country))
    //         ? Countries::getName(strtoupper($this->country))
    //         : $this->country;
    // }
}
