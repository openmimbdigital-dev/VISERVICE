<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ItemType;
use App\Rules\NotConflictingWithGeneralCatalogName;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateItemTypeAction
{
    use AsAction;

    /** @param  array{name: string, active: bool}  $data */
    public function handle(?int $item_type_id, array $data): ItemType
    {
        abort_unless(
            auth()->user()->can($item_type_id ? 'settings.item_types.edit' : 'settings.item_types.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => CatalogLabelNormalizer::fromName($data['name']),
            'active' => $data['active'],
        ];

        if ($item_type_id) {
            $item_type = ItemType::query()->visibleToUser($user)->findOrFail($item_type_id);
            abort_unless($item_type->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $item_type->update($attributes);

            return $item_type->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return ItemType::create($attributes);
    }
}
