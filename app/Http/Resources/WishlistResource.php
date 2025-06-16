<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->product->price;
        $discount = $this->product->discount_price;

        $finalPrice = $discount
            ? $price - ($price * $discount / 100)
            : $price;
            
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product name' => $this->product->name ?? null,
            'original price' => number_format($price, 2) . ' $',
            'discount' => $discount ? $discount . ' %' : 'No discount',
            'final price' => number_format($finalPrice, 2) . ' $',
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
