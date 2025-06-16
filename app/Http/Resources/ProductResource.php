<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => "$ " . $this->price,
            'discount_price' => $this->discount_price ? "$ " . $this->discount_price : "No discount",
            'final_price' => $this->final_price,
            'stock' => $this->stock,
            'brand' => $this->brand->name,
            'sub category' => $this->subCategory->name,
            'images' => $this->images->map(function ($image) {
                return asset('storage/Product/' . $image->filename);
            }),
        ];
    }
}
