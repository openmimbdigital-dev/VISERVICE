<?php

namespace App\Actions\Workshop;

use App\Models\QuotationServiceType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteQuotationServiceTypeAction
{
    use AsAction;

    public function handle(int $service_type_id): void
    {
        abort_unless(auth()->user()?->can('workshop.quotation_service_types.delete'), 403);

        $service_type = QuotationServiceType::query()->visibleToUser()->findOrFail($service_type_id);

        abort_unless($service_type->canDelete(), 403);

        $service_type->delete();
    }
}
