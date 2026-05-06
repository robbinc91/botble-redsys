<?php

return [
    'name' => 'Redsys',
    'description' => 'Paga con tarjeta de credito o debito a traves de Redsys (bancos espanoles / Servired).',
    'merchant_code' => 'Codigo de comercio (FUC)',
    'terminal' => 'Numero de terminal',
    'secret_key' => 'Clave de cifrado SHA-256',
    'secret_key_placeholder' => 'Introduce la clave del panel de Redsys',
    'environment' => 'Entorno',
    'environment_test' => 'Pruebas',
    'environment_live' => 'Real (produccion)',
    'trade_name' => 'Nombre comercial',
    'instructions' => [
        'merchant_data' => 'Configura los datos de comercio con tu banco adquirente (FUC, terminal, clave SHA-256).',
        'test_mode' => 'Usa el modo de pruebas hasta tener activas las credenciales de produccion.',
        'notification_url' => 'Asegura que la URL de notificacion sea accesible por HTTPS en produccion:',
    ],
    'messages' => [
        'invalid_settings' => 'La configuracion de pago de :name no es valida.',
        'unsupported_currency' => ':name no admite :currency. Monedas soportadas: :currencies.',
        'missing_booking_reference' => 'Falta la referencia de reserva para el pago. Intentalo de nuevo.',
        'could_not_start_payment' => 'No se pudo iniciar el pago. Intentalo de nuevo.',
        'could_not_start_redsys_payment' => 'No se pudo iniciar el pago con Redsys.',
        'checkout_successfully' => 'Pago completado correctamente.',
        'payment_failed_or_cancelled' => 'El pago ha fallado o fue cancelado.',
        'redirecting_to_redsys' => 'Redirigiendo a Redsys…',
        'redirecting_to_secure_payment' => 'Te estamos redirigiendo a una pagina de pago segura. No recargues ni cierres esta ventana.',
        'redirect_hint' => 'Si la redireccion tarda demasiado, usa el boton de abajo.',
        'continue_to_secure_payment' => 'Continuar al pago seguro',
        'payment_reference' => 'Referencia de pago',
        'paid_via_redsys' => 'Pagado via Redsys (Servired).',
    ],
];
