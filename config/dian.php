<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Entorno de facturación electrónica
    |--------------------------------------------------------------------------
    |
    | "test" apunta al ambiente de habilitación/pruebas del proveedor y
    | "production" al ambiente real ante la DIAN. Cada negocio puede además
    | forzar su propio entorno desde business_dian_settings.environment.
    |
    */

    'environment' => env('DIAN_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Proveedor tecnológico: TITANIO (Delcop)
    |--------------------------------------------------------------------------
    |
    | Las credenciales corresponden a la empresa gestionadora, bajo la cual se
    | registran todos los negocios emisores.
    |
    */

    'titanio' => [
        'base_url' => [
            'test'       => rtrim(env('URL_TEST', 'https://www-prueba.titanio.com.co/'), '/'),
            'production' => rtrim(env('URL_PROD', 'https://www.titanio.com.co/'), '/'),
        ],

        'nit'      => env('NIT_DELCOP'),
        'user'     => env('USER_DELCOP'),
        'password' => env('PASSWORD_DELCOP'),

        // El manual recomienda un timeout mínimo de 10 segundos para emitir_v2.
        'timeout' => (int) env('DIAN_TIMEOUT', 30),

        // El token trae su fecha de vencimiento; se cachea restando este margen.
        'token_safety_margin_seconds' => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Valores fijos del formato DATASET
    |--------------------------------------------------------------------------
    |
    | Marcados como "fijo" en el XML de referencia del proveedor.
    |
    */

    'defaults' => [
        'provider_id'                        => '860028581',
        'provider_id_scheme_id'              => '1',
        'authorization_provider_id'          => '800197268',
        'authorization_provider_id_scheme_id' => '4',
        'ubl_version_id'                     => 'UBL 2.1',
        'profile_id'                         => 'DIAN 2.1: Factura Electrónica de Venta',
        'customization_id'                   => '10',
        'invoice_type_code'                  => '01',
        'currency_code'                      => 'COP',
        'identification_code'                => 'CO',
        'country_name'                       => 'Colombia',
        'country_code'                       => 'CO',

        // ProfileExecutionID: 1 = producción, 2 = pruebas.
        'profile_execution_id' => [
            'test'       => '2',
            'production' => '1',
        ],

        // Código DIAN del tipo de documento para SaveAutoGestion (11 = factura de venta).
        'document_type_code' => '11',

        // Formato de entrada del documento en la plataforma.
        'input_format' => 'DATASET_DATASET',

        // Clave técnica del ambiente de pruebas indicada en el manual.
        'test_technical_key' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
    ],

    /*
    |--------------------------------------------------------------------------
    | Catálogos DIAN para los formularios
    |--------------------------------------------------------------------------
    */

    'person_types' => [
        1 => 'Persona jurídica',
        2 => 'Persona natural',
    ],

    'fiscal_responsibilities' => [
        'R-99-PN' => 'No responsable (R-99-PN)',
        'O-13'    => 'Gran contribuyente (O-13)',
        'O-15'    => 'Autorretenedor (O-15)',
        'O-23'    => 'Agente de retención de IVA (O-23)',
        'O-47'    => 'Régimen simple de tributación (O-47)',
    ],

];
