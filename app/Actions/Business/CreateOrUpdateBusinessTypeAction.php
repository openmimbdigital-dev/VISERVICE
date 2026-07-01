<?php

namespace App\Actions\Business;

use App\Models\BusinessType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateBusinessTypeAction
{
    use AsAction;

    /** @param array{name: string, status: bool} $data */
    public function handle(?int $business_type_id, array $data): BusinessType
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        $attributes = [
            'name'   => $data['name'],
            'label'  => BusinessType::normalizeLabel($data['name']),
            'status' => $data['status'],
        ];

        if ($business_type_id) {
            $business_type = BusinessType::query()->findOrFail($business_type_id);
            $business_type->update($attributes);

            return $business_type->fresh();
        }

        return BusinessType::create($attributes);
    }
}
