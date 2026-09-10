<?php

namespace App\Actions\Workshop;

use App\Actions\Catalog\ResolveUsableCouponAction;
use App\Models\Quotation;
use App\Models\WorkOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyCouponToDocumentAction
{
    use AsAction;

    public function handle(Quotation|WorkOrder $document, ?int $coupon_id, int $business_id): void
    {
        $coupon_id = $coupon_id && $coupon_id > 0 ? $coupon_id : null;

        $coupon = ResolveUsableCouponAction::run(
            $business_id,
            round((float) $document->items()->sum('subtotal'), 2),
            coupon_id: $coupon_id,
            excluded_work_order_id: $document instanceof WorkOrder ? $document->id : null,
        );

        $document->forceFill([
            'coupon_id'   => $coupon?->id,
            'coupon_code' => $coupon?->code,
        ])->save();
    }
}
