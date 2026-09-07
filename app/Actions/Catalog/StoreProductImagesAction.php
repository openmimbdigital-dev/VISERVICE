<?php

namespace App\Actions\Catalog;

use App\Models\Product;
use App\Support\ProductImageStorage;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreProductImagesAction
{
    use AsAction;

    /** @param  list<UploadedFile>  $files */
    public function handle(Product $product, array $files): void
    {
        abort_unless($product->isEditableBy(), 403);

        $next_sort_order = ((int) $product->images()->max('sort_order')) + ($product->images()->exists() ? 1 : 0);
        $has_primary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = ProductImageStorage::store($product->business_id, $product->id, $file);

            $product->images()->create([
                'path'       => $path,
                'is_primary' => ! $has_primary,
                'sort_order' => $next_sort_order,
            ]);

            $has_primary = true;
            $next_sort_order++;
        }
    }
}
