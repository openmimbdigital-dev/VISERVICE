<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Negocio dueño de la plataforma
    |--------------------------------------------------------------------------
    |
    | VISERVICE le factura a los comercios que se suscriben, así que la propia
    | plataforma necesita un negocio emisor: es el que lleva la configuración
    | DIAN y el catálogo donde viven los productos de cada plan.
    |
    | No se puede eliminar y el superAdmin queda asociado a él.
    |
    */

    'owner_business_id' => (int) env('SUBSCRIPTIONS_OWNER_BUSINESS_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Producto que respalda a cada plan
    |--------------------------------------------------------------------------
    |
    | Cada plan de suscripción se refleja como un producto del negocio dueño,
    | para poder facturarlo como cualquier otra venta. Son productos ocultos:
    | no aparecen en el catálogo ni en los selectores de ítems, y solo se
    | modifican desde el plan.
    |
    */

    'plan_product' => [
        // Tipo de producto con el que se crean. Se busca por nombre entre los
        // tipos generales; si no existe, el producto queda sin tipo.
        'type_name' => 'Servicio',

        // Prefijo del SKU: queda como PLAN-0001.
        'sku_prefix' => 'PLAN-',
    ],

];
