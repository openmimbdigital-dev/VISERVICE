<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pasarela de pagos Bold
    |--------------------------------------------------------------------------
    |
    | Con Bold los comercios pagan su suscripción en línea (tarjeta, PSE, Nequi
    | o botón Bancolombia) sin esperar a que alguien confirme una transferencia.
    | El cobro manual con comprobante sigue existiendo: esto se suma, no lo
    | reemplaza.
    |
    | Son dos llaves distintas y no se pueden intercambiar:
    |   - identity_key: autentica nuestras llamadas a la API de Bold.
    |   - secret_key:   verifica la firma de los webhooks que Bold nos envía.
    |
    */

    'base_url' => rtrim((string) env('BOLD_BASE_URL', 'https://integrations.api.bold.co'), '/'),

    'identity_key' => env('IDENTITY_KEY_BOLD'),

    'secret_key' => env('SECRET_KEY_BOLD'),

    'timeout' => (int) env('BOLD_TIMEOUT', 20),

    /*
    |--------------------------------------------------------------------------
    | Links de pago
    |--------------------------------------------------------------------------
    */

    'link' => [
        'currency' => 'COP',

        // Métodos ofrecidos en el checkout. Vacío = los que Bold tenga activos.
        'payment_methods' => ['CREDIT_CARD', 'PSE', 'BOTON_BANCOLOMBIA', 'NEQUI'],

        // Vigencia del link. Bold la recibe en nanosegundos desde la época Unix.
        'expiration_hours' => (int) env('BOLD_LINK_EXPIRATION_HOURS', 72),

        // A dónde vuelve el pagador al terminar. Debe ser https en producción.
        'callback_route' => 'subscriptions.payment.callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook
    |--------------------------------------------------------------------------
    |
    | Bold espera un 200 en menos de 2 segundos y reintenta hasta 5 veces si no
    | lo recibe. Por eso el endpoint solo registra y confirma: nada lento —como
    | emitir ante la DIAN— debe colgar de él.
    |
    | En modo de pruebas Bold firma con una llave vacía; «test_mode» permite
    | aceptar esa firma mientras se integra.
    |
    */

    'webhook' => [
        'signature_header' => 'x-bold-signature',

        'test_mode' => (bool) env('BOLD_WEBHOOK_TEST_MODE', false),
    ],

];
