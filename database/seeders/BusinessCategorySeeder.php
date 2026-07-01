<?php

namespace Database\Seeders;

use App\Models\BusinessCategory;
use Illuminate\Database\Seeder;

class BusinessCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Iglesia principal', 'active' => true],
            ['name' => 'Iglesia Hija', 'active' => true],
            ['name' => 'Campo blanco', 'active' => true],
        ];

        foreach ($categories as $category) {
            $label = BusinessCategory::normalizeLabel($category['name']);

            BusinessCategory::query()->updateOrCreate(
                ['label' => $label],
                [
                    'name'   => $category['name'],
                    'active' => $category['active'],
                ]
            );
        }

        $this->command->info('Categorías de negocio sincronizadas exitosamente.');
    }
}
