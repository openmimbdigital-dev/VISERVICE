<?php

namespace App\Actions\Catalog;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolveUsableCouponAction
{
    use AsAction;

    public function handle(
        int $business_id,
        float $subtotal,
        ?int $coupon_id = null,
        ?string $code = null,
        ?int $excluded_work_order_id = null,
        string $error_key = 'coupon_code',
    ): ?Coupon {
        if ($coupon_id === null && $code === null) {
            return null;
        }

        $coupon = $code !== null
            ? $this->findByCode($business_id, $code, $error_key)
            : $this->findById($business_id, (int) $coupon_id, $error_key);

        $reason = $coupon->unavailableReason($subtotal, $excluded_work_order_id);

        if ($reason !== null) {
            throw ValidationException::withMessages([$error_key => $reason]);
        }

        return $coupon;
    }

    private function findByCode(int $business_id, string $code, string $error_key): Coupon
    {
        $code = Coupon::normalizeCode($code);

        if ($code === '') {
            throw ValidationException::withMessages([
                $error_key => 'Escribe el código del cupón.',
            ]);
        }

        $coupon = Coupon::query()
            ->where('business_id', $business_id)
            ->where('code', $code)
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                $error_key => 'No existe un cupón con ese código.',
            ]);
        }

        return $coupon;
    }

    private function findById(int $business_id, int $coupon_id, string $error_key): Coupon
    {
        $coupon = Coupon::query()
            ->where('business_id', $business_id)
            ->whereKey($coupon_id)
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                $error_key => 'El cupón seleccionado no está disponible.',
            ]);
        }

        return $coupon;
    }
}
