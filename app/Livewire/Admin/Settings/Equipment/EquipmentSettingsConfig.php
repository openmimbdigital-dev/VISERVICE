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
            'types' => [
                'key'                  => 'types',
                'title'                => 'Tipos de equipo',
                'description'          => 'Clasificación de equipos atendidos en el taller (motocicleta, automóvil, maquinaria, etc.).',
                'button_text'          => 'Gestionar tipos',
                'create_button_text'   => 'Nuevo tipo',
                'route'                => 'admin.settings.equipment.types',
                'datatable_component'  => 'admin.settings.equipment.types.datatable-equipment-types',
                'card_bg'              => 'bg-violet-50/60 border-violet-100/80',
                'icon_bg'              => 'bg-violet-100',
                'icon_c'               => 'text-violet-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z',
            ],
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
                'key'                  => 'models',
                'title'                => 'Modelos',
                'description'          => 'Catálogo de modelos asociados a las marcas de equipos del taller.',
                'button_text'          => 'Gestionar modelos',
                'create_button_text'   => 'Nuevo modelo',
                'route'                => 'admin.settings.equipment.models',
                'datatable_component'  => 'admin.settings.equipment.models.datatable-equipment-models',
                'card_bg'              => 'bg-sky-50/60 border-sky-100/80',
                'icon_bg'              => 'bg-sky-100',
                'icon_c'               => 'text-sky-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
            ],
            'attributes' => [
                'key'                  => 'attributes',
                'title'                => 'Atributos',
                'description'          => 'Campos personalizados por tipo de producto (select, texto, número, etc.) para caracterizar equipos y servicios.',
                'button_text'          => 'Gestionar atributos',
                'create_button_text'   => 'Nuevo atributo',
                'card_bg'              => 'bg-emerald-50/60 border-emerald-100/80',
                'icon_bg'              => 'bg-emerald-100',
                'icon_c'               => 'text-emerald-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
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
