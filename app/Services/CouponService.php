<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Coupon;
use InvalidArgumentException;

class CouponService
{
    public const SESSION_KEY = 'applied_coupon';

    /**
     * Validate and apply a coupon to the customer session.
     */
    public function applyCoupon(string $code, float $cartSubtotal): Coupon
    {
        $code = strtoupper(trim($code));

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new InvalidArgumentException("El cupón {$code} no existe.");
        }

        if (! $coupon->is_active) {
            throw new InvalidArgumentException("El cupón {$code} está desactivado.");
        }

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            throw new InvalidArgumentException("El cupón {$code} aún no está activo.");
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            throw new InvalidArgumentException("El cupón {$code} ha caducado.");
        }

        if ($coupon->max_uses !== null && $coupon->uses_count >= $coupon->max_uses) {
            throw new InvalidArgumentException("El cupón {$code} ha alcanzado el límite máximo de usos.");
        }

        if ($cartSubtotal < (float) $coupon->min_order_amount) {
            $minFormatted = number_format($coupon->min_order_amount, 2, ',', '.');
            throw new InvalidArgumentException("El cupón {$code} requiere un importe mínimo de compra de {$minFormatted} €.");
        }

        session([self::SESSION_KEY => $coupon->code]);

        return $coupon;
    }

    /**
     * Get currently applied coupon from session, or null if none.
     */
    public function getAppliedCoupon(float $cartSubtotal): ?Coupon
    {
        $code = session(self::SESSION_KEY);
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isValidForAmount($cartSubtotal)) {
            $this->removeCoupon();

            return null;
        }

        return $coupon;
    }

    /**
     * Remove the active coupon from session.
     */
    public function removeCoupon(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Increment coupon usage count upon successful order creation.
     */
    public function recordUsage(Coupon $coupon): void
    {
        $coupon->increment('uses_count');

        AuditLog::record('coupon.used', $coupon, [
            'code' => $coupon->code,
            'uses_count' => $coupon->uses_count,
        ]);
    }
}
