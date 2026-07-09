<?php

namespace App\Livewire\Admin\Settings\Catalog;

class CatalogProductsSettingsConfig
{
    /**
     * Secciones disponibles en configuración de productos del catálogo.
     */
    public static function sections(): array
    {
        return [
            'types' => [
                'key'                  => 'types',
                'title'                => 'Tipos de producto',
                'description'          => 'Clasificación de productos y servicios (repuesto, insumo, servicio, etc.).',
                'button_text'          => 'Gestionar tipos',
                'create_button_text'   => 'Nuevo tipo',
                'route'                => 'admin.settings.catalog-products.item-types.index',
                'create_route'         => 'admin.settings.catalog-products.item-types.create',
                'permission'           => 'settings.item_types.view',
                'datatable_component'  => 'admin.settings.catalog.item-types.datatable-item-types',
                'card_bg'              => 'bg-violet-50/60 border-violet-100/80',
                'icon_bg'              => 'bg-violet-100',
                'icon_c'               => 'text-violet-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z',
            ],
            'categories' => [
                'key'                  => 'categories',
                'title'                => 'Categorías',
                'description'          => 'Agrupación de productos por categoría. Define si la categoría es cuantificable en inventario.',
                'button_text'          => 'Gestionar categorías',
                'create_button_text'   => 'Nueva categoría',
                'route'                => 'admin.settings.catalog-products.item-categories.index',
                'create_route'         => 'admin.settings.catalog-products.item-categories.create',
                'permission'           => 'settings.item_categories.view',
                'datatable_component'  => 'admin.settings.catalog.item-categories.datatable-item-categories',
                'card_bg'              => 'bg-indigo-50/60 border-indigo-100/80',
                'icon_bg'              => 'bg-indigo-100',
                'icon_c'               => 'text-indigo-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
            ],
            'units' => [
                'key'                  => 'units',
                'title'                => 'Unidades de medida',
                'description'          => 'Unidades para cuantificar productos (unidad, litro, metro, kilogramo, etc.).',
                'button_text'          => 'Gestionar unidades',
                'create_button_text'   => 'Nueva unidad',
                'route'                => 'admin.settings.catalog-products.units.index',
                'create_route'         => 'admin.settings.catalog-products.units.create',
                'permission'           => 'settings.units.view',
                'datatable_component'  => 'admin.settings.catalog.units.datatable-units',
                'card_bg'              => 'bg-sky-50/60 border-sky-100/80',
                'icon_bg'              => 'bg-sky-100',
                'icon_c'               => 'text-sky-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3',
            ],
            'brands' => [
                'key'                  => 'brands',
                'title'                => 'Marcas',
                'description'          => 'Marcas de productos y servicios. Asócialas a las categorías del catálogo.',
                'button_text'          => 'Gestionar marcas',
                'create_button_text'   => 'Nueva marca',
                'route'                => 'admin.settings.catalog-products.brands.index',
                'create_route'         => 'admin.settings.catalog-products.brands.create',
                'permission'           => 'settings.brands.view',
                'datatable_component'  => 'admin.settings.catalog.brands.datatable-catalog-brands',
                'card_bg'              => 'bg-amber-50/60 border-amber-100/80',
                'icon_bg'              => 'bg-amber-100',
                'icon_c'               => 'text-amber-600',
                'btn_class'            => 'btn-primary',
                'icon'                 => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z',
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
            'index_route' => 'admin.settings.catalog-products.index',
        ];
    }
}
