<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     *
     * @param string $code
     * @param float $total
     * @param int $customerId
     * @return array
     * @throws \Exception
     */
    public function validateAndApplyCoupon(string $code, float $total, int $customerId): array
    {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $coupon) {
            throw new \Exception('Coupon not valid or expired.');
        }

        if ($coupon->min_order_amount && $total < $coupon->min_order_amount) {
            throw new \Exception("Order total must be at least {$coupon->min_order_amount} to use this coupon.");
        }

        $usageCount = DB::table('customers_coupons')
            ->where('customer_id', $customerId)
            ->where('coupon_id', $coupon->id)
            ->where('is_used', true)
            ->count();

        if ($usageCount >= $coupon->usage_per_user) {
            throw new \Exception("Coupon usage limit reached.");
        }

        $discount = $coupon->type === 'fixed'
            ? $coupon->value
            : ($total * ($coupon->value / 100));

        return [
            'coupon' => $coupon,
            'discount' => round($discount, 2),
            'final_total' => max(0, round($total - $discount, 2)),
        ];
    }
}
