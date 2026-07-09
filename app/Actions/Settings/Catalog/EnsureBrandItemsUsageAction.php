<?php

namespace App\Actions\Settings\Catalog;

use App\Enums\BrandUsageType;
use App\Models\Brand;
use App\Models\BrandUsage;
use Lorisleiva\Actions\Concerns\AsAction;

class EnsureBrandItemsUsageAction
{
    use AsAction;

    public function handle(Brand $brand): void
    {
        BrandUsage::query()->firstOrCreate([
            'brand_id' => $brand->id,
            'type'     => BrandUsageType::Items,
        ]);
    }
}
