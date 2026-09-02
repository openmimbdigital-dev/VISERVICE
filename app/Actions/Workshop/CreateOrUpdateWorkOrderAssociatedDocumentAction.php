<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Models\AssociatedDocumentType;
use App\Models\WorkOrder;
use App\Models\WorkOrderAssociatedDocument;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateWorkOrderAssociatedDocumentAction
{
    use AsAction;

    public function handle(
        int $work_order_id,
        ?int $document_id,
        int $document_type_id,
        string $document_value,
        bool $send_invoice = false,
    ): WorkOrderAssociatedDocument {
        abort_unless(auth()->user()?->can('workshop.work-orders.edit'), 403);

        $work_order = WorkOrder::query()->forAuthUser()->findOrFail($work_order_id);

        if (! $work_order->isEditable()) {
            throw ValidationException::withMessages([
                'document_input_value' => 'La OT está finalizada o cancelada y no admite cambios.',
            ]);
        }

        $value = trim($document_value);

        $document_type = AssociatedDocumentType::query()
            ->forAuthUser()
            ->where('business_id', $work_order->business_id)
            ->findOrFail($document_type_id);

        $duplicate_query = WorkOrderAssociatedDocument::query()
            ->where('work_order_id', $work_order->id)
            ->where('associated_document_type_id', $document_type->id);

        if ($document_id) {
            $duplicate_query->whereKeyNot($document_id);
        }

        if ($duplicate_query->exists()) {
            throw ValidationException::withMessages([
                'selected_document_type_id' => 'Este documento ya está asociado a la OT.',
            ]);
        }

        $attributes = [
            'associated_document_type_id' => $document_type->id,
            'name'                        => $document_type->name,
            'value'                       => $value,
            'send_invoice'                => $send_invoice,
        ];

        if ($document_id) {
            $document = WorkOrderAssociatedDocument::query()
                ->where('work_order_id', $work_order->id)
                ->findOrFail($document_id);

            $document->update($attributes);
            $document = $document->fresh();

            $this->logChange($work_order, $document, 'Actualizó');

            return $document;
        }

        $document = WorkOrderAssociatedDocument::query()->create([
            'work_order_id' => $work_order->id,
            ...$attributes,
        ]);

        $this->logChange($work_order, $document, 'Registró');

        return $document;
    }

    private function logChange(
        WorkOrder $work_order,
        WorkOrderAssociatedDocument $document,
        string $verb,
    ): void {
        $description = "{$verb} documento asociado «{$document->name}» en la OT {$work_order->reference}";
        $properties = [
            'document_name'  => $document->name,
            'document_value' => $document->value,
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
    }
}
