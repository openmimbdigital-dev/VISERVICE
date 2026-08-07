<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Models\GeneralConfig;
use App\Models\WorkOrder;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveWorkOrderDocumentClientAction
{
    use AsAction;

    public function handle(int $work_order_id, string $document_label, string $document_value): WorkOrder
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        $config = GeneralConfig::query()
            ->forAuthUser()
            ->associatedDocumentsOt()
            ->where('business_id', $work_order->business_id)
            ->where('label', $document_label)
            ->firstOrFail();

        $value = trim($document_value);
        $had_previous = ! empty($work_order->document_client);

        // Un solo documento por OT: reemplaza el JSON completo
        $documents = [$config->label => $value];

        $work_order->update(['document_client' => $documents]);
        $work_order = $work_order->fresh();

        $description = ($had_previous ? 'Actualizó' : 'Registró')
            . " documento asociado «{$config->value}» en la OT {$work_order->reference}";
        $properties = [
            'document_label' => $config->label,
            'document_value' => $value,
            'document_name'  => $config->value,
        ];

        LogUserHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $description,
            subject: $work_order,
            subject_label: $work_order->reference,
            properties: $properties,
            business_id: (int) $work_order->business_id,
        );

        LogEquipmentHistoricalAction::run(
            action: 'updated',
            module: 'workshop.work-orders',
            description: $description,
            subject: $work_order,
            properties: $properties,
            business_id: (int) $work_order->business_id,
        );

        return $work_order;
    }
}
