<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteQuotationAction
{
    use AsAction;

    public function handle(int $quotation_id): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.delete'), 403);

        $quotation = Quotation::query()
            ->forAuthUser()
            ->with(['client:id,name', 'equipments', 'items'])
            ->findOrFail($quotation_id);

        if (! $quotation->canBeDeleted()) {
            throw ValidationException::withMessages([
                'quotation' => $quotation->isAccepted()
                    ? 'No se puede eliminar: la cotización está aceptada.'
                    : 'No se puede eliminar: la cotización está rechazada.',
            ]);
        }

        $properties = [
            'status'        => $quotation->status?->value ?? $quotation->status,
            'client_id'     => $quotation->client_id,
            'equipment_ids' => $quotation->equipments->pluck('id')->all(),
            'total'         => $quotation->total,
        ];

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.quotations',
            description: "Eliminó la cotización {$quotation->reference}",
            subject: $quotation,
            subject_label: $quotation->reference,
            properties: $properties,
            business_id: (int) $quotation->business_id,
        );

        foreach ($quotation->equipments as $equipment) {
            LogEquipmentHistoricalAction::run(
                action: 'deleted',
                module: 'workshop.quotations',
                description: "Eliminó la cotización {$quotation->reference}",
                equipment: $equipment,
                subject: $quotation,
                properties: $properties,
                business_id: (int) $quotation->business_id,
            );
        }

        $quotation->delete();
    }
}
