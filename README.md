# Botble Redsys Plugin

Redsys (Servired / Sermepa) payment gateway integration for Botble CMS.

## Package Information

- Package: `botble/redsys`
- Author: Robin Cabeza Ruiz
- Plugin ID: `botble/redsys`
- Namespace: `Botble\Redsys\`

## Features

- Adds Redsys as a checkout payment method.
- Adds Redsys configuration section in Botble payment settings.
- Handles Redsys callback flow with signature validation.
- Processes successful payments through Botble payment hooks.
- Includes duplicate-payment protection (idempotency).

## Requirements

- Botble CMS with `botble/payment` plugin active.
- PHP and Laravel versions compatible with your Botble release.
- HTTPS-enabled public domain for production callbacks.

## Dependencies

- `botble/payment` (plugin dependency)
- `ssheduardo/redsys-laravel` `~1.5.0` (Composer dependency)

This plugin uses dependency placement Option A: Redsys SDK is declared in the plugin package and installed by Composer in the application `vendor/` directory.

## Installation

### 1. Add the plugin

Place the plugin under:

`platform/plugins/redsys`

### 2. Install Composer dependencies

From project root:

```bash
composer update
```

### 3. Activate plugin

From Admin -> Plugins, or:

```bash
php artisan cms:plugin:activate redsys
```

## Configuration

Go to **Admin -> Payments**, enable Redsys, then configure:

- Merchant code (FUC)
- Terminal
- SHA-256 secret key
- Environment (`test` / `live`)
- Trade name

## Redsys Endpoints

The plugin registers:

- `GET /payment/redsys/checkout`
- `POST /payment/redsys/notification`
- `GET /payment/redsys/ok`
- `GET /payment/redsys/ko`

In production, configure Redsys backoffice with:

- Notification URL: `https://your-domain.com/payment/redsys/notification`
- OK URL: `https://your-domain.com/payment/redsys/ok`
- KO URL: `https://your-domain.com/payment/redsys/ko`

## CSRF Exclusion

Exclude this route from CSRF protection in your app:

- `payment/redsys/notification`

Redsys sends server-to-server callbacks and cannot provide Laravel CSRF tokens.

## Botble Integration Notes

- Successful Redsys transactions trigger `PAYMENT_ACTION_PAYMENT_PROCESSED`.
- Notification callback is the primary confirmation path.
- OK return route provides a fallback processing path when needed.

## Quick Test Checklist

1. Enable Redsys in test mode.
2. Complete a test checkout.
3. Confirm callback hits `/payment/redsys/notification`.
4. Confirm payment/order data is persisted in Botble.
5. Switch to live credentials after successful validation.

## License

Proprietary or project-defined. Add a `LICENSE` file before public release.

