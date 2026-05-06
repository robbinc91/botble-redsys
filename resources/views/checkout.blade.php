<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('plugins/redsys::redsys.messages.redirecting_to_redsys') }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at top right, #eef4ff 0%, #f7f9fc 40%, #ffffff 100%);
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .checkout-wrapper {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            padding: 28px;
            text-align: center;
        }

        .logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            margin-bottom: 14px;
        }

        .title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }

        .description {
            margin: 10px 0 18px;
            color: #475569;
            line-height: 1.55;
            font-size: 0.97rem;
        }

        .spinner {
            width: 36px;
            height: 36px;
            border: 3px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            margin: 0 auto 16px;
            animation: spin 0.8s linear infinite;
        }

        .hint {
            margin: 0;
            font-size: 0.88rem;
            color: #6b7280;
        }

        .manual-submit {
            margin-top: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 11px 16px;
            cursor: pointer;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
<main class="checkout-wrapper" role="status" aria-live="polite">
    <img class="logo" src="{{ asset('vendor/core/plugins/redsys/images/redsys.svg') }}" alt="{{ trans('plugins/redsys::redsys.name') }}">
    <h1 class="title">{{ trans('plugins/redsys::redsys.messages.redirecting_to_redsys') }}</h1>
    <p class="description">{{ trans('plugins/redsys::redsys.messages.redirecting_to_secure_payment') }}</p>
    <div class="spinner" aria-hidden="true"></div>
    <p class="hint">{{ trans('plugins/redsys::redsys.messages.redirect_hint') }}</p>

    {!! $form !!}
    <noscript>
        <button class="manual-submit" type="submit" form="redsys_form">{{ trans('plugins/redsys::redsys.messages.continue_to_secure_payment') }}</button>
    </noscript>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('redsys_form');
        if (form) {
            form.submit();
        }
    });
</script>
</body>
</html>
