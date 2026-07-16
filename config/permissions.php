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
                'businesses.manage_modules'   => 'Gestionar módulos por negocio (plataforma)',
            ],
        ],
        'business_types' => [
            'name' => 'Gestión de Negocios — Tipos de negocio',
            'permissions' => [
                'business_types.view'   => 'Ver tipos de negocio',
                'business_types.create' => 'Crear tipos de negocio',
                'business_types.edit'   => 'Editar tipos de negocio',
                'business_types.delete' => 'Eliminar tipos de negocio',
            ],
        ],
        'organization_types' => [
            'name' => 'Gestión de Negocios — Tipos de organización',
            'permissions' => [
                'organization_types.view'   => 'Ver tipos de organización',
                'organization_types.create' => 'Crear tipos de organización',
                'organization_types.edit'   => 'Editar tipos de organización',
                'organization_types.delete' => 'Eliminar tipos de organización',
            ],
        ],
        'organization_type_access' => [
            'name' => 'Gestión de Negocios — Acceso por negocio',
            'permissions' => [
                'organization_types.access.view'   => 'Ver acceso por tipo de organización',
                'organization_types.access.manage' => 'Gestionar acceso por tipo de organización',
            ],
        ],
        'team_positions' => [
            'name' => 'Negocios — Cargos del equipo',
            'permissions' => [
                'team_positions.view'   => 'Ver cargos del equipo',
                'team_positions.create' => 'Crear cargos del equipo',
                'team_positions.edit'   => 'Editar cargos del equipo',
                'team_positions.delete' => 'Eliminar cargos del equipo',
            ],
        ],
        'business_payment_methods' => [
            'name' => 'Negocios — Métodos de pago',
            'permissions' => [
                'business_payment_methods.view'   => 'Ver métodos de pago del negocio',
                'business_payment_methods.create' => 'Crear métodos de pago del negocio',
                'business_payment_methods.edit'   => 'Editar métodos de pago del negocio',
                'business_payment_methods.delete' => 'Eliminar métodos de pago del negocio',
            ],
        ],
        'business_bank_accounts' => [
            'name' => 'Negocios — Datos bancarios',
            'permissions' => [
                'business_bank_accounts.view'   => 'Ver cuentas bancarias del negocio',
                'business_bank_accounts.create' => 'Crear cuentas bancarias del negocio',
                'business_bank_accounts.edit'   => 'Editar cuentas bancarias del negocio',
                'business_bank_accounts.delete' => 'Eliminar cuentas bancarias del negocio',
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
        'settings_brands' => [
            'name' => 'Configuración — Marcas de equipo',
            'permissions' => [
                'settings.brands.view'   => 'Ver marcas de equipo',
                'settings.brands.create' => 'Crear marcas de equipo',
                'settings.brands.edit'   => 'Editar marcas de equipo',
                'settings.brands.delete' => 'Eliminar marcas de equipo',
            ],
        ],
        'settings_model_equipment' => [
            'name' => 'Configuración — Modelos de equipo',
            'permissions' => [
                'settings.model_equipment.view'   => 'Ver modelos de equipo',
                'settings.model_equipment.create' => 'Crear modelos de equipo',
                'settings.model_equipment.edit'   => 'Editar modelos de equipo',
                'settings.model_equipment.delete' => 'Eliminar modelos de equipo',
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
                'workshop.quotations.view'   => 'Ver cotizaciones',
                'workshop.quotations.create' => 'Crear cotizaciones',
                'workshop.quotations.edit'   => 'Editar cotizaciones',
                'workshop.quotations.delete' => 'Eliminar cotizaciones',
            ],
        ],
        'workshop_quotation_service_types' => [
            'name' => 'Taller — Tipos de servicio',
            'permissions' => [
                'workshop.quotation_service_types.view'   => 'Ver tipos de servicio',
                'workshop.quotation_service_types.create' => 'Crear tipos de servicio',
                'workshop.quotation_service_types.edit'   => 'Editar tipos de servicio',
                'workshop.quotation_service_types.delete' => 'Eliminar tipos de servicio',
            ],
        ],
        'workshop_work_orders' => [
            'name' => 'Taller — Órdenes de trabajo',
            'permissions' => [
                'workshop.work-orders.view'   => 'Ver órdenes de trabajo',
                'workshop.work-orders.create' => 'Crear órdenes de trabajo',
                'workshop.work-orders.edit'   => 'Editar órdenes de trabajo',
                'workshop.work-orders.delete' => 'Eliminar órdenes de trabajo',
            ],
        ],
        'workshop_work_order_associated_documents' => [
            'name' => 'Taller — Documentos asociados OT',
            'permissions' => [
                'workshop.work-orders.associated-documents.view'   => 'Ver documentos asociados OT',
                'workshop.work-orders.associated-documents.create' => 'Crear documentos asociados OT',
                'workshop.work-orders.associated-documents.edit'   => 'Editar documentos asociados OT',
                'workshop.work-orders.associated-documents.delete' => 'Eliminar documentos asociados OT',
            ],
        ],
        'catalog' => [
            'name' => 'Catálogo — Productos y servicios',
            'permissions' => [
                'catalog.view'            => 'Ver módulo de catálogo',
                'catalog.products.view'   => 'Ver productos y servicios',
                'catalog.products.create' => 'Crear productos y servicios',
                'catalog.products.edit'   => 'Editar productos y servicios',
                'catalog.products.delete' => 'Eliminar productos y servicios',
            ],
        ],
        'settings_product_types' => [
            'name' => 'Configuración — Tipos de producto',
            'permissions' => [
                'settings.product_types.view'   => 'Ver tipos de producto',
                'settings.product_types.create' => 'Crear tipos de producto',
                'settings.product_types.edit'   => 'Editar tipos de producto',
                'settings.product_types.delete' => 'Eliminar tipos de producto',
            ],
        ],
        'settings_product_categories' => [
            'name' => 'Configuración — Categorías de producto',
            'permissions' => [
                'settings.product_categories.view'   => 'Ver categorías de producto',
                'settings.product_categories.create' => 'Crear categorías de producto',
                'settings.product_categories.edit'   => 'Editar categorías de producto',
                'settings.product_categories.delete' => 'Eliminar categorías de producto',
            ],
        ],
        'settings_units' => [
            'name' => 'Configuración — Unidades de medida',
            'permissions' => [
                'settings.units.view'   => 'Ver unidades de medida',
                'settings.units.create' => 'Crear unidades de medida',
                'settings.units.edit'   => 'Editar unidades de medida',
                'settings.units.delete' => 'Eliminar unidades de medida',
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
    | Permisos solo de plataforma (Gestión de Negocios — solo superAdmin)
    | No se asignan a negocios vía Acceso por negocio.
    |--------------------------------------------------------------------------
    */

    'platform_only_permissions' => [
        'organization_types.view',
        'organization_types.create',
        'organization_types.edit',
        'organization_types.delete',
        'business_types.view',
        'business_types.create',
        'business_types.edit',
        'business_types.delete',
        'organization_types.access.view',
        'organization_types.access.manage',
        'businesses.manage_modules',
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
