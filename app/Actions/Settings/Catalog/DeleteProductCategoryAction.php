<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ProductCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProductCategoryAction
{
    use AsAction;

    public function handle(int $product_category_id): void
    {
        abort_unless(auth()->user()?->can('settings.product_categories.delete'), 403);

        $product_category = ProductCategory::query()->visibleToUser()->findOrFail($product_category_id);

        abort_unless($product_category->canDelete(), 403);

        $product_category->delete();
    }
}
