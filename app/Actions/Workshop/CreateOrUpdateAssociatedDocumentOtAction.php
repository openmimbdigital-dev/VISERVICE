<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\Business;
use App\Models\GeneralConfig;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateAssociatedDocumentOtAction
{
    use AsAction;

    /** @param  array{value: string, label: string, business_id: int}  $data */
    public function handle(?int $config_id, array $data): GeneralConfig
    {
        abort_unless(
            auth()->user()->can(
                $config_id
                    ? 'workshop.work-orders.associated-documents.edit'
                    : 'workshop.work-orders.associated-documents.create'
            ),
            403
        );

        $business_id = (int) $data['business_id'];
        $user        = auth()->user();

        if (! $user->hasRole('superAdmin')) {
            abort_unless(in_array($business_id, $user->businessIds(), true), 403);
        }

        $business = Business::query()->forAuthUser()->findOrFail($business_id);

        $attributes = [
            'business_id' => $business_id,
            'key'         => GeneralConfig::KEY_ASSOCIATE_DOCUMENT_OT,
            'label'       => $data['label'],
            'value'       => $data['value'],
        ];

        if ($config_id) {
            $config = GeneralConfig::query()
                ->forAuthUser()
                ->associatedDocumentsOt()
                ->findOrFail($config_id);

            abort_unless((int) $config->business_id === $business_id, 403);

            $config->update($attributes);
            $config = $config->fresh();

            LogUserHistoricalAction::run(
                action: 'updated',
                module: 'workshop.work-orders.associated-documents',
                description: "Actualizó el documento asociado {$config->value}",
                subject: $config,
                subject_label: $config->value,
                properties: [
                    'key'   => $config->key,
                    'label' => $config->label,
                    'value' => $config->value,
                ],
                business_id: $business_id,
            );

            return $config;
        }

        $config = $business->generalConfigs()->create($attributes);

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'workshop.work-orders.associated-documents',
            description: "Creó el documento asociado {$config->value}",
            subject: $config,
            subject_label: $config->value,
            properties: [
                'key'   => $config->key,
                'label' => $config->label,
                'value' => $config->value,
            ],
            business_id: $business_id,
        );

        return $config;
    }
}
