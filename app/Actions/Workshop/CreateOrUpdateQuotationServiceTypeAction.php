<?php

namespace App\Actions\Workshop;

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

            return $service_type->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : ($user->businessIds()[0] ?? null);
        $attributes['general']     = $is_super_admin;

        return QuotationServiceType::create($attributes);
    }
}
