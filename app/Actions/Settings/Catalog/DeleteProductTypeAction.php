<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ProductType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProductTypeAction
{
    use AsAction;

    public function handle(int $product_type_id): void
    {
        abort_unless(auth()->user()?->can('settings.product_types.delete'), 403);

        $product_type = ProductType::query()->visibleToUser()->findOrFail($product_type_id);

        abort_unless($product_type->canDelete(), 403);

        $product_type->delete();
    }
}
