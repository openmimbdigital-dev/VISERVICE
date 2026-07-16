<?php

namespace App\Actions\Settings\Catalog;

use App\Models\ProductType;
use App\Support\CatalogLabelNormalizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateProductTypeAction
{
    use AsAction;

    /** @param  array{name: string, active: bool}  $data */
    public function handle(?int $product_type_id, array $data): ProductType
    {
        abort_unless(
            auth()->user()->can($product_type_id ? 'settings.product_types.edit' : 'settings.product_types.create'),
            403
        );

        $user           = auth()->user();
        $is_super_admin = $user->hasRole('superAdmin');

        $attributes = [
            'name'   => $data['name'],
            'label'  => CatalogLabelNormalizer::fromName($data['name']),
            'active' => $data['active'],
        ];

        if ($product_type_id) {
            $product_type = ProductType::query()->visibleToUser($user)->findOrFail($product_type_id);
            abort_unless($product_type->isEditableBy($user), 403);

            $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
            $attributes['general']     = $is_super_admin;

            $product_type->update($attributes);

            return $product_type->fresh();
        }

        $attributes['business_id'] = $is_super_admin ? null : $user->business_id;
        $attributes['general']     = $is_super_admin;

        return ProductType::create($attributes);
    }
}
