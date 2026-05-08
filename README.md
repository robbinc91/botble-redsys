# Botble Redsys Plugin

Redsys (Servired / Sermepa) payment gateway integration for Botble CMS.

## Package metadata

| Field | Value |
|--------|--------|
| **Composer package** | `robbinc91/botble-redsys` |
| **Homepage / source** | [github.com/robbinc91/botble-redsys](https://github.com/robbinc91/botble-redsys) |
| **Plugin ID** (`plugin.json`) | `robbinc91/botble-redsys` |
| **PHP namespace** | `Botble\Redsys\` |
| **Author** | Robin Cabeza Ruiz |
| **License** | MIT |

## Features

- Redsys as a checkout payment method (with Botble `payment` plugin).
- Redsys settings under **Admin → Payments**.
- Signed notification handling and optional OK-return verification.
- Successful payments fire Botble’s `PAYMENT_ACTION_PAYMENT_PROCESSED` hook.
- Idempotent processing to reduce duplicate payment rows.

## Requirements

- **PHP** `>= 8.2.11` (see `composer.json`).
- **Botble** with **`botble/payment`** plugin installed and active.
- **HTTPS** on production for callback URLs.
- Redsys **notification URL** must be reachable from Redsys servers.

## Dependencies

- **`botble/payment`** — required Botble plugin (`plugin.json`).
- **`ssheduardo/redsys-laravel`** `~1.5.0` — Redsys Laravel SDK (`composer.json`).

### Composer vs `vendor/robbinc91`

If you only copy this plugin into **`platform/plugins/redsys`** and run **`composer update`** at the project root, Composer installs **merged dependencies** (e.g. `ssheduardo/redsys-laravel`) into **`vendor/`**. It does **not** necessarily create **`vendor/robbinc91/botble-redsys`** unless the root app **`composer require`s** `robbinc91/botble-redsys`. That layout is normal for Botble plugins deployed as files under `platform/plugins/`.

**Option A (merge-plugin):** SDK is declared in this plugin’s `composer.json` and resolved into the app’s root `vendor/` after `composer update`.

## Installation

### Method A — Copy into Botble (most common)

1. Put the plugin folder here (folder name **`redsys`**):

   `platform/plugins/redsys`

2. From the **project root**:

   ```bash
   composer update
   ```

3. Activate using the **directory name** under `platform/plugins` (here **`redsys`**). Botble’s CLI strips any `vendor/` prefix but still resolves the folder — **`cms:plugin:activate botble-redsys` would not match** if your folder is `redsys`.

   ```bash
   php artisan cms:plugin:activate redsys
   ```

   Or use **Admin → Plugins**.

### Method B — Install as Composer package (optional)

If the package is on Packagist or a VCS repository configured in the root `composer.json`:

```bash
composer require robbinc91/botble-redsys:^1.0
```

You still need the plugin **discovered by Botble** under `platform/plugins/redsys` (copy, symlink, or your deployment strategy). Botble scans **direct children** of `platform/plugins`, not arbitrary nested vendor paths.

## Configuration

**Admin → Payments** — enable Redsys and set:

- Merchant code (FUC)
- Terminal
- SHA-256 secret key
- Environment: `test` or `live`
- Trade name

## HTTP routes

| Method | Path | Name |
|--------|------|------|
| GET | `/payment/redsys/checkout` | `payments.redsys.checkout` |
| POST | `/payment/redsys/notification` | `payments.redsys.notification` |
| GET | `/payment/redsys/ok` | `payments.redsys.ok` |
| GET | `/payment/redsys/ko` | `payments.redsys.ko` |

Configure Redsys backoffice (example):

- **Notification:** `https://your-domain.com/payment/redsys/notification`
- **OK:** `https://your-domain.com/payment/redsys/ok`
- **KO:** `https://your-domain.com/payment/redsys/ko`

## CSRF

Exclude the notification route from CSRF verification (Redsys server POST has no Laravel token):

- Path prefix / URI: **`payment/redsys/notification`**

(Typically in `app/Http/Middleware/VerifyCsrfToken.php` `$except`.)

## Integration behaviour

- **Notification** (`POST …/notification`) is the primary authoritative confirmation.
- **OK** return can assist when session/callback ordering differs; signatures are verified when Redsys sends parameters.
- Hotel/booking flows that listen for `PAYMENT_ACTION_PAYMENT_PROCESSED` behave like other gateways (e.g. Stripe) once this hook runs.

## Verify installation

```bash
php artisan route:list --name=payments.redsys
```

Expect four routes named `payments.redsys.*`.

Then: test mode checkout → confirm notification → confirm payment and order/booking linkage → switch to live credentials.

## Translations

Language files live under `resources/lang/{locale}/redsys.php` (e.g. `en`, `es`).

