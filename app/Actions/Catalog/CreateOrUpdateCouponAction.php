<?php

namespace App\Actions\Catalog;

use App\Models\Coupon;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateCouponAction
{
    use AsAction;

    /** @param  array<string, mixed>  $data */
    public function handle(?int $coupon_id, array $data): Coupon
    {
        $user = auth()->user();

        abort_unless(
            $user?->can($coupon_id ? 'catalog.coupons.edit' : 'catalog.coupons.create'),
            403
        );

        $business_id = (int) $data['business_id'];
        abort_unless($business_id > 0, 422, 'El cupón necesita un negocio.');
        abort_unless($user->hasRole('superAdmin') || $user->belongsToBusiness($business_id), 403);

        if ($coupon_id) {
            $coupon = Coupon::query()->forAuthUser()->findOrFail($coupon_id);
            abort_unless($coupon->isEditableBy(), 403);

            $coupon->update($data);

            return $coupon->fresh();
        }

        return Coupon::create([
            ...$data,
            'created_by' => $user->id,
        ]);
    }
}
