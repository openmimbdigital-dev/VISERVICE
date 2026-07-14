<?php

namespace App\Actions\Settings\Catalog;

use App\Enums\BrandUsageType;
use App\Models\Brand;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCatalogBrandAction
{
    use AsAction;

    public function handle(int $brand_id): void
    {
        abort_unless(auth()->user()?->can('settings.brands.delete'), 403);

        $brand = Brand::query()->visibleToUser()->findOrFail($brand_id);

        abort_unless($brand->isEditableBy(), 403);

        abort_unless(
            $brand->brandUsages()->where('type', BrandUsageType::Products)->exists(),
            403,
            'Esta marca no pertenece al catálogo de productos.'
        );

        if ($brand->hasEquipmentUsage()) {
            abort(422, 'No se puede eliminar: la marca también está asociada a equipos.');
        }

        abort_unless($brand->canDelete(), 403);

        $brand->delete();
    }
}
