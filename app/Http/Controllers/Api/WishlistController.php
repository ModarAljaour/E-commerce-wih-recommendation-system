<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Models\WishList;
use App\Repository\CartRepository;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    use GeneralTrait;
    protected $cart;

    public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }

    public function index()
    {
        $customerId = Auth::guard('customer')->user()->id;
        $wishlists = WishList::with('product')->where('customer_id', $customerId)->get();
        $count = $wishlists->count();
        $wishlistCount = $wishlists->count();
        return $this->apiResponse([
            'count' => $wishlistCount,
            'data' => WishlistResource::collection($wishlists),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $customerId = Auth::guard('customer')->user()->id;
        $product = Product::findOrFail($productId);

        if ($product->stock <= 0) {
            return response()
                ->json(['message' => 'Product is out of stock and cannot be added to wishlist.'], 400);
        }

        $existingWishlist = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($existingWishlist) {
            return response()
                ->json(['message' => 'This product is already in your wishlist.'], 400);
        }

        $wishlist = Wishlist::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
        ]);

        return $this->apiResponse(new WishlistResource($wishlist));
    }

    public function moveToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $customer = Auth::guard('customer')->user();

        $wishlistItem = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in your wishlist.'
            ], 404);
        }
        try {

            $cartItem = $this->cart->add($validated['product_id'], $validated['quantity'] ?? 1);
            //$cartItem = $this->cart->setCustomerId($customer->id)->add($productId, 1);
            $wishlistItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product moved to cart successfully',
                'cart_item' => $cartItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move product to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $customerId = Auth::guard('customer')->user()->id;

        $wishlistItem = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if (!$wishlistItem) {
            return response()
                ->json(['message' => 'This product is not in your wishlist.'], 400);
        }

        $wishlistItem->delete();

        return response()
            ->json(['message' => 'Product removed from wishlist'], 400);
    }
    public function count()
    {
        $customerId = Auth::guard('customer')->user()->id;
        $wishlists = WishList::with('product')->where('customer_id', $customerId)->get();
        $count = $wishlists->count();
        return response($count);
    }
}
