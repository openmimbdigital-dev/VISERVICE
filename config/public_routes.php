<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sección pública Participantes — ítems administrables
    |--------------------------------------------------------------------------
    |
    | El portal (bienvenida) siempre está disponible con el token del negocio.
    | El superAdmin solo decide qué ítems del menú se muestran por organization_type.
    |
    | route_key  → clave en organization_type_public_routes
    | label      → texto UI (español)
    | route_name → ruta Laravel del ítem
    |
    */

    'section' => [
        'label' => 'Participantes',
        'home_route' => 'public.participants.home',
    ],

    'items' => [
        'public.participants.events' => [
            'label' => 'Eventos',
            'route_name' => 'public.participants.events',
            'sort_order' => 10,
        ],
    ],
];
