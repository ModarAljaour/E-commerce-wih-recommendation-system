<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->number,
            'total_price' => number_format($this->total_price, 2),
            'status' => $this->status,
            'payment_method' => $this->paymentMethod->name ?? null,
            'payment_status' => $this->payment_status ?? 'pending',

            'customer' => [
                'name' => $this->customer->name ?? null,
                'email' => $this->customer->email ?? null,
                'phone' => $this->customer->phone ?? null,
            ],

            'billing_address' => new OrderAddressResource($this->billingAddress),
            'shipping_address' => new OrderAddressResource($this->shippingAddress),

            'items' => OrderItemResource::collection($this->items),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
