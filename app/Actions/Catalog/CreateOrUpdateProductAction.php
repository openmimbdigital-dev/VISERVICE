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
     *   product_type_id: int,
     *   product_category_id: int,
     *   unit_id: int,
     *   brand_id: int|null,
     *   code: string,
     *   name: string,
     *   description: string|null,
     *   cost_price: float,
     *   sale_price: float,
     *   tax_id: int|null,
     *   status: bool
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

        $product_category = ProductCategory::query()
            ->visibleToUser($user)
            ->findOrFail($data['product_category_id']);

        $attributes = [
            'business_id'         => $business_id,
            'product_type_id'     => $data['product_type_id'],
            'product_category_id' => $data['product_category_id'],
            'unit_id'             => $data['unit_id'],
            'brand_id'            => $data['brand_id'],
            'code'                => $data['code'],
            'name'                => $data['name'],
            'description'         => $data['description'],
            'cost_price'          => $data['cost_price'],
            'sale_price'          => $data['sale_price'],
            'tax_id'              => $data['tax_id'],
            'track_inventory'     => (bool) $product_category->inventory,
            'status'              => $data['status'],
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

        abort_unless(
            ProductType::query()->visibleToUser($user)->whereKey($data['product_type_id'])->exists(),
            422
        );

        abort_unless(
            ProductCategory::query()->visibleToUser($user)->whereKey($data['product_category_id'])->exists(),
            422
        );

        abort_unless(
            Unit::query()->visibleToUser($user)->whereKey($data['unit_id'])->exists(),
            422
        );

        if ($data['brand_id']) {
            $brand_query = Brand::query()
                ->visibleToUser($user)
                ->forProductsCatalog()
                ->whereKey($data['brand_id'])
                ->whereHas('productCategories', fn ($category_query) => $category_query->whereKey($data['product_category_id']));

            abort_unless($brand_query->exists(), 422);
        }
    }
}
