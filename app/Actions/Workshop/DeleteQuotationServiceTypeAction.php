<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
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

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.quotation_service_types',
            description: "Eliminó el tipo de servicio {$service_type->name}",
            subject: $service_type,
            subject_label: $service_type->name,
            properties: [
                'general' => $service_type->general,
            ],
            business_id: $service_type->business_id ? (int) $service_type->business_id : null,
        );

        $service_type->delete();
    }
}
