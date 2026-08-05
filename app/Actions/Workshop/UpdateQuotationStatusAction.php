<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateQuotationStatusAction
{
    use AsAction;

    public function handle(int $quotation_id, QuotationStatus $status, ?string $reject_reason = null): Quotation
    {
        abort_unless(auth()->user()?->can('workshop.quotations.edit'), 403);

        $quotation = Quotation::query()->forAuthUser()->findOrFail($quotation_id);

        $user = auth()->user();
        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $quotation->business_id === (int) $user->business_id, 403);
        }

        if ($quotation->isRejected()) {
            throw ValidationException::withMessages([
                'status' => 'No se puede cambiar el estado: la cotización ya está rechazada.',
            ]);
        }

        $previous_status = $quotation->status;

        $payload = ['status' => $status];

        if ($status === QuotationStatus::Rechazada) {
            $payload['reject_reason'] = $reject_reason;
        }

        $quotation->update($payload);
        $quotation = $quotation->fresh(['client:id,name', 'equipment', 'items']);

        $description = "Cambió el estado de la cotización {$quotation->reference} a {$status->label()}";
        $properties = [
            'from'          => $previous_status?->value ?? $previous_status,
            'to'            => $status->value,
            'reject_reason' => $status === QuotationStatus::Rechazada ? $reject_reason : null,
        ];

        LogUserHistoricalAction::run(
            action: 'status_changed',
            module: 'workshop.quotations',
            description: $description,
            subject: $quotation,
            subject_label: $quotation->reference,
            properties: $properties,
            business_id: (int) $quotation->business_id,
        );

        LogEquipmentHistoricalAction::run(
            action: 'status_changed',
            module: 'workshop.quotations',
            description: $description,
            subject: $quotation,
            properties: $properties,
            business_id: (int) $quotation->business_id,
        );

        return $quotation;
    }
}
