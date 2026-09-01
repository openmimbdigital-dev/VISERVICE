<?php

namespace App\Actions\Business;

use App\Actions\LogUserHistoricalAction;
use App\Models\CustomTax;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCustomTaxAction
{
    use AsAction;

    public function handle(int $custom_tax_id): void
    {
        abort_unless(auth()->user()?->can('custom_taxes.delete'), 403);

        $tax = CustomTax::query()->forAuthUser()->findOrFail($custom_tax_id);

        abort_unless($tax->canDelete(), 403);

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'business.custom_taxes',
            description: "Eliminó el impuesto {$tax->name}",
            subject: $tax,
            subject_label: $tax->name,
            properties: [
                'percentage' => $tax->percentage,
            ],
            business_id: (int) $tax->business_id,
        );

        $tax->delete();
    }
}
