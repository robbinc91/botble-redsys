<?php

namespace Botble\Redsys\Services\Gateways;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Redsys\Supports\RedsysHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RedsysPaymentService
{
    use PaymentErrorTrait;

    public function getSupportRefundOnline(): bool
    {
        return false;
    }

    public static function payloadCacheKey(string $merchantOrder): string
    {
        return 'redsys.payment.payload.' . $merchantOrder;
    }

    public static function chargeCacheKey(string $merchantOrder): string
    {
        return 'redsys.payment.charge.' . $merchantOrder;
    }

    public static function chargeDataCacheKey(string $chargeId): string
    {
        return 'redsys.payment.charge.data.' . $chargeId;
    }

    public function execute(array $data): ?string
    {
        $merchantCode = get_payment_setting('merchant_code', REDSYS_PAYMENT_METHOD_NAME);
        $terminal = get_payment_setting('terminal', REDSYS_PAYMENT_METHOD_NAME, '001');
        $key = get_payment_setting('key', REDSYS_PAYMENT_METHOD_NAME);

        if (! $merchantCode || ! $terminal || ! $key) {
            $this->setErrorMessage(trans('plugins/redsys::redsys.messages.invalid_settings', ['name' => trans('plugins/redsys::redsys.name')]));

            return null;
        }

        $currency = strtoupper((string) ($data['currency'] ?? ''));
        if (! RedsysHelper::isoToNumeric($currency)) {
            $this->setErrorMessage(trans(
                'plugins/redsys::redsys.messages.unsupported_currency',
                [
                    'name' => trans('plugins/redsys::redsys.name'),
                    'currency' => $currency,
                    'currencies' => implode(', ', RedsysHelper::supportedCurrencyCodes()),
                ]
            ));

            return null;
        }

        $orderIds = Arr::wrap(Arr::get($data, 'order_id'));
        $bookingId = Arr::first($orderIds);

        if ($bookingId === null || $bookingId === '') {
            $this->setErrorMessage(trans('plugins/redsys::redsys.messages.missing_booking_reference'));

            return null;
        }

        $merchantOrder = $this->generateMerchantOrder((string) $bookingId);

        Cache::put(
            self::payloadCacheKey($merchantOrder),
            [
                'order_id' => $orderIds,
                'amount' => (float) $data['amount'],
                'currency' => $currency,
                'customer_id' => Arr::get($data, 'customer_id'),
                'customer_type' => Arr::get($data, 'customer_type'),
            ],
            now()->addMinutes(45)
        );

        session([
            'redsys_checkout_order' => $merchantOrder,
        ]);

        return route('payments.redsys.checkout');
    }

    /**
     * Redsys order id: 4–12 chars; first four characters must be valid per Tpv (starts with 4+ chars matching [\w\.]+).
     */
    protected function generateMerchantOrder(string $bookingId): string
    {
        $digits = preg_replace('/\D/', '', $bookingId) ?: '1';
        $prefix = substr(str_pad($digits, 4, '0', STR_PAD_LEFT), -4);
        $suffix = strtoupper(bin2hex(random_bytes(4)));

        return substr($prefix . $suffix, 0, 12);
    }

    public function supportedCurrencyCodes(): array
    {
        return RedsysHelper::supportedCurrencyCodes();
    }
}
