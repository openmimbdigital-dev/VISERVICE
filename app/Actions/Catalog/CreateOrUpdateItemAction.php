<?php

namespace App\Actions\Catalog;

use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemType;
use App\Models\Unit;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateItemAction
{
    use AsAction;

    /**
     * @param  array{
     *   item_type_id: int,
     *   item_category_id: int,
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
    public function handle(?int $item_id, array $data): Item
    {
        abort_unless(
            auth()->user()->can($item_id ? 'catalog.items.edit' : 'catalog.items.create'),
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

        $item_category = ItemCategory::query()
            ->visibleToUser($user)
            ->findOrFail($data['item_category_id']);

        $attributes = [
            'business_id'      => $business_id,
            'item_type_id'     => $data['item_type_id'],
            'item_category_id' => $data['item_category_id'],
            'unit_id'          => $data['unit_id'],
            'brand_id'         => $data['brand_id'],
            'code'             => $data['code'],
            'name'             => $data['name'],
            'description'      => $data['description'],
            'cost_price'       => $data['cost_price'],
            'sale_price'       => $data['sale_price'],
            'tax_id'           => $data['tax_id'],
            'track_inventory'  => (bool) $item_category->inventory,
            'status'           => $data['status'],
        ];

        if ($item_id) {
            $item = Item::query()->forAuthUser($user)->findOrFail($item_id);
            abort_unless($item->isEditableBy($user), 403);
            abort_unless((int) $item->business_id === (int) $business_id, 403);

            $item->update($attributes);

            return $item->fresh();
        }

        return Item::create($attributes);
    }

    private function assertRelationsBelongToBusiness(int $business_id, array $data): void
    {
        $user = auth()->user();

        abort_unless(
            ItemType::query()->visibleToUser($user)->whereKey($data['item_type_id'])->exists(),
            422
        );

        abort_unless(
            ItemCategory::query()->visibleToUser($user)->whereKey($data['item_category_id'])->exists(),
            422
        );

        abort_unless(
            Unit::query()->visibleToUser($user)->whereKey($data['unit_id'])->exists(),
            422
        );

        if ($data['brand_id']) {
            $brand_query = Brand::query()
                ->visibleToUser($user)
                ->forItemsCatalog()
                ->whereKey($data['brand_id'])
                ->whereHas('itemCategories', fn ($category_query) => $category_query->whereKey($data['item_category_id']));

            abort_unless($brand_query->exists(), 422);
        }
    }
}
