<?php

namespace App\Livewire\Admin\Settings\Events;

class EventsSettingsConfig
{
    public static function sections(): array
    {
        return [
            'event-categories' => [
                'title'               => 'Categorías de eventos',
                'description'         => 'Clasifica los eventos periódicos y eventuales de la iglesia.',
                'button_text'         => 'Gestionar categorías',
                'create_button_text'  => 'Nueva categoría',
                'route'               => 'admin.settings.events.event-categories.index',
                'create_route'        => 'admin.settings.events.event-categories.create',
                'permission'          => 'settings.event_categories.view',
                'datatable_component' => 'admin.settings.events.event-categories.datatable-event-categories',
                'card_bg'             => 'bg-violet-50/60 border-violet-100/80',
                'icon_bg'             => 'bg-violet-100',
                'icon_c'              => 'text-violet-600',
                'btn_class'           => 'btn-primary',
                'icon'                => 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z',
            ],
            'attendee-types' => [
                'title'               => 'Tipos de asistente',
                'description'         => 'Define los grupos de asistentes y sus rangos mínimo y máximo.',
                'button_text'         => 'Gestionar tipos',
                'create_button_text'  => 'Nuevo tipo de asistente',
                'route'               => 'admin.settings.events.attendee-types.index',
                'create_route'        => 'admin.settings.events.attendee-types.create',
                'permission'          => 'settings.attendee_types.view',
                'datatable_component' => 'admin.settings.events.attendee-types.datatable-attendee-types',
                'card_bg'             => 'bg-sky-50/60 border-sky-100/80',
                'icon_bg'             => 'bg-sky-100',
                'icon_c'              => 'text-sky-600',
                'btn_class'           => 'btn-primary',
                'icon'                => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            ],
        ];
    }

    public static function sectionOrFail(string $key): array
    {
        $section = static::sections()[$key] ?? null;

        abort_unless($section, 404);

        return $section + [
            'index_route' => 'admin.settings.events.index',
        ];
    }
}
