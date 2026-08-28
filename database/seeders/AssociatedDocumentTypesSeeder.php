<?php

namespace Database\Seeders;

use App\Models\AssociatedDocumentType;
use App\Models\Business;
use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class AssociatedDocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $taller = OrganizationType::query()->where('label', 'taller')->first();

        if (! $taller) {
            $this->command?->warn('Documentos asociados OT: no se encontró el tipo de organización "taller".');

            return;
        }

        $documents = [
            ['name' => 'Cédula del cliente', 'document_send' => false],
            ['name' => 'Tarjeta de propiedad', 'document_send' => false],
            ['name' => 'SOAT vigente', 'document_send' => true],
            ['name' => 'Revisión técnico-mecánica', 'document_send' => true],
            ['name' => 'Póliza de seguro', 'document_send' => false],
        ];

        $created = 0;

        Business::query()
            ->where('organization_type_id', $taller->id)
            ->orderBy('id')
            ->each(function (Business $business) use ($documents, &$created) {
                foreach ($documents as $document) {
                    $type = AssociatedDocumentType::query()->firstOrCreate(
                        [
                            'business_id' => $business->id,
                            'key'         => AssociatedDocumentType::makeKeyFromName($document['name']),
                        ],
                        [
                            'name'          => $document['name'],
                            'active'        => true,
                            'document_send' => $document['document_send'],
                        ]
                    );

                    if ($type->wasRecentlyCreated) {
                        $created++;
                    }
                }
            });

        $this->command?->info("Documentos asociados OT: {$created} tipos creados.");
    }
}
