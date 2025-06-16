<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'products_count' => $this->products_count ?? $this->products()->count(),
            'logo' => $this->image
                ? asset('storage/Brands/' . $this->image->filename)
                : null,

            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
