<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            //'total_price' => ['required', 'numeric', 'min:0'],

            // billing address
            'billing.name' => ['required', 'string'],
            'billing.email' => ['required', 'email'],
            'billing.phone' => ['required'],
            'billing.address' => ['required'],
            'billing.city' => ['required'],
            'billing.state' => ['required'],
            'billing.postal_code' => ['required'],
            'billing.country' => ['required'],
            'coupon_code' => 'nullable|string',


            // optional shipping if same_address is false
            'same_address' => ['required', 'boolean'],
            'shipping.name' => ['required_if:same_address,false', 'string'],
            'shipping.email' => ['required_if:same_address,false', 'email'],
            'shipping.phone' => ['required_if:same_address,false'],
            'shipping.address' => ['required_if:same_address,false'],
            'shipping.city' => ['required_if:same_address,false'],
            'shipping.state' => ['required_if:same_address,false'],
            'shipping.postal_code' => ['required_if:same_address,false'],
            'shipping.country' => ['required_if:same_address,false'],
            'coupon_code' => 'nullable|string',

        ];
    }
}
