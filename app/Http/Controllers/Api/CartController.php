<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Repository\CartRepository;
use App\Services\RecommendationService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use GeneralTrait;
    public $cart;
    protected $recommendationService;

    public function __construct(CartRepository $cart, RecommendationService $recommendationService)
    {
        $this->cart = $cart;
        $this->recommendationService = $recommendationService;
    }

    public function index()
    {
        try {
            $items = CartResource::collection(Cart::all());
            if ($items->isEmpty()) {
                return $this->apiResponse([
                    'message' => 'Your cart is empty.',
                ], 200);
            }
            return $this->apiResponse([
                'items' => $items,
                'total' => number_format($this->cart->total(), 2),
                'count' => $this->cart->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $item = $this->cart->add($validated['product_id'], $validated['quantity'] ?? 1);
            return $this->apiResponse(new CartResource($item));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, CartRepository $cart)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = $cart->updateQuantity(
            $request->product_id,
            $request->quantity
        );

        return $this->apiResponse([
            'message' => 'Cart updated.',
            'item' => new CartResource($item),
        ]);
    }

    public function destroy(Request $request, CartRepository $cart)
    {
        if (!$request->has('product_id')) {
            return response()->json([
                'error' => 'The product id field is required.'
            ], 400);
        }
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);
        $productId = $request->input('product_id');
        $cartItem = $cart->query()->where('product_id', $productId)->first();
        if (!$cartItem) {
            return $this->apiResponse(null, false, 'Product not found in your cart.', 404);
        }
        $cart->remove($productId);
        return $this->apiResponse(['message' => 'Product removed from cart.']);
    }

    public function clear(CartRepository $cart)
    {
        $cart->clear();
        return response()->json(['message' => 'Cart cleared.']);
    }

    
}
