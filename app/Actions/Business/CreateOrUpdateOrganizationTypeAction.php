<?php

namespace App\Actions\Business;

use App\Models\OrganizationType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateOrganizationTypeAction
{
    use AsAction;

    /** @param array{business_type_id: int, name: string, active: bool} $data */
    public function handle(?int $organization_type_id, array $data): OrganizationType
    {
        abort_unless(
            auth()->user()?->can($organization_type_id ? 'organization_types.edit' : 'organization_types.create'),
            403
        );

        $attributes = [
            'business_type_id' => $data['business_type_id'],
            'name'             => $data['name'],
            'label'            => OrganizationType::normalizeLabel($data['name']),
            'active'           => $data['active'],
        ];

        if ($organization_type_id) {
            $organization_type = OrganizationType::query()->findOrFail($organization_type_id);
            $organization_type->update($attributes);

            return $organization_type->fresh(['business_type']);
        }

        return OrganizationType::create($attributes);
    }
}
