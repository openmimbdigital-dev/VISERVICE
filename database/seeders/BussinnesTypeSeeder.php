<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;

class BussinnesTypeSeeder extends Seeder
{
    public function run(): void
    {
        $business_types = [
            ['name' => 'Taller', 'status' => true],
            ['name' => 'Iglesia', 'status' => true],
            ['name' => 'Centro Educativo', 'status' => true],
        ];

        $active_labels = [];

        foreach ($business_types as $type) {
            $label = BusinessType::normalizeLabel($type['name']);
            $active_labels[] = $label;

            BusinessType::query()->updateOrCreate(
                ['label' => $label],
                [
                    'name'   => $type['name'],
                    'status' => $type['status'],
                ]
            );
        }

        BusinessType::query()
            ->whereNotIn('label', $active_labels)
            ->update(['status' => false]);

        $this->command->info('Tipos de negocio sincronizados exitosamente.');
    }
}
