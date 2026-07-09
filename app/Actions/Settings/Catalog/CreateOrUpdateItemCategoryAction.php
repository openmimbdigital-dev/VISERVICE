<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ItemCategory;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateItemCategoryAction
{
    use AsAction;

    /** @param  array{name: string, active: bool, inventory: bool}  $data */
    public function handle(?int $item_category_id, array $data): ItemCategory
    {
        abort_unless(
            auth()->user()->can($item_category_id ? 'settings.item_categories.edit' : 'settings.item_categories.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'      => $data['name'],
            'label'     => CatalogLabelNormalizer::fromName($data['name']),
            'active'    => $data['active'],
            'inventory' => $data['inventory'],
        ];

        if ($item_category_id) {
            $item_category = ItemCategory::query()->visibleToUser($user)->findOrFail($item_category_id);
            abort_unless($item_category->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $item_category->update($attributes);

            return $item_category->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return ItemCategory::create($attributes);
    }
}
