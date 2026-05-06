<?php

namespace Botble\Redsys\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Forms\PaymentMethodForm;
use Illuminate\Support\Facades\Blade;

class RedsysPaymentMethodForm extends PaymentMethodForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(REDSYS_PAYMENT_METHOD_NAME)
            ->paymentName(trans('plugins/redsys::redsys.name'))
            ->paymentDescription(trans('plugins/redsys::redsys.description'))
            ->paymentLogo(url('vendor/core/plugins/redsys/images/redsys.svg'))
            ->paymentUrl('https://pagosonline.redsys.es/')
            ->paymentInstructions(Blade::render(<<<BLADE
                <ol>
                    <li>{{ trans('plugins/redsys::redsys.instructions.merchant_data') }}</li>
                    <li>{{ trans('plugins/redsys::redsys.instructions.test_mode') }}</li>
                    <li>{{ trans('plugins/redsys::redsys.instructions.notification_url') }}
                        <code>{{ route('payments.redsys.notification', [], true) }}</code>
                    </li>
                </ol>
            BLADE))
            ->add(
                'payment_redsys_merchant_code',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/redsys::redsys.merchant_code'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_code', REDSYS_PAYMENT_METHOD_NAME))
                    ->required()
                    ->attributes(['data-counter' => 20])
                    ->toArray()
            )
            ->add(
                'payment_redsys_terminal',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/redsys::redsys.terminal'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '***' : get_payment_setting('terminal', REDSYS_PAYMENT_METHOD_NAME, '001'))
                    ->required()
                    ->attributes(['data-counter' => 10])
                    ->toArray()
            )
            ->add(
                'payment_redsys_key',
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/redsys::redsys.secret_key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('key', REDSYS_PAYMENT_METHOD_NAME))
                    ->placeholder(trans('plugins/redsys::redsys.secret_key_placeholder'))
                    ->required()
                    ->toArray()
            )
            ->add(
                'payment_redsys_environment',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/redsys::redsys.environment'))
                    ->choices([
                        'test' => trans('plugins/redsys::redsys.environment_test'),
                        'live' => trans('plugins/redsys::redsys.environment_live'),
                    ])
                    ->selected(get_payment_setting('environment', REDSYS_PAYMENT_METHOD_NAME, 'test'))
                    ->toArray()
            )
            ->add(
                'payment_redsys_trade_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/redsys::redsys.trade_name'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('trade_name', REDSYS_PAYMENT_METHOD_NAME, config('app.name')))
                    ->attributes(['data-counter' => 120])
                    ->toArray()
            );
    }
}
