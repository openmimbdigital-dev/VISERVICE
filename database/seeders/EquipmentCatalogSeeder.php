<?php

namespace Database\Seeders;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use App\Models\Brand;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class EquipmentCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Toyota',
            'Honda',
            'Yamaha',
            'Ford',
            'Chevrolet',
        ];

        $types = [
            'Motocicleta',
            'Automóvil',
            'Camión',
            'Tractocamión',
            'Aire acondicionado',
            'Bicicleta',
            'Maquinaria agrícola',
        ];

        $models = [
            ['brand' => 'Toyota', 'name' => 'Corolla'],
            ['brand' => 'Honda', 'name' => 'Civic'],
            ['brand' => 'Yamaha', 'name' => 'FZ150'],
            ['brand' => 'Ford', 'name' => 'Ranger'],
            ['brand' => 'Chevrolet', 'name' => 'Spark'],
        ];

        foreach ($brands as $name) {
            $this->seedCatalogRecord(
                Brand::class,
                ['business_id' => null, 'name' => $name],
                [
                    'label'   => CreateOrUpdateBrandAction::normalizeLabel($name),
                    'active'  => true,
                    'general' => true,
                ]
            );
        }

        foreach ($types as $name) {
            $this->seedCatalogRecord(
                EquipmentType::class,
                ['business_id' => null, 'name' => $name],
                [
                    'label'   => CreateOrUpdateBrandAction::normalizeLabel($name),
                    'active'  => true,
                    'general' => true,
                ]
            );
        }

        foreach ($models as $model) {
            $brand = Brand::query()
                ->whereNull('business_id')
                ->where('name', $model['brand'])
                ->first();

            if (! $brand) {
                continue;
            }

            $this->seedCatalogRecord(
                EquipmentModel::class,
                [
                    'business_id' => null,
                    'brand_id'    => $brand->id,
                    'name'        => $model['name'],
                ],
                [
                    'label'   => CreateOrUpdateBrandAction::normalizeLabel($model['name']),
                    'active'  => true,
                    'general' => true,
                ]
            );
        }

        $this->command->info('Catálogo de equipos: 5 marcas, 7 tipos y 5 modelos generales.');
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
