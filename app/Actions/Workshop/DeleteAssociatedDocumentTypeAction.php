<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\AssociatedDocumentType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAssociatedDocumentTypeAction
{
    use AsAction;

    public function handle(int $document_type_id): void
    {
        abort_unless(auth()->user()?->can('workshop.work-orders.associated-documents.delete'), 403);

        $type = AssociatedDocumentType::query()->forAuthUser()->findOrFail($document_type_id);

        abort_unless($type->canDelete(), 403);

        LogUserHistoricalAction::run(
            action: 'deleted',
            module: 'workshop.work-orders.associated-documents',
            description: "Eliminó el documento asociado {$type->name}",
            subject: $type,
            subject_label: $type->name,
            properties: [
                'key' => $type->key,
            ],
            business_id: (int) $type->business_id,
        );

        $type->delete();
    }
}
