<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Image extends Model
{
    use HasFactory, HasApiTokens;

    public function imageable()
    {
        return $this->morphTo();
    }
}
