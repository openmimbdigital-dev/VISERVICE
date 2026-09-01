<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\Remission;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteRemissionAction
{
    use AsAction;

    public function handle(int $remission_id): void
    {
        abort_unless(auth()->user()?->can('workshop.remissions.delete'), 403);

        $remission = Remission::query()->forAuthUser()->findOrFail($remission_id);

        if ($remission->status?->isTerminal()) {
            throw new \RuntimeException('No se puede eliminar una remisión finalizada o cancelada.');
        }

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.remissions',
            description: "Eliminó la remisión {$remission->reference}",
            subject: $remission,
            subject_label: $remission->reference,
            properties: [
                'status'        => $remission->status?->value,
                'type'          => $remission->type,
                'work_order_id' => $remission->work_order_id,
            ],
            business_id: (int) $remission->business_id,
        );

        $remission->items()->delete();
        $remission->delete();
    }
}
