<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::query()->where('slug', 'transportes-transad')->first();

        if (! $business) {
            $this->command?->warn('ProductsSeeder: no se encontró el negocio transportes-transad.');

            return;
        }

        $type_producto = ProductType::query()->whereNull('business_id')->where('name', 'Producto')->first();
        $type_servicio = ProductType::query()->whereNull('business_id')->where('name', 'Servicio')->first();

        $cat_mano_obra = ProductCategory::query()->whereNull('business_id')->where('name', 'Mano de Obra')->first();
        $cat_repuestos = ProductCategory::query()->whereNull('business_id')->where('name', 'Repuestos')->first();
        $cat_lubricantes = ProductCategory::query()->whereNull('business_id')->where('name', 'Lubricantes y fluidos')->first();

        $unit_und   = Unit::query()->whereNull('business_id')->where('symbol', 'und')->first();
        $unit_galon = Unit::query()->whereNull('business_id')->where('symbol', 'gal')->first();
        $unit_cart  = Unit::query()->whereNull('business_id')->where('symbol', 'cart')->first();

        if (! $type_producto || ! $type_servicio || ! $cat_mano_obra || ! $cat_repuestos || ! $cat_lubricantes || ! $unit_und) {
            $this->command?->warn('ProductsSeeder: ejecuta ProductCatalogSeeder antes.');

            return;
        }

        $brand_donaldson = $this->seedBrand('Donaldson', [$cat_repuestos?->id]);
        $brand_gates     = $this->seedBrand('Gates', [$cat_repuestos?->id]);
        $brand_shell     = $this->seedBrand('Shell', [$cat_lubricantes?->id]);

        $products = [
            // Mano de obra — servicios
            [
                'code' => 'SRV-MANT-PREV',
                'name' => 'Servicio de mantenimiento preventivo',
                'product_type_id' => $type_servicio->id,
                'product_category_id' => $cat_mano_obra->id,
                'unit_id' => $unit_und->id,
                'brand_id' => null,
                'track_inventory' => false,
                'sale_price' => 85000,
                'cost_price' => 0,
            ],
            [
                'code' => 'SRV-DIAG-ELEC',
                'name' => 'Diagnóstico electrónico',
                'product_type_id' => $type_servicio->id,
                'product_category_id' => $cat_mano_obra->id,
                'unit_id' => $unit_und->id,
                'brand_id' => null,
                'track_inventory' => false,
                'sale_price' => 120000,
                'cost_price' => 0,
            ],
            [
                'code' => 'SRV-AJ-FREN',
                'name' => 'Ajuste de frenos',
                'product_type_id' => $type_servicio->id,
                'product_category_id' => $cat_mano_obra->id,
                'unit_id' => $unit_und->id,
                'brand_id' => null,
                'track_inventory' => false,
                'sale_price' => 65000,
                'cost_price' => 0,
            ],
            // Repuestos — productos
            [
                'code' => 'P550588',
                'name' => 'Filtro de aceite Donaldson P550588',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_repuestos->id,
                'unit_id' => $unit_und->id,
                'brand_id' => $brand_donaldson?->id,
                'track_inventory' => true,
                'sale_price' => 45000,
                'cost_price' => 32000,
            ],
            [
                'code' => 'P628182',
                'name' => 'Filtro de aire Donaldson P628182',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_repuestos->id,
                'unit_id' => $unit_und->id,
                'brand_id' => $brand_donaldson?->id,
                'track_inventory' => true,
                'sale_price' => 78000,
                'cost_price' => 55000,
            ],
            [
                'code' => 'P550926',
                'name' => 'Filtro de combustible Donaldson P550926',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_repuestos->id,
                'unit_id' => $unit_und->id,
                'brand_id' => $brand_donaldson?->id,
                'track_inventory' => true,
                'sale_price' => 52000,
                'cost_price' => 38000,
            ],
            [
                'code' => 'REP-PAST-FRE-DEL',
                'name' => 'Pastillas de freno juego (Delanteras)',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_repuestos->id,
                'unit_id' => $unit_und->id,
                'brand_id' => null,
                'track_inventory' => true,
                'sale_price' => 185000,
                'cost_price' => 140000,
            ],
            [
                'code' => 'K081021',
                'name' => 'Kit de bandas Gates K081021',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_repuestos->id,
                'unit_id' => $unit_und->id,
                'brand_id' => $brand_gates?->id,
                'track_inventory' => true,
                'sale_price' => 95000,
                'cost_price' => 72000,
            ],
            // Lubricantes y fluidos — productos
            [
                'code' => 'LUB-ACE-RIMULA',
                'name' => 'Aceite de motor Shell Rimula R6 15W-40 (Galón)',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_lubricantes->id,
                'unit_id' => $unit_galon?->id ?? $unit_und->id,
                'brand_id' => $brand_shell?->id,
                'track_inventory' => true,
                'sale_price' => 125000,
                'cost_price' => 98000,
            ],
            [
                'code' => 'LUB-GRA-GADUS',
                'name' => 'Grasa multipropósito Shell Gadus S2 V220 (Cartucho)',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_lubricantes->id,
                'unit_id' => $unit_cart?->id ?? $unit_und->id,
                'brand_id' => $brand_shell?->id,
                'track_inventory' => true,
                'sale_price' => 42000,
                'cost_price' => 31000,
            ],
            [
                'code' => 'LUB-REF-SHELL',
                'name' => 'Líquido refrigerante Shell (Galón)',
                'product_type_id' => $type_producto->id,
                'product_category_id' => $cat_lubricantes->id,
                'unit_id' => $unit_galon?->id ?? $unit_und->id,
                'brand_id' => $brand_shell?->id,
                'track_inventory' => true,
                'sale_price' => 38000,
                'cost_price' => 28000,
            ],
        ];

        $created = 0;

        foreach ($products as $data) {
            Product::query()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'code'        => $data['code'],
                ],
                [
                    'product_type_id'     => $data['product_type_id'],
                    'product_category_id' => $data['product_category_id'],
                    'unit_id'          => $data['unit_id'],
                    'brand_id'         => $data['brand_id'],
                    'name'             => $data['name'],
                    'description'      => null,
                    'cost_price'       => $data['cost_price'],
                    'sale_price'       => $data['sale_price'],
                    'tax_id'           => null,
                    'track_inventory'  => $data['track_inventory'],
                    'status'           => true,
                ]
            );

            $created++;
        }

        $this->command?->info("Products: {$created} productos/servicios sembrados para {$business->name}.");
    }

    /** @param  list<int|null>  $category_ids */
    private function seedBrand(string $name, array $category_ids): ?Brand
    {
        $brand = Brand::withTrashed()->firstOrNew([
            'business_id' => null,
            'name'        => $name,
        ]);

        if ($brand->trashed()) {
            $brand->restore();
        }

        $brand->fill([
            'label'   => CatalogLabelNormalizer::fromName($name),
            'active'  => true,
            'general' => true,
        ])->save();

        $ids = array_values(array_filter($category_ids));

        if ($ids !== []) {
            $brand->productCategories()->syncWithoutDetaching($ids);
        }

        \App\Models\BrandUsage::query()->firstOrCreate([
            'brand_id' => $brand->id,
            'type'     => \App\Enums\BrandUsageType::Products,
        ]);

        return $brand;
    }
}
