<?php

namespace App\Actions\Business;

use App\Models\OrganizationType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateOrganizationTypeAction
{
    use AsAction;

    /** @param array{name: string, status: bool} $data */
    public function handle(?int $organization_type_id, array $data): OrganizationType
    {
        abort_unless(
            auth()->user()?->can($organization_type_id ? 'organization_types.edit' : 'organization_types.create'),
            403
        );

        $attributes = [
            'name'   => $data['name'],
            'label'  => OrganizationType::normalizeLabel($data['name']),
            'status' => $data['status'],
        ];

        if ($organization_type_id) {
            $organization_type = OrganizationType::query()->findOrFail($organization_type_id);
            $organization_type->update($attributes);

            return $organization_type->fresh();
        }

        return OrganizationType::create($attributes);
    }
}
