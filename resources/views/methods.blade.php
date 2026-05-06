@if (setting('payment_redsys_status') == 1)
    <li class="list-group-item">
        <input
            class="magic-radio js_payment_method"
            id="payment_redsys"
            name="payment_method"
            type="radio"
            value="{{ REDSYS_PAYMENT_METHOD_NAME }}"
            @if ($selecting == REDSYS_PAYMENT_METHOD_NAME) checked @endif
        >
        <label class="text-start" for="payment_redsys">
            {{ get_payment_setting('name', REDSYS_PAYMENT_METHOD_NAME, trans('plugins/redsys::redsys.name')) }}
        </label>

        <div
            class="payment_redsys_wrap payment_collapse_wrap collapse @if ($selecting == REDSYS_PAYMENT_METHOD_NAME) show @endif"
            style="padding: 15px 0;"
        >
            <p>{!! BaseHelper::clean(get_payment_setting('description', REDSYS_PAYMENT_METHOD_NAME)) !!}</p>

            @php $supportedCurrencies = (new Botble\Redsys\Services\Gateways\RedsysPaymentService)->supportedCurrencyCodes(); @endphp
            @if (function_exists('get_application_currency') && ! in_array(get_application_currency()->title, $supportedCurrencies))
                <div class="alert alert-warning" style="margin-top: 15px;">
                    {{ trans('plugins/redsys::redsys.messages.unsupported_currency', [
                        'name' => trans('plugins/redsys::redsys.name'),
                        'currency' => get_application_currency()->title,
                        'currencies' => implode(', ', $supportedCurrencies),
                    ]) }}
                </div>
            @endif
        </div>
    </li>
@endif
