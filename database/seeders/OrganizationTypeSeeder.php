<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $organization_types = [
            ['name' => 'Taller', 'status' => true],
            ['name' => 'Iglesia', 'status' => true],
            ['name' => 'Centro Educativo', 'status' => true],
        ];

        $active_labels = [];

        foreach ($organization_types as $type) {
            $label = OrganizationType::normalizeLabel($type['name']);
            $active_labels[] = $label;

            OrganizationType::query()->updateOrCreate(
                ['label' => $label],
                [
                    'name'   => $type['name'],
                    'status' => $type['status'],
                ]
            );
        }

        OrganizationType::query()
            ->whereNotIn('label', $active_labels)
            ->update(['status' => false]);

        $this->command->info('Tipos de organización sincronizados exitosamente.');
    }
}
