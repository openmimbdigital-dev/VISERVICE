<?php

namespace App\Actions\Workshop;

use App\Actions\LogEquipmentHistoricalAction;
use App\Actions\LogUserHistoricalAction;
use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\Status;
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

        $status_record = Status::query()
            ->forModule('quotations')
            ->where('name', $status->value)
            ->first();

        if (! $status_record) {
            throw ValidationException::withMessages([
                'status' => 'El estado seleccionado no es válido para cotizaciones.',
            ]);
        }

        $previous_status = $quotation->status;

        $payload = ['status' => $status->value];

        if ($status === QuotationStatus::Rejected) {
            $payload['reject_reason'] = $reject_reason;
        }

        $quotation->update($payload);
        $quotation = $quotation->fresh(['client:id,name', 'equipment', 'items', 'statusDefinition']);

        $label = $status_record->label;
        $description = "Cambió el estado de la cotización {$quotation->reference} a {$label}";
        $properties = [
            'from'          => $previous_status instanceof QuotationStatus
                ? $previous_status->value
                : $previous_status,
            'to'            => $status->value,
            'reject_reason' => $status === QuotationStatus::Rejected ? $reject_reason : null,
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
