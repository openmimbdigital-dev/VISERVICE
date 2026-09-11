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
        // Valor con el que el proveedor aceptó el documento de referencia.
        'profile_id'                         => 'DIAN 2.1',
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
    | Consumidor final (adquiriente no identificado)
    |--------------------------------------------------------------------------
    |
    | Valores que la DIAN define para facturar a alguien que no se identifica.
    | El NIT 222222222222 es el reservado para este caso; el tipo de documento
    | es 13 (cédula de ciudadanía) y la responsabilidad fiscal R-99-PN.
    |
    */

    'final_consumer' => [
        'name'               => 'Consumidor final',
        'document_number'    => '222222222222',
        'document_type_code' => '13',
        'person_type'        => '2', // 2 = persona natural
        'tax_level_code'     => 'R-99-PN',
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

    /*
    |--------------------------------------------------------------------------
    | Numeración
    |--------------------------------------------------------------------------
    |
    | El consecutivo lo llevamos nosotros, pero quien sabe cuáles se usaron de
    | verdad es el proveedor. Consultarlo permite recuperar el punto de partida
    | cuando la base local se rehace. No acepta rangos de más de 30 días, así que
    | se busca por ventanas hacia atrás.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Medios de pago
    |--------------------------------------------------------------------------
    |
    | Tabla 12 de la DIAN (UN/ECE 4461). La oficial tiene casi noventa entradas,
    | y la mayoría son instrumentos bancarios que en un taller no se ven nunca
    | —notas promisorias, clearing, bookentry—. Se ofrecen las que sí se usan; el
    | resto solo estorbaría al elegir.
    |
    | Cuando no se sabe con qué van a pagar, la DIAN espera el «1»: instrumento
    | no definido. Es lo que salía siempre antes de poder preguntarlo.
    |
    */

    'payment_means' => [
        '10' => 'Efectivo',
        '48' => 'Tarjeta de crédito',
        '49' => 'Tarjeta débito',
        '42' => 'Consignación bancaria',
        '45' => 'Transferencia crédito bancario',
        '47' => 'Transferencia débito bancaria',
        '20' => 'Cheque',
        '71' => 'Bonos',
        '72' => 'Vales',
        '1'  => 'Instrumento no definido',
    ],

    'default_payment_means' => '1',

    'consecutive_lookup' => [
        'windows'   => (int) env('DIAN_CONSECUTIVE_WINDOWS', 6),
        'max_pages' => (int) env('DIAN_CONSECUTIVE_MAX_PAGES', 20),
    ],

    'emission' => [
        // Un «documento duplicado» significa que nuestro contador viene atrasado.
        // Se reintenta con el siguiente número, unas pocas veces.
        'duplicate_retries' => (int) env('DIAN_DUPLICATE_RETRIES', 5),
    ],

    'fiscal_responsibilities' => [
        'R-99-PN' => 'No responsable (R-99-PN)',
        'O-13'    => 'Gran contribuyente (O-13)',
        'O-15'    => 'Autorretenedor (O-15)',
        'O-23'    => 'Agente de retención de IVA (O-23)',
        'O-47'    => 'Régimen simple de tributación (O-47)',
    ],

];
