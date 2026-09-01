<?php

namespace App\Actions\Catalog;

use App\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProductAction
{
    use AsAction;

    public function handle(int $product_id): void
    {
        abort_unless(auth()->user()?->can('catalog.products.delete'), 403);

        $product = Product::query()->forAuthUser()->findOrFail($product_id);

        abort_unless($product->canDelete(), 403);

        $product->delete();
    }
}
