<?php

namespace App\Actions\Workshop;

use App\Actions\LogUserHistoricalAction;
use App\Models\QuotationServiceType;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateQuotationServiceTypeAction
{
    use AsAction;

    /** @param  array{name: string, active: bool}  $data */
    public function handle(?int $service_type_id, array $data): QuotationServiceType
    {
        abort_unless(
            auth()->user()->can($service_type_id ? 'workshop.quotation_service_types.edit' : 'workshop.quotation_service_types.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => CatalogLabelNormalizer::fromName($data['name']),
            'active' => $data['active'],
        ];

        if ($service_type_id) {
            $service_type = QuotationServiceType::query()->visibleToUser($user)->findOrFail($service_type_id);
            abort_unless($service_type->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->businessIds()[0] ?? null;
            $attributes['general']     = $is_super_admin;

            $service_type->update($attributes);
            $service_type = $service_type->fresh();

            LogUserHistoricalAction::run(
                action: 'updated',
                module: 'workshop.quotation_service_types',
                description: "Actualizó el tipo de servicio {$service_type->name}",
                subject: $service_type,
                subject_label: $service_type->name,
                properties: [
                    'active'  => $service_type->active,
                    'general' => $service_type->general,
                ],
                business_id: $service_type->business_id ? (int) $service_type->business_id : null,
            );

            return $service_type;
        }

        $attributes['business_id'] = $is_super_admin ? null : ($user->businessIds()[0] ?? null);
        $attributes['general']     = $is_super_admin;

        $service_type = QuotationServiceType::create($attributes);

        LogUserHistoricalAction::run(
            action: 'created',
            module: 'workshop.quotation_service_types',
            description: "Creó el tipo de servicio {$service_type->name}",
            subject: $service_type,
            subject_label: $service_type->name,
            properties: [
                'active'  => $service_type->active,
                'general' => $service_type->general,
            ],
            business_id: $service_type->business_id ? (int) $service_type->business_id : null,
        );

        return $service_type;
    }
}
