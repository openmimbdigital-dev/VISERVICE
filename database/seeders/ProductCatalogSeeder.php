<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['Producto', 'Servicio'];

        foreach ($types as $name) {
            $this->seedCatalogRecord(
                ProductType::class,
                ['business_id' => null, 'name' => $name],
                [
                    'label'   => CatalogLabelNormalizer::fromName($name),
                    'active'  => true,
                    'general' => true,
                ]
            );
        }

        $categories = [
            ['name' => 'Mano de Obra', 'inventory' => false],
            ['name' => 'Repuestos', 'inventory' => true],
            ['name' => 'Lubricantes y fluidos', 'inventory' => true],
        ];

        foreach ($categories as $category) {
            $this->seedCatalogRecord(
                ProductCategory::class,
                ['business_id' => null, 'name' => $category['name']],
                [
                    'label'     => CatalogLabelNormalizer::fromName($category['name']),
                    'inventory' => $category['inventory'],
                    'active'    => true,
                    'general'   => true,
                ]
            );
        }

        $units = [
            ['name' => 'Kilogramo', 'symbol' => 'kg'],
            ['name' => 'Gramo', 'symbol' => 'g'],
            ['name' => 'Miligramo', 'symbol' => 'mg'],
            ['name' => 'Litro', 'symbol' => 'L'],
            ['name' => 'Mililitro', 'symbol' => 'mL'],
            ['name' => 'Metro', 'symbol' => 'm'],
            ['name' => 'Centímetro', 'symbol' => 'cm'],
            ['name' => 'Milímetro', 'symbol' => 'mm'],
            ['name' => 'Unidad', 'symbol' => 'und'],
            ['name' => 'Galón', 'symbol' => 'gal'],
            ['name' => 'Cartucho', 'symbol' => 'cart'],
        ];

        foreach ($units as $unit) {
            $this->seedCatalogRecord(
                Unit::class,
                ['business_id' => null, 'name' => $unit['name']],
                [
                    'label'   => CatalogLabelNormalizer::fromName($unit['name']),
                    'symbol'  => $unit['symbol'],
                    'active'  => true,
                    'general' => true,
                ]
            );
        }

        $this->command?->info('Catálogo de productos: 2 tipos, 3 categorías y 11 unidades generales.');
    }

    /**
     * @param  class-string<Model>  $model_class
     */
    private function seedCatalogRecord(string $model_class, array $keys, array $values): void
    {
        /** @var Model $record */
        $record = $model_class::withTrashed()->firstOrNew($keys);

        if (method_exists($record, 'trashed') && $record->trashed()) {
            $record->restore();
        }

        $record->fill($values)->save();
    }
}
