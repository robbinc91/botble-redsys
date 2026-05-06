<?php

return [
    'name' => 'Redsys',
    'description' => 'Pay with credit or debit card via Redsys (Spanish banks / Servired).',
    'merchant_code' => 'Merchant code (FUC)',
    'terminal' => 'Terminal number',
    'secret_key' => 'SHA-256 encryption key',
    'secret_key_placeholder' => 'Enter key from Redsys back-office',
    'environment' => 'Environment',
    'environment_test' => 'Test',
    'environment_live' => 'Live (production)',
    'trade_name' => 'Merchant trade name',
    'instructions' => [
        'merchant_data' => 'Configure your merchant data from your acquiring bank (FUC, terminal, SHA-256 key).',
        'test_mode' => 'Use test mode until production credentials are active.',
        'notification_url' => 'Ensure notification URL is reachable over HTTPS in production:',
    ],
    'messages' => [
        'invalid_settings' => ':name payment settings are invalid.',
        'unsupported_currency' => ":name doesn't support :currency. Supported currencies: :currencies.",
        'missing_booking_reference' => 'Missing booking reference for payment. Please try again.',
        'could_not_start_payment' => 'Could not start payment. Please try again.',
        'could_not_start_redsys_payment' => 'Could not start Redsys payment.',
        'checkout_successfully' => 'Checkout successfully!',
        'payment_failed_or_cancelled' => 'Payment failed or was cancelled.',
        'redirecting_to_redsys' => 'Redirecting to Redsys…',
        'redirecting_to_secure_payment' => 'You are being redirected to a secure payment page. Please do not refresh or close this window.',
        'redirect_hint' => 'If the redirect takes too long, use the button below.',
        'continue_to_secure_payment' => 'Continue to secure payment',
        'payment_reference' => 'Payment reference',
        'paid_via_redsys' => 'Paid via Redsys (Servired).',
    ],
];
