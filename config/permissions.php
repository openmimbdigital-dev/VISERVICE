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
