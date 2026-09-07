<?php

namespace App\Actions\Catalog;

use App\Models\Coupon;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCouponAction
{
    use AsAction;

    public function handle(int $coupon_id): void
    {
        abort_unless(auth()->user()?->can('catalog.coupons.delete'), 403);

        $coupon = Coupon::query()->forAuthUser()->findOrFail($coupon_id);
        abort_unless($coupon->isEditableBy(), 403);

        // Un cupón ya usado no se borra: las OT que lo aplicaron perderían la
        // referencia de por qué tienen ese descuento. Se desactiva en su lugar.
        if ($coupon->usesCount() > 0) {
            throw ValidationException::withMessages([
                'coupon' => 'El cupón ya se usó en órdenes de trabajo. Desactívalo en lugar de eliminarlo.',
            ]);
        }

        $coupon->delete();
    }
}
