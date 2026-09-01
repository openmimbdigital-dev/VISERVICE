<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
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

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'business.payment_methods',
            description: "Eliminó el método de pago {$method->name}",
            subject: $method,
            subject_label: $method->name,
            properties: [
                'general' => $method->general,
            ],
            business_id: $method->business_id ? (int) $method->business_id : null,
        );

        $method->delete();
    }
}
