<?php

namespace App\Repository;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartRepository
{
    public function query()
    {
        $customerId = Auth::guard('customer')->user()->id;
        if (!$customerId) {
            throw new \Exception('Customer not authenticated.');
        }
        //return Cart::with('product');
        return Cart::with('product')->where('customer_id', $customerId);
    }

    public function get()
    {
        $customerId = Auth::guard('customer')->user()->id;
        if (!$customerId) {
            throw new \Exception('Customer not authenticated.');
        }
        return $this->query()
            ->where('customer_id', $customerId)
            ->get();
        //return $this->query()->get();
    }

    public function add($productId, $qty = 1, $customerId = null)
    {
        $product = Product::findOrFail($productId);

        if ($product->stock < $qty) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => ['Quantity exceeds available stock.'],
            ]);
        }
        $customerId = $customerId ?? $this->customer_id ?? Auth::guard('customer')->user()->id;
        if (!$customerId) {
            throw new \Exception('Customer not authenticated.');
        }
        $cart = $this->query()
            ->where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->first();
        if ($cart) {
            $newQty = $cart->quantity + $qty;

            if ($newQty > $product->stock) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => ['Total quantity exceeds stock.'],
                ]);
            }
            $cart->increment('quantity', $qty);
            return $cart->fresh();
        }
        return Cart::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'quantity' => $qty,
        ]);
    }

    public function updateQuantity($productId, $qty)
    {
        $product = Product::findOrFail($productId);

        if ($qty > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => ['Requested quantity exceeds stock.'],
            ]);
        }

        $cart = $this->query()->where('product_id', $productId)->first();

        if (!$cart) {
            throw ValidationException::withMessages([
                'product' => ['Product not found in cart.'],
            ]);
        }

        $cart->update(['quantity' => $qty]);
        return $cart->fresh();
    }

    public function remove($productId)
    {
        $cartItem = $this->query()->where('product_id', $productId)->delete();
    }

    public function clear()
    {
        return $this->query()->delete();
    }

    public function total()
    {
        return $this->get()->sum(fn($item) => $item->quantity * $item->product->final_price);
    }

    public function count()
    {
        return $this->get()->sum('quantity');
    }
}
