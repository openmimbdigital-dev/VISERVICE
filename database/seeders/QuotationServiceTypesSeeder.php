<?php

namespace Database\Seeders;

use App\Models\QuotationServiceType;
use App\Support\CatalogLabelNormalizer;
use Illuminate\Database\Seeder;

class QuotationServiceTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Mantenimiento preventivo',
            'Mantenimiento correctivo',
            'Diagnóstico',
            'Reparación general',
            'Inspección técnica',
        ];

        foreach ($types as $name) {
            QuotationServiceType::query()->firstOrCreate(
                ['business_id' => null, 'name' => $name, 'general' => true],
                [
                    'label'  => CatalogLabelNormalizer::fromName($name),
                    'active' => true,
                ]
            );
        }

        $this->command?->info('Tipos de servicio de cotización: ' . count($types) . ' registros generales.');
    }
}
