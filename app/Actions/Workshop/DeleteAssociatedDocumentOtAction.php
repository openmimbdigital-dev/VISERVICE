<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\GeneralConfig;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAssociatedDocumentOtAction
{
    use AsAction;

    public function handle(int $config_id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.delete'), 403);

        $config = GeneralConfig::query()
            ->forAuthUser()
            ->associatedDocumentsOt()
            ->findOrFail($config_id);

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.work-orders.associated-documents',
            description: "Eliminó el documento asociado {$config->value}",
            subject: $config,
            subject_label: $config->value,
            properties: [
                'key'   => $config->key,
                'label' => $config->label,
                'value' => $config->value,
            ],
            business_id: (int) $config->business_id,
        );

        $config->delete();
    }
}
