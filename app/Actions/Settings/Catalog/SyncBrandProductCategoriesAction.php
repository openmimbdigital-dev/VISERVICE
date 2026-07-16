<?php

namespace App\Actions\Settings\Catalog;

use App\Models\Brand;
use App\Models\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncBrandProductCategoriesAction
{
    use AsAction;

    /**
     * @param  array<int>  $product_category_ids
     */
    public function handle(Brand $brand, array $product_category_ids): void
    {
        abort_unless(
            auth()->user()->can('settings.brands.edit') || auth()->user()->can('settings.brands.create'),
            403
        );

        $user = auth()->user();

        $allowed_ids = ProductCategory::query()
            ->visibleToUser($user)
            ->where('inventory', true)
            ->whereIn('id', $product_category_ids)
            ->pluck('id')
            ->all();

        $brand->productCategories()->sync(
            collect($product_category_ids)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->intersect($allowed_ids)
                ->values()
                ->all()
        );
    }
}
