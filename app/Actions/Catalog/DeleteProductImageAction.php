<?php

namespace App\Actions\Catalog;

use App\Models\ProductImage;
use App\Support\ProductImageStorage;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProductImageAction
{
    use AsAction;

    public function handle(ProductImage $image): void
    {
        $product = $image->product;

        abort_unless($product->isEditableBy(), 403);

        $was_primary = $image->is_primary;

        ProductImageStorage::delete($image->path);
        $image->delete();

        if ($was_primary) {
            $next = $product->images()->orderBy('sort_order')->orderBy('id')->first();
            $next?->update(['is_primary' => true]);
        }
    }
}
