<?php

namespace App\Actions\Workshop;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
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

        $payload = ['status' => $status];

        if ($status === QuotationStatus::Rechazada) {
            $payload['reject_reason'] = $reject_reason;
        }

        $quotation->update($payload);

        return $quotation->fresh();
    }
}
