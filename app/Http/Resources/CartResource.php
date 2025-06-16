<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => optional($this->product)->name,
            'original_price' => number_format(optional($this->product)->price, 2),
            'discount_percentage' => optional($this->product)->discount_price,
            'quantity' => $this->quantity,
            'total_price' => number_format(optional($this->product)->final_price * $this->quantity, 2),
            'image' => optional($this->product->images->first())->filename
                ? asset('storage/Product/' . $this->product->images->first()->filename)
                : null,
        ];
    }
}
