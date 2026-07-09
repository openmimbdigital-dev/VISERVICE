<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ItemType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteItemTypeAction
{
    use AsAction;

    public function handle(int $item_type_id): void
    {
        abort_unless(auth()->user()?->can('settings.item_types.delete'), 403);

        $item_type = ItemType::query()->visibleToUser()->findOrFail($item_type_id);

        abort_unless($item_type->canDelete(), 403);

        $item_type->delete();
    }
}
