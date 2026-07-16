<?php

namespace App\Actions\Settings\Catalog;

use App\Models\Brand;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateCatalogBrandAction
{
    use AsAction;

    /**
     * @param  array{name: string, active: bool, product_category_ids: array<int>}  $data
     */
    public function handle(?int $brand_id, array $data): Brand
    {
        abort_unless(
            auth()->user()->can($brand_id ? 'settings.brands.edit' : 'settings.brands.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => CatalogLabelNormalizer::fromName($data['name']),
            'active' => $data['active'],
        ];

        if ($brand_id) {
            $brand = Brand::query()->visibleToUser($user)->findOrFail($brand_id);
            abort_unless($brand->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $brand->update($attributes);
        } else {
            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $brand = Brand::create($attributes);
        }

        SyncBrandProductCategoriesAction::run($brand, $data['product_category_ids']);
        EnsureBrandProductsUsageAction::run($brand);

        return $brand->fresh(['productCategories', 'brandUsages']);
    }
}
