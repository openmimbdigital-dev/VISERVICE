<?php

namespace App\Actions\Settings\Catalog;

use App\Models\Brand;
use App\Models\ItemCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncBrandItemCategoriesAction
{
    use AsAction;

    /**
     * @param  array<int>  $item_category_ids
     */
    public function handle(Brand $brand, array $item_category_ids): void
    {
        abort_unless(
            auth()->user()->can('settings.brands.edit') || auth()->user()->can('settings.brands.create'),
            403
        );

        $user = auth()->user();

        $allowed_ids = ItemCategory::query()
            ->visibleToUser($user)
            ->where('inventory', true)
            ->whereIn('id', $item_category_ids)
            ->pluck('id')
            ->all();

        $brand->itemCategories()->sync(
            collect($item_category_ids)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->intersect($allowed_ids)
                ->values()
                ->all()
        );
    }
}
