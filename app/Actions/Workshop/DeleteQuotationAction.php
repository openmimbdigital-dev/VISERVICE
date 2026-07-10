<?php

namespace App\Actions\Workshop;

use App\Models\Quotation;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteQuotationAction
{
    use AsAction;

    public function handle(int $quotation_id): void
    {
        abort_unless(auth()->user()?->can('workshop.quotations.delete'), 403);

        $quotation = Quotation::query()->forAuthUser()->findOrFail($quotation_id);

        abort_unless($quotation->canDelete(), 403, 'No se puede eliminar esta cotización.');

        $quotation->delete();
    }
}
