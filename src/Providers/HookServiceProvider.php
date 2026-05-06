<?php

namespace Botble\Redsys\Providers;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Redsys\Forms\RedsysPaymentMethodForm;
use Botble\Redsys\Services\Gateways\RedsysPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerRedsysMethod'], 15, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithRedsys'], 15, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 15);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['REDSYS'] = REDSYS_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 15, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == REDSYS_PAYMENT_METHOD_NAME) {
                $value = trans('plugins/redsys::redsys.name');
            }

            return $value;
        }, 15, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == REDSYS_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 15, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == REDSYS_PAYMENT_METHOD_NAME) {
                $data = RedsysPaymentService::class;
            }

            return $data;
        }, 15, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == REDSYS_PAYMENT_METHOD_NAME) {
                $data = view('plugins/redsys::detail', ['payment' => $payment])->render();
            }

            return $data;
        }, 15, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . RedsysPaymentMethodForm::create()->renderForm();
    }

    public function registerRedsysMethod(?string $html, array $data): string
    {
        PaymentMethods::method(REDSYS_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/redsys::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithRedsys(array $data, Request $request): array
    {
        if ($data['type'] !== REDSYS_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $service = $this->app->make(RedsysPaymentService::class);

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        $currency = strtoupper((string) ($paymentData['currency'] ?? ''));

        if (! in_array($currency, $service->supportedCurrencyCodes())) {
            $data['error'] = true;
            $data['message'] = trans(
                'plugins/redsys::redsys.messages.unsupported_currency',
                [
                    'name' => trans('plugins/redsys::redsys.name'),
                    'currency' => $currency,
                    'currencies' => implode(', ', $service->supportedCurrencyCodes()),
                ]
            );

            return $data;
        }

        $checkoutUrl = $service->execute($paymentData);

        if ($service->getErrorMessage()) {
            $data['error'] = true;
            $data['message'] = $service->getErrorMessage();
        } elseif ($checkoutUrl) {
            $data['checkoutUrl'] = $checkoutUrl;
        } else {
            $data['error'] = true;
            $data['message'] = trans('plugins/redsys::redsys.messages.could_not_start_redsys_payment');
        }

        return $data;
    }
}
