<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\GeneralConfig;
use Illuminate\Database\Seeder;

class AssociatedDocumentOtSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            'Cédula del cliente',
            'Tarjeta de propiedad',
            'SOAT vigente',
            'Revisión técnico-mecánica',
            'Póliza de seguro',
        ];

        $created = 0;

        Business::query()->orderBy('id')->each(function (Business $business) use ($documents, &$created) {
            foreach ($documents as $value) {
                $label = GeneralConfig::makeLabelFromValue($value);

                $config = $business->generalConfigs()->firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'key'         => GeneralConfig::KEY_ASSOCIATE_DOCUMENT_OT,
                        'label'       => $label,
                    ],
                    [
                        'value' => $value,
                    ]
                );

                if ($config->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        $this->command?->info("Documentos asociados OT: {$created} registros creados.");
    }
}
