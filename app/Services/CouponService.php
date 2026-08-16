<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function apply(Cart $cart, string $code): Cart
    {
        if ($cart->status !== 'active') {
            throw ValidationException::withMessages([
                'cart' => 'This cart is no longer active.',
            ]);
        }

        $coupon = Coupon::query()
            ->whereRaw(
                'UPPER(code) = ?',
                [strtoupper(trim($code))]
            )
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon' => 'Invalid coupon code.',
            ]);
        }

        $this->validate($cart, $coupon);

        $cart->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
        ]);

        return app(CartService::class)
            ->recalculate($cart);
    }

    public function remove(Cart $cart): Cart
    {
        if ($cart->status !== 'active') {
            throw ValidationException::withMessages([
                'cart' => 'This cart is no longer active.',
            ]);
        }

        $cart->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'discount' => 0,
        ]);

        return app(CartService::class)
            ->recalculate($cart);
    }

    public function validate(
        Cart $cart,
        Coupon $coupon
    ): void {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon' => 'This coupon is inactive.',
            ]);
        }

        if (
            $coupon->starts_at !== null &&
            now()->lt($coupon->starts_at)
        ) {
            throw ValidationException::withMessages([
                'coupon' => 'This coupon is not active yet.',
            ]);
        }

        if (
            $coupon->expires_at !== null &&
            now()->gt($coupon->expires_at)
        ) {
            throw ValidationException::withMessages([
                'coupon' => 'This coupon has expired.',
            ]);
        }

        if (
            $coupon->usage_limit !== null &&
            $coupon->usage_count >= $coupon->usage_limit
        ) {
            throw ValidationException::withMessages([
                'coupon' => 'This coupon has reached its usage limit.',
            ]);
        }

        if (
            (float) $cart->subtotal
            < (float) $coupon->minimum_order_amount
        ) {
            throw ValidationException::withMessages([
                'coupon' => sprintf(
                    'Minimum order amount for this coupon is %.2f.',
                    $coupon->minimum_order_amount
                ),
            ]);
        }
    }

    public function calculateDiscount(
        Cart $cart,
        Coupon $coupon
    ): float {
        $subtotal = (float) $cart->subtotal;

        if ($coupon->type === 'percentage') {
            $discount = $subtotal *
                ((float) $coupon->value / 100);

            if ($coupon->max_discount !== null) {
                $discount = min(
                    $discount,
                    (float) $coupon->max_discount
                );
            }

            return round(
                min($discount, $subtotal),
                2
            );
        }

        if ($coupon->type === 'fixed') {
            return round(
                min(
                    (float) $coupon->value,
                    $subtotal
                ),
                2
            );
        }

        throw ValidationException::withMessages([
            'coupon' => 'Unsupported coupon type.',
        ]);
    }
}