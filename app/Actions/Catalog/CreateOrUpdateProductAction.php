<?php

namespace App\Actions\Catalog;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateProductAction
{
    use AsAction;

    /**
     * @param  array{
     *   product_type_id: int|null,
     *   product_category_id: int|null,
     *   unit_id: int|null,
     *   brand_id: int|null,
     *   sku: string,
     *   barcode: string|null,
     *   name: string,
     *   description: string|null,
     *   cost_price: float|null,
     *   profit_percentage: float|null,
     *   sale_price: float|null,
     *   status: bool,
     *   step: int,
     *   final_step: int
     * }  $data
     */
    public function handle(?int $product_id, array $data): Product
    {
        abort_unless(
            auth()->user()->can($product_id ? 'catalog.products.edit' : 'catalog.products.create'),
            403
        );

        $user        = auth()->user();
        $business_id = $user->hasRole('superAdmin')
            ? (int) ($data['business_id'] ?? 0)
            : (int) $user->business_id;

        abort_unless($business_id > 0, 403);

        if (! $user->hasRole('superAdmin')) {
            abort_unless((int) $business_id === (int) $user->business_id, 403);
        }

        $this->assertRelationsBelongToBusiness($business_id, $data);

        $track_inventory = false;

        if (! empty($data['product_category_id'])) {
            $product_category = ProductCategory::query()
                ->visibleToUser($user)
                ->findOrFail($data['product_category_id']);

            $track_inventory = (bool) $product_category->inventory;
        }

        $attributes = [
            'business_id'         => $business_id,
            'step'                => (int) ($data['step'] ?? 1),
            'final_step'          => (int) ($data['final_step'] ?? Product::DEFAULT_FINAL_STEP),
            'product_type_id'     => $data['product_type_id'] ?? null,
            'product_category_id' => $data['product_category_id'] ?? null,
            'unit_id'             => $data['unit_id'] ?? null,
            'brand_id'            => $data['brand_id'] ?? null,
            'sku'                 => $data['sku'],
            'barcode'             => $data['barcode'] ?? null,
            'name'                => $data['name'],
            'description'         => $data['description'] ?? null,
            'cost_price'          => $data['cost_price'] ?? null,
            'profit_percentage'   => $data['profit_percentage'] ?? null,
            'sale_price'          => $data['sale_price'] ?? null,
            'discount_type'       => $data['discount_type'] ?? null,
            'discount_value'      => $data['discount_value'] ?? null,
            'track_inventory'     => $track_inventory,
            'status'              => $data['status'] ?? true,
        ];

        if ($product_id) {
            $product = Product::query()->forAuthUser($user)->findOrFail($product_id);
            abort_unless($product->isEditableBy($user), 403);
            abort_unless((int) $product->business_id === (int) $business_id, 403);

            $product->update($attributes);

            return $product->fresh();
        }

        return Product::create($attributes);
    }

    private function assertRelationsBelongToBusiness(int $business_id, array $data): void
    {
        $user = auth()->user();

        if (! empty($data['product_type_id'])) {
            abort_unless(
                ProductType::query()->visibleToUser($user)->whereKey($data['product_type_id'])->exists(),
                422
            );
        }

        if (! empty($data['product_category_id'])) {
            abort_unless(
                ProductCategory::query()->visibleToUser($user)->whereKey($data['product_category_id'])->exists(),
                422
            );
        }

        if (! empty($data['unit_id'])) {
            abort_unless(
                Unit::query()->visibleToUser($user)->whereKey($data['unit_id'])->exists(),
                422
            );
        }

        if ($data['brand_id'] ?? null) {
            abort_unless(! empty($data['product_category_id']), 422);

            $brand_query = Brand::query()
                ->visibleToUser($user)
                ->forProductsCatalog()
                ->whereKey($data['brand_id'])
                ->whereHas('productCategories', fn ($category_query) => $category_query->whereKey($data['product_category_id']));

            abort_unless($brand_query->exists(), 422);
        }
    }
}
