<?php

namespace App\Actions\Catalog;

use App\Models\Item;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteItemAction
{
    use AsAction;

    public function handle(int $item_id): void
    {
        abort_unless(auth()->user()?->can('catalog.items.delete'), 403);

        $item = Item::query()->forAuthUser()->findOrFail($item_id);

        abort_unless($item->canDelete(), 403);

        $item->delete();
    }
}
