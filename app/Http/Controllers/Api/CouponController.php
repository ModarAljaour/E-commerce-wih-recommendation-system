<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use GeneralTrait;
    public function index()
    {
        return response()->json(Coupon::latest()->paginate(2));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ]);

        $coupon = Coupon::create($validated);

        return $this->apiResponse($coupon);
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|string|unique:coupons,code,' . $coupon->id,
            'type' => 'sometimes|in:fixed,percentage',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_user' => 'sometimes|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'is_active' => 'boolean',
        ]);

        $coupon->update($validated);
        $coupon->fresh();
        return response()->json(['message' => 'Coupon updated successfully.', 'coupon' => $coupon]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully.']);
    }
}
