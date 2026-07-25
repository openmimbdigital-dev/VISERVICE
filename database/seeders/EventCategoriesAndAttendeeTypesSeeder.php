<?php

namespace Database\Seeders;

use App\Enums\EventCategoryType;
use App\Models\AttendeeType;
use App\Models\Business;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategoriesAndAttendeeTypesSeeder extends Seeder
{
    private const BUSINESS_SLUG = 'centro-de-fe-y-esperanza-sampues';

    public function run(): void
    {
        $business = Business::query()->where('slug', self::BUSINESS_SLUG)->first();

        if (! $business) {
            $this->command?->warn('Categorías de eventos: no se encontró el Centro de Fe y Esperanza Sampues.');

            return;
        }

        $this->seedEventCategories($business);
        $this->seedAttendeeTypes($business);
    }

    private function seedEventCategories(Business $business): void
    {
        $categories = [
            [
                'name'        => 'Culto dominical',
                'description' => 'Reunión general de adoración todos los domingos.',
                'type'        => EventCategoryType::Periodic,
            ],
            [
                'name'        => 'Reunión de oración',
                'description' => 'Encuentro semanal de oración e intercesión.',
                'type'        => EventCategoryType::Periodic,
            ],
            [
                'name'        => 'Escuela dominical',
                'description' => 'Clases de formación bíblica por edades.',
                'type'        => EventCategoryType::Periodic,
            ],
            [
                'name'        => 'Vigilia',
                'description' => 'Noche de oración y adoración extendida.',
                'type'        => EventCategoryType::Occasional,
            ],
            [
                'name'        => 'Conferencia anual',
                'description' => 'Evento especial con invitados y ministraciones.',
                'type'        => EventCategoryType::Occasional,
            ],
            [
                'name'        => 'Bautismos',
                'description' => 'Ceremonia de bautismos en agua.',
                'type'        => EventCategoryType::Occasional,
            ],
        ];

        $created = 0;

        foreach ($categories as $category) {
            EventCategory::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name'        => $category['name'],
                ],
                [
                    'description' => $category['description'],
                    'type'        => $category['type'],
                    'deleted_at'  => null,
                ]
            );

            $created++;
        }

        $this->command?->info("Categorías de eventos demo: {$created} registros.");
    }

    private function seedAttendeeTypes(Business $business): void
    {
        $types = [
            [
                'name'          => 'Niños',
                'description'   => 'Asistentes de primera infancia y niñez.',
                'minimum_range' => 0,
                'maximum_range' => 11,
            ],
            [
                'name'          => 'Adolescentes',
                'description'   => 'Asistentes en etapa de adolescencia.',
                'minimum_range' => 12,
                'maximum_range' => 17,
            ],
            [
                'name'          => 'Jóvenes',
                'description'   => 'Asistentes jóvenes y universitarios.',
                'minimum_range' => 18,
                'maximum_range' => 30,
            ],
            [
                'name'          => 'Adultos',
                'description'   => 'Asistentes adultos.',
                'minimum_range' => 31,
                'maximum_range' => 59,
            ],
            [
                'name'          => 'Adultos mayores',
                'description'   => 'Asistentes de la tercera edad.',
                'minimum_range' => 60,
                'maximum_range' => 120,
            ],
        ];

        $created = 0;

        foreach ($types as $type) {
            AttendeeType::withTrashed()->updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name'        => $type['name'],
                ],
                [
                    'description'   => $type['description'],
                    'minimum_range' => $type['minimum_range'],
                    'maximum_range' => $type['maximum_range'],
                    'deleted_at'    => null,
                ]
            );

            $created++;
        }

        $this->command?->info("Tipos de asistente demo: {$created} registros.");
    }
}
