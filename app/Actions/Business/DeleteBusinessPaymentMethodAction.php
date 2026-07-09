<?php

namespace App\Actions\Business;

use App\Models\BusinessPaymentMethod;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteBusinessPaymentMethodAction
{
    use AsAction;

    public function handle(int $payment_method_id): void
    {
        abort_unless(auth()->user()?->can('business_payment_methods.delete'), 403);

        $method = BusinessPaymentMethod::query()->visibleToUser()->findOrFail($payment_method_id);

        abort_unless($method->canDelete(), 403);

        $method->delete();
    }
}
