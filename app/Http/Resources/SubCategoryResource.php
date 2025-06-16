<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category->name ?? null,
            //'sub category' => $this->products ?? null,
            'sub category' => ProductResource::collection($this->whenLoaded('products')),
            'products_count' => $this->products_count ?? $this->products()->count(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
