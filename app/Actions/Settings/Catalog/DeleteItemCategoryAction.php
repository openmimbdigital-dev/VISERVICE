<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ItemCategory;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteItemCategoryAction
{
    use AsAction;

    public function handle(int $item_category_id): void
    {
        abort_unless(auth()->user()?->can('settings.item_categories.delete'), 403);

        $item_category = ItemCategory::query()->visibleToUser()->findOrFail($item_category_id);

        abort_unless($item_category->canDelete(), 403);

        $item_category->delete();
    }
}
