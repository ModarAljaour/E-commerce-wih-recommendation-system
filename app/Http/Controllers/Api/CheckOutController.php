<?php

namespace App\Http\Controllers\Api;

use App\Events\EmptyCartEvent;
use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CheckOutResource;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Notifications\CreateOrderNotification;
use App\Repository\CartRepository;
use App\Services\CouponService;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class CheckOutController extends Controller
{
    use GeneralTrait;

    protected $cart;
    public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }
    public function index()
    {
        try {
            $customerId = auth('customer')->id();
            $orders = Order::whereCustomerId($customerId)
                ->with(['items', 'billingAddress', 'shippingAddress'])
                ->latest()
                ->paginate(10);

            return $this->apiResponse(OrderResource::collection($orders));
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database query exceptions
            return $this->apiResponse(null, 500, 'Database query error');
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Handle authentication exceptions
            return $this->apiResponse(null, 401, 'Unauthorized');
        } catch (\Exception $e) {
            // Handle general exceptions
            return $this->apiResponse(null, 500, 'Internal server error');
        }
    }

    public function CartInfo()  // Before checkout
    {
        try {
            $cartItems = auth('customer')->user()
                ->carts()->with(['product:id,name,price,discount_price'])->get();

            if ($cartItems->isEmpty()) {
                return $this->apiResponse([
                    'message' => 'Your cart is empty.',
                    'items' => [],
                    'total' => 0,
                    'count' => 0,
                ], 200);
            }

            $total = 0;
            $count = 0;

            foreach ($cartItems as $item) {
                $product = $item->product;
                if (!$product) continue;

                $price = $product->final_price ?? 0;
                $lineTotal = $price * $item->quantity;

                $total += $lineTotal;
                $count += $item->quantity;
            }

            return $this->apiResponse([
                'items' => CheckOutResource::collection($cartItems),
                'total' => round($total, 2),
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function store(StoreOrderRequest $request)
    {
        DB::beginTransaction();
        try {
            $customerId = Auth::guard('customer')->user()->id;

            $cartItems = auth('customer')->user()->carts()->with('product')->get();

            if ($cartItems->isEmpty())
                return response()->json(['message' => 'your cart is empty'], 400);

            $totalBeforeDiscount  = $cartItems->sum(function ($item) {
                return $item->product->final_price * $item->quantity;
            });

            $couponCode = $request->coupon_code;
            $discount = 0;
            $finalTotal = $totalBeforeDiscount;

            if ($couponCode) {
                $couponService = app(CouponService::class);
                $couponData = $couponService->validateAndApplyCoupon($couponCode, $totalBeforeDiscount, $customerId);
                $discount = $couponData['discount'];
                $finalTotal = $couponData['final_total'];
            }

            $order = Order::create([
                // 'customer_id' => $customerId,
                // 'payment_method_id' => $request->payment_method_id,
                // 'total_price' => $totalPrice,
                // 'status' => 'pending',
                'customer_id' => $customerId,
                'payment_method_id' => $request->payment_method_id,
                'status' => 'pending',
                'total_before_discount' => $totalBeforeDiscount,
                'discount' => $discount,
                'total_after_discount' => $finalTotal,
                'coupon_code' => $couponCode,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->final_price,
                    'quantity' => $item->quantity,
                ]);
            }
            $addresses = [
                'billing' => $request->billing,
            ];

            if (!$request->same_address) {
                $addresses['shipping'] = $request->shipping;
            } else {
                $addresses['shipping'] = $request->billing;
            }

            foreach ($addresses as $type => $address) {
                $address['type'] = $type; // billing or shipping
                $order->addresses()->create($address);
            }

            //event(new OrderCreated($order->load('items.product')));
            //event(new EmptyCartEvent());

            // Notification::route('mail', $order->customer->email)
            //     ->notify(new CreateOrderNotification($order));

            DB::commit();

            return response()->json([
                'message' => 'Order placed successfully!',
                'order_id' => $order->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $order = Order::with(['paymentMethod', 'items', 'customer', 'billingAddress', 'shippingAddress'])
                ->findOrFail($id);
            return $this->apiResponse(new OrderResource($order));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, 404, 'Order not found');
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'processing', 'completed', 'delivered', 'cancelled', 'delivering']),
            ],
        ]);

        $allowedTransitions = [
            'pending' => ['cancelled', 'processing'],
            'processing' => ['delivering', 'cancelled'],
            'delivering' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];
        $currentStatus = $order->status;
        $newStatus = $request->status; // Retrieve the status from the request

        if ($currentStatus === $newStatus) {
            return response()->json(['message' => "Status is already set to $newStatus"], 200);
        }

        if (! $this->isValidTransition($currentStatus, $newStatus, $allowedTransitions)) {
            return response()->json([
                'message' => 'Invalid status transition.',
                'current_status' => $currentStatus,
                'new_status' => $newStatus,
            ], 400);
        }

        $order->update(['status' => $newStatus]);

        return response()->json(['message' => 'Order status updated successfully.']);
    }

    // Check if a status transition is valid.
    public function isValidTransition($currentStatus, $newStatus, $allowedTransitions)
    {
        if (is_null($currentStatus)) {
            return false;
        }

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $customer = auth('customer')->user();
        $total = $this->cart->total();

        try {
            $data = app(CouponService::class)
                ->validateAndApplyCoupon($request->code, $total, $customer->id);

            return response()->json([
                'message' => 'Coupon applied successfully.',
                'discount' => $data['discount'],
                'final_total' => $data['final_total'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


    // public function applyCoupon(Request $request)
    // {
    //     $request->validate([
    //         'code' => 'required|string',
    //     ]);

    //     $coupon = Coupon::where('code', $request->coupon_code)
    //         ->where('is_active', true)
    //         ->where(function ($query) {
    //             $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
    //         })
    //         ->first();

    //     if (! $coupon) {
    //         return response()->json(['message' => 'Coupon not valid or expired.'], 400);
    //     }

    //     $total = $this->cart->total();

    //     if ($coupon->min_order_amount && $total < $coupon->min_order_amount) {
    //         return response()->json([
    //             'message' => "Order total must be at least {$coupon->min_order_amount} to use this coupon."
    //         ], 400);
    //     }

    //     $customer = auth('customer')->user();

    //     if (! $customer) {
    //         return response()->json(['message' => 'Unauthorized'], 401);
    //     }


    //     // Check if customer exceeded usage_per_user
    //     $usageCount = DB::table('customers_coupons')
    //         ->where('customer_id', $customer->id)
    //         ->where('coupon_id', $coupon->id)
    //         ->where('is_used', true)
    //         ->count();

    //     if ($usageCount >= $coupon->usage_per_user) {
    //         return response()->json(['message' => 'Coupon usage limit reached.'], 400);
    //     }

    //     // Calculate discount
    //     $discount = $coupon->type === 'fixed'
    //         ? $coupon->value
    //         : ($total * ($coupon->value / 100));

    //     return response()->json([
    //         'message' => 'Coupon applied successfully.',
    //         'discount' => $discount,
    //         'final_total' => max(0, round($total - $discount, 2)),
    //     ]);
    // }
}
