<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\AssociatedDocumentType;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateAssociatedDocumentTypeAction
{
    use AsAction;

    /**
     * @param  array{
     *   business_id: int,
     *   name: string,
     *   active: bool,
     *   document_send: bool
     * }  $data
     */
    public function handle(?int $document_type_id, array $data): AssociatedDocumentType
    {
        abort_unless(
            auth()->user()->can(
                $document_type_id
                    ? 'workshop.work-orders.associated-documents.edit'
                    : 'workshop.work-orders.associated-documents.create'
            ),
            403
        );

        $user        = auth()->user();
        $business_id = (int) $data['business_id'];

        if (! $user->hasRole('superAdmin')) {
            abort_unless($user->belongsToBusiness($business_id), 403);
        }

        return DB::transaction(function () use ($document_type_id, $data, $business_id) {
            $attributes = [
                'business_id'   => $business_id,
                'key'           => AssociatedDocumentType::makeKeyFromName($data['name']),
                'name'          => $data['name'],
                'active'        => $data['active'],
                'document_send' => $data['document_send'],
            ];

            if ($document_type_id) {
                $type = AssociatedDocumentType::query()->forAuthUser()->findOrFail($document_type_id);
                abort_unless($type->isEditableBy($user, 'workshop.work-orders.associated-documents.edit'), 403);
                abort_unless((int) $type->business_id === $business_id, 403);

                $type->update($attributes);
            } else {
                $type = AssociatedDocumentType::query()->create($attributes);
            }

            $type = $type->fresh(['business']);

            LogUserHistoricalAction::run(
                action: $document_type_id ? 'updated' : 'created',
                module: 'workshop.work-orders.associated-documents',
                description: ($document_type_id ? 'Actualizó' : 'Creó') . " el documento asociado {$type->name}",
                subject: $type,
                subject_label: $type->name,
                properties: [
                    'key'           => $type->key,
                    'active'        => $type->active,
                    'document_send' => $type->document_send,
                ],
                business_id: $business_id,
            );

            return $type;
        });
    }
}
