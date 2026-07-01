<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\MenuSection;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'slug'             => 'usuarios',
                'name'             => 'Usuarios',
                'icon_svg_path'    => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'icon_color_class' => 'text-indigo-400',
                'route_patterns'   => ['admin.users.*', 'admin.roles.*'],
                'behavior'         => 'collapsible',
                'permission'       => 'users.view',
                'sort_order'       => 10,
                'items'            => [
                    [
                        'name'                  => 'Ver usuarios',
                        'route_name'            => 'admin.users.index',
                        'active_route_pattern'  => 'admin.users.*',
                        'icon_svg_path'         => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                        'sort_order'            => 10,
                    ],
                    [
                        'name'                  => 'Roles y permisos',
                        'route_name'            => 'admin.roles.index',
                        'active_route_pattern'  => 'admin.roles.*',
                        'icon_svg_path'         => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        'role'                  => 'superAdmin',
                        'sort_order'            => 20,
                    ],
                ],
            ],
            [
                'slug'             => 'suscripciones',
                'name'             => 'Suscripciones',
                'icon_svg_path'    => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                'icon_color_class' => 'text-indigo-400',
                'route_patterns'   => [
                    'admin.subscriptions.*',
                    'admin.payments.*',
                    'admin.bank-accounts.*',
                    'admin.banks.*',
                    'admin.finance.*',
                ],
                'behavior'         => 'collapsible',
                'role'             => 'superAdmin',
                'sort_order'       => 20,
                'items'            => [
                    ['name' => 'Finanzas', 'route_name' => 'admin.finance.index', 'active_route_pattern' => 'admin.finance.*', 'icon_svg_path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'sort_order' => 10],
                    ['name' => 'Ver suscripciones', 'route_name' => 'admin.subscriptions.index', 'active_route_pattern' => 'admin.subscriptions.index', 'icon_svg_path' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'sort_order' => 20],
                    ['name' => 'Planes', 'route_name' => 'admin.subscriptions.plans.index', 'active_route_pattern' => 'admin.subscriptions.plans.*', 'icon_svg_path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'sort_order' => 30],
                    ['name' => 'Pagos pendientes', 'route_name' => 'admin.payments.index', 'active_route_pattern' => 'admin.payments.*', 'icon_svg_path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'badge_key' => 'pending_subscription_payments', 'sort_order' => 40],
                    ['name' => 'Cuentas bancarias', 'route_name' => 'admin.bank-accounts.index', 'active_route_pattern' => 'admin.bank-accounts.*', 'icon_svg_path' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z', 'sort_order' => 50],
                    ['name' => 'Bancos', 'route_name' => 'admin.banks.index', 'active_route_pattern' => 'admin.banks.*', 'icon_svg_path' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'sort_order' => 60],
                ],
            ],
            [
                'slug'             => 'negocios',
                'name'             => 'Negocios',
                'icon_svg_path'    => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'icon_color_class' => 'text-indigo-400',
                'route_patterns'   => ['admin.businesses.*', 'admin.business-types.*', 'admin.organization-types.*'],
                'behavior'         => 'collapsible',
                'role'             => 'superAdmin',
                'sort_order'       => 25,
                'items'            => [
                    [
                        'name'                 => 'Ver negocios',
                        'route_name'           => 'admin.businesses.index',
                        'active_route_pattern' => 'admin.businesses.*',
                        'icon_svg_path'        => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                        'sort_order'           => 10,
                    ],
                    [
                        'name'                 => 'Tipos de negocio',
                        'route_name'           => 'admin.business-types.index',
                        'active_route_pattern' => 'admin.business-types.index',
                        'icon_svg_path'        => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
                        'sort_order'           => 20,
                    ],
                    [
                        'name'                 => 'Tipos de organización',
                        'route_name'           => 'admin.organization-types.index',
                        'active_route_pattern' => 'admin.organization-types.*',
                        'icon_svg_path'        => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                        'sort_order'           => 25,
                    ],
                    [
                        'name'                 => 'Acceso por tipo',
                        'route_name'           => 'admin.business-types.access',
                        'active_route_pattern' => 'admin.business-types.access',
                        'icon_svg_path'        => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        'sort_order'           => 30,
                    ],
                ],
            ],
            [
                'slug'             => 'mi-negocio',
                'name'             => 'Mi Negocio',
                'icon_svg_path'    => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'icon_color_class' => 'text-violet-400',
                'route_patterns'   => ['comercio.business.edit'],
                'behavior'         => 'single_link',
                'route_name'       => 'comercio.business.edit',
                'role'             => 'Comercio',
                'sort_order'       => 30,
                'items'            => [],
            ],
            [
                'slug'             => 'taller',
                'name'             => 'Taller',
                'icon_svg_path'    => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z | M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'icon_color_class' => 'text-emerald-400',
                'route_patterns'   => [
                    'admin.workshop.clients.*',
                    'admin.workshop.equipment.*',
                    'admin.workshop.quotations.*',
                    'admin.workshop.work-orders.*',
                ],
                'behavior'         => 'collapsible',
                'sort_order'       => 40,
                'items'            => [
                    ['name' => 'Clientes', 'route_name' => 'admin.workshop.clients.index', 'active_route_pattern' => 'admin.workshop.clients.*', 'icon_svg_path' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'permission' => 'workshop.clients.view', 'sort_order' => 10],
                    ['name' => 'Equipos', 'route_name' => 'admin.workshop.equipment.index', 'active_route_pattern' => 'admin.workshop.equipment.*', 'icon_svg_path' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z', 'permission' => 'workshop.equipment.view', 'sort_order' => 20],
                    ['name' => 'Cotizaciones', 'route_name' => 'admin.workshop.quotations.index', 'active_route_pattern' => 'admin.workshop.quotations.*', 'icon_svg_path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'sort_order' => 30],
                    ['name' => 'Órdenes de Trabajo', 'route_name' => 'admin.workshop.work-orders.index', 'active_route_pattern' => 'admin.workshop.work-orders.*', 'icon_svg_path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'sort_order' => 40],
                ],
            ],
            [
                'slug'             => 'catalogo',
                'name'             => 'Catálogo',
                'icon_svg_path'    => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                'icon_color_class' => 'text-amber-400',
                'route_patterns'   => ['admin.workshop.catalog.*'],
                'behavior'         => 'collapsible',
                'sort_order'       => 50,
                'items'            => [
                    ['name' => 'Servicios', 'route_name' => 'admin.workshop.catalog.services.index', 'active_route_pattern' => 'admin.workshop.catalog.services.*', 'icon_svg_path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'sort_order' => 10],
                    ['name' => 'Repuestos', 'route_name' => 'admin.workshop.catalog.spare-parts.index', 'active_route_pattern' => 'admin.workshop.catalog.spare-parts.*', 'icon_svg_path' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'sort_order' => 20],
                ],
            ],
            [
                'slug'             => 'configuracion',
                'name'             => 'Configuración',
                'icon_svg_path'    => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z | M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'icon_color_class' => 'text-sky-400',
                'route_patterns'   => ['admin.settings.*'],
                'behavior'         => 'collapsible',
                'permission'       => 'settings.view',
                'sort_order'       => 60,
                'items'            => [
                    ['name' => 'Equipos', 'route_name' => 'admin.settings.equipment.index', 'active_route_pattern' => 'admin.settings.equipment.*', 'icon_svg_path' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z', 'sort_order' => 10],
                ],
            ],
        ];

        foreach ($sections as $section_data) {
            $items = $section_data['items'];
            unset($section_data['items']);

            $section = MenuSection::query()->updateOrCreate(
                ['slug' => $section_data['slug']],
                $section_data
            );

            foreach ($items as $item_data) {
                MenuItem::query()->updateOrCreate(
                    [
                        'menu_section_id' => $section->id,
                        'route_name'      => $item_data['route_name'],
                    ],
                    array_merge($item_data, ['menu_section_id' => $section->id, 'active' => true])
                );
            }
        }

        // Quitar ítem de negocios de Suscripciones si existía antes
        $subscriptions = MenuSection::query()->where('slug', 'suscripciones')->first();
        if ($subscriptions) {
            MenuItem::query()
                ->where('menu_section_id', $subscriptions->id)
                ->where('route_name', 'admin.businesses.index')
                ->delete();
        }
    }
}
