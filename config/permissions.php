<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permisos del Sistema VISERVICE
    |--------------------------------------------------------------------------
    */

    'modules' => [
        'users' => [
            'name' => 'Gestión de Usuarios',
            'permissions' => [
                'users.view' => 'Ver usuarios',
                'users.create' => 'Crear usuarios',
                'users.edit' => 'Editar usuarios',
                'users.delete' => 'Eliminar usuarios',
                'users.activate' => 'Activar usuarios',
                'users.deactivate' => 'Desactivar usuarios',
            ],
        ],
        'businesses' => [
            'name' => 'Gestión de Empresas',
            'permissions' => [
                'businesses.view' => 'Ver empresas',
                'businesses.create' => 'Crear empresas',
                'businesses.edit' => 'Editar empresas',
                'businesses.delete' => 'Eliminar empresas',
                'businesses.activate' => 'Activar empresas',
                'businesses.deactivate' => 'Desactivar empresas',
                'businesses.manage_addresses' => 'Gestionar direcciones',
                'businesses.manage_modules'   => 'Gestionar módulos del negocio',
            ],
        ],
        'business_types' => [
            'name' => 'Negocios — Tipos de negocio',
            'permissions' => [
                'business_types.view'   => 'Ver tipos de negocio',
                'business_types.create' => 'Crear tipos de negocio',
                'business_types.edit'   => 'Editar tipos de negocio',
                'business_types.delete' => 'Eliminar tipos de negocio',
            ],
        ],
        'organization_types' => [
            'name' => 'Negocios — Tipos de organización',
            'permissions' => [
                'organization_types.view'   => 'Ver tipos de organización',
                'organization_types.create' => 'Crear tipos de organización',
                'organization_types.edit'   => 'Editar tipos de organización',
                'organization_types.delete' => 'Eliminar tipos de organización',
            ],
        ],
        'business_type_access' => [
            'name' => 'Negocios — Acceso por tipo',
            'permissions' => [
                'business_types.access.view'   => 'Ver acceso por tipo de negocio',
                'business_types.access.manage' => 'Gestionar acceso por tipo de negocio',
            ],
        ],
        'reports' => [
            'name' => 'Gestión de Reportes',
            'permissions' => [
                'reports.view' => 'Ver reportes',
                'reports.export' => 'Exportar reportes',
            ],
        ],
        'settings' => [
            'name' => 'Configuración del Sistema',
            'permissions' => [
                'settings.view' => 'Ver configuración',
                'settings.edit' => 'Editar configuración',
            ],
        ],
        'settings_equipment_types' => [
            'name' => 'Configuración — Tipos de equipo',
            'permissions' => [
                'settings.equipment_types.view'   => 'Ver tipos de equipo',
                'settings.equipment_types.create' => 'Crear tipos de equipo',
                'settings.equipment_types.edit'   => 'Editar tipos de equipo',
                'settings.equipment_types.delete' => 'Eliminar tipos de equipo',
            ],
        ],
        'settings_attributes' => [
            'name' => 'Configuración — Atributos de equipo',
            'permissions' => [
                'settings.attributes.view'   => 'Ver atributos de equipo',
                'settings.attributes.create' => 'Crear atributos de equipo',
                'settings.attributes.edit'   => 'Editar atributos de equipo',
                'settings.attributes.delete' => 'Eliminar atributos de equipo',
            ],
        ],
        'roles' => [
            'name' => 'Gestión de Roles y Permisos',
            'permissions' => [
                'roles.view' => 'Ver roles',
                'roles.create' => 'Crear roles',
                'roles.edit' => 'Editar roles',
                'roles.delete' => 'Eliminar roles',
                'permissions.view' => 'Ver permisos',
                'permissions.assign' => 'Asignar permisos',
            ],
        ],
        'subscriptions' => [
            'name' => 'Gestión de Suscripciones',
            'permissions' => [
                'subscriptions.view' => 'Ver suscripciones',
                'subscriptions.create' => 'Crear suscripciones',
                'subscriptions.edit' => 'Editar suscripciones',
                'subscriptions.cancel' => 'Cancelar suscripciones',
                'subscriptions.plans.view' => 'Ver planes de suscripción',
                'subscriptions.plans.manage' => 'Gestionar planes de suscripción',
                'subscriptions.invoices.view' => 'Ver facturas',
                'subscriptions.invoices.manage' => 'Gestionar pagos',
            ],
        ],
        'workshop_clients' => [
            'name' => 'Taller — Clientes',
            'permissions' => [
                'workshop.clients.view' => 'Ver clientes',
                'workshop.clients.create' => 'Crear clientes',
                'workshop.clients.edit' => 'Editar clientes',
                'workshop.clients.delete' => 'Eliminar clientes',
                'workshop.clients.activate' => 'Activar clientes',
                'workshop.clients.deactivate' => 'Desactivar clientes',
            ],
        ],
        'workshop_equipment' => [
            'name' => 'Taller — Equipos',
            'permissions' => [
                'workshop.equipment.view' => 'Ver equipos',
                'workshop.equipment.create' => 'Crear equipos',
                'workshop.equipment.edit' => 'Editar equipos',
                'workshop.equipment.delete' => 'Eliminar equipos',
            ],
        ],
        'workshop' => [
            'name' => 'Taller',
            'permissions' => [
                'workshop.view' => 'Ver módulo de taller',
            ],
        ],
        'workshop_quotations' => [
            'name' => 'Taller — Cotizaciones',
            'permissions' => [
                'workshop.quotations.view' => 'Ver cotizaciones',
            ],
        ],
        'workshop_work_orders' => [
            'name' => 'Taller — Órdenes de trabajo',
            'permissions' => [
                'workshop.work-orders.view' => 'Ver órdenes de trabajo',
            ],
        ],
        'workshop_catalog' => [
            'name' => 'Catálogo',
            'permissions' => [
                'workshop.catalog.view'              => 'Ver módulo de catálogo',
                'workshop.catalog.services.view'     => 'Ver servicios',
                'workshop.catalog.spare-parts.view'  => 'Ver repuestos',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles del Sistema
    |--------------------------------------------------------------------------
    */

    'roles' => [
        'superAdmin' => [
            'name' => 'Super Administrador',
            'description' => 'Acceso completo al sistema',
            'level' => 1,
        ],
        'Administrador' => [
            'name' => 'Administrador',
            'description' => 'Administración de la empresa',
            'level' => 2,
        ],
        'Comercio' => [
            'name' => 'Comercio',
            'description' => 'Propietario del comercio registrado vía onboarding',
            'level' => 2,
        ],
        'Supervisor' => [
            'name' => 'Supervisor',
            'description' => 'Supervisión de operaciones',
            'level' => 3,
        ],
        'Operador' => [
            'name' => 'Operador',
            'description' => 'Operación del sistema',
            'level' => 4,
        ],
        'Pastor' => [
            'name' => 'Pastor',
            'description' => 'Liderazgo pastoral de la iglesia',
            'level' => 2,
        ],
        'Secretario' => [
            'name' => 'Secretario',
            'description' => 'Gestión administrativa de la iglesia',
            'level' => 3,
        ],
        'Lider de congregacion' => [
            'name' => 'Líder de congregación',
            'description' => 'Liderazgo de congregación o campo',
            'level' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles globales (disponibles en cualquier tipo de negocio)
    |--------------------------------------------------------------------------
    */

    'global_roles' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles de sistema (no se asignan por tipo de negocio)
    |--------------------------------------------------------------------------
    */

    'system_roles' => [
        'superAdmin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'redirect_unauthorized' => 'login',
        'error_message' => 'No tienes permisos para acceder a esta funcionalidad.',
        'error_code' => 403,
    ],
];
