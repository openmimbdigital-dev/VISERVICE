<?php

namespace App\Actions\Settings\Catalog;

use App\Models\Unit;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateUnitAction
{
    use AsAction;

    /** @param  array{name: string, symbol: string, active: bool}  $data */
    public function handle(?int $unit_id, array $data): Unit
    {
        abort_unless(
            auth()->user()->can($unit_id ? 'settings.units.edit' : 'settings.units.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => CatalogLabelNormalizer::fromName($data['name']),
            'symbol' => $data['symbol'],
            'active' => $data['active'],
        ];

        if ($unit_id) {
            $unit = Unit::query()->visibleToUser($user)->findOrFail($unit_id);
            abort_unless($unit->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $unit->update($attributes);

            return $unit->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return Unit::create($attributes);
    }
}
