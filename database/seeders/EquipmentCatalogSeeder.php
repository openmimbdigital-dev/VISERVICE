<?php

namespace Database\Seeders;

use App\Actions\Settings\Equipment\CreateOrUpdateBrandAction;
use App\Enums\BrandUsageType;
use App\Models\Brand;
use App\Models\BrandUsage;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use Database\Seeders\Support\BrandEquipmentTypeSeeder;
use Database\Seeders\Support\EquipmentTypeBusinessSeeder;
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
            'LG',
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
            ['brand' => 'LG', 'name' => 'Dual Inverter 12.000 BTU'],
            ['brand' => 'LG', 'name' => 'Dual Inverter 18.000 BTU'],
            ['brand' => 'LG', 'name' => 'Art Cool 9.000 BTU'],
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

        $brand_usage_count = 0;

        Brand::query()
            ->whereNull('business_id')
            ->where('general', true)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->each(function (int $brand_id) use (&$brand_usage_count) {
                BrandUsage::query()->firstOrCreate([
                    'brand_id' => $brand_id,
                    'type'     => BrandUsageType::Equipment,
                ]);

                $brand_usage_count++;
            });

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

        $associated = EquipmentTypeBusinessSeeder::run();

        $brand_type_associations = BrandEquipmentTypeSeeder::run();

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

        $this->command->info('Catálogo de equipos: 6 marcas, 7 tipos y 8 modelos generales.');

        if ($associated > 0) {
            $this->command->info("Tipos de equipo: {$associated} asociación(es) con negocios activos.");
        }

        if ($brand_type_associations > 0) {
            $this->command->info("Marcas: {$brand_type_associations} asociación(es) con tipos de equipo.");
        }

        if ($brand_usage_count > 0) {
            $this->command->info("Marcas: {$brand_usage_count} registro(s) de uso (equipment).");
        }
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
