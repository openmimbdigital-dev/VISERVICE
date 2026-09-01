<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ProductCategory;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateProductCategoryAction
{
    use AsAction;

    /** @param  array{name: string, active: bool, inventory: bool}  $data */
    public function handle(?int $product_category_id, array $data): ProductCategory
    {
        abort_unless(
            auth()->user()->can($product_category_id ? 'settings.product_categories.edit' : 'settings.product_categories.create'),
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

        if ($product_category_id) {
            $product_category = ProductCategory::query()->visibleToUser($user)->findOrFail($product_category_id);
            abort_unless($product_category->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $product_category->update($attributes);

            return $product_category->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return ProductCategory::create($attributes);
    }
}
