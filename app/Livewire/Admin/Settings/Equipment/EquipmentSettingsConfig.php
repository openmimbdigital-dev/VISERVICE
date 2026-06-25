<?php

namespace App\Livewire\Admin\Settings\Equipment;

class EquipmentSettingsConfig
{
    /**
     * Secciones disponibles en configuración de equipos.
     * Agregar una nueva entrada aquí para escalar el módulo.
     */
    public static function sections(): array
    {
        return [
            'brands' => [
                'key'                  => 'brands',
                'title'                => 'Marcas',
                'description'          => 'Catálogo de marcas de equipos. Incluye registros del negocio y marcas generales del sistema.',
                'button_text'          => 'Gestionar marcas',
                'create_button_text'   => 'Nueva marca',
                'route'                => 'admin.settings.equipment.brands',
                'datatable_component'  => 'admin.settings.equipment.brands.datatable-brands',
                'card_bg'              => 'bg-indigo-50/60 border-indigo-100/80',
                'icon_bg'              => 'bg-indigo-100',
                'icon_c'               => 'text-indigo-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
            ],
            'models' => [
                'key'         => 'models',
                'title'       => 'Modelos',
                'description' => 'Catálogo de modelos asociados a las marcas de equipos del taller.',
                'button_text' => 'Gestionar modelos',
                'card_bg'     => 'bg-sky-50/60 border-sky-100/80',
                'icon_bg'     => 'bg-sky-100',
                'icon_c'      => 'text-sky-600',
                'btn_class'   => 'btn-primary',
                'icon'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            ],
        ];
    }

    public static function section(string $key): ?array
    {
        return static::sections()[$key] ?? null;
    }

    public static function sectionOrFail(string $key): array
    {
        $section = static::section($key);

        if (! $section) {
            abort(404);
        }

        return $section + [
            'section_route' => 'admin.settings.equipment.section',
            'index_route'   => 'admin.settings.equipment.index',
        ];
    }
}
