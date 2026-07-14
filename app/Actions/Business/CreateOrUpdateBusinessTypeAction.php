<?php

namespace App\Actions\Business;

use App\Models\BusinessType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBusinessTypeAction
{
    use AsAction;

    /** @param array{organization_type_id: int, name: string, active: bool} $data */
    public function handle(?int $business_type_id, array $data): BusinessType
    {
        abort_unless(
            auth()->user()?->can($business_type_id ? 'business_types.edit' : 'business_types.create'),
            403
        );

        $attributes = [
            'organization_type_id' => $data['organization_type_id'],
            'name'                 => $data['name'],
            'label'                => BusinessType::normalizeLabel($data['name']),
            'active'               => $data['active'],
        ];

        if ($business_type_id) {
            $business_type = BusinessType::query()->findOrFail($business_type_id);
            $business_type->update($attributes);

            return $business_type->fresh(['organization_type']);
        }

        return BusinessType::create($attributes);
    }
}
