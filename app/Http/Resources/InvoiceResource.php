<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'customer_id' => $this->customer_id,
            'order_id' => $this->order_id,
            'total' => $this->total,
            'due_date' => $this->due_date,
            'paid_at' => $this->paid_at,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
