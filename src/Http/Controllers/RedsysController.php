<?php

namespace Botble\Redsys\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Botble\Payment\Supports\PaymentHelper;
use Botble\Redsys\Services\Gateways\RedsysPaymentService;
use Botble\Redsys\Supports\RedsysHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Ssheduardo\Redsys\Facades\Redsys;
use Throwable;

class RedsysController extends BaseController
{
    public function checkout(): mixed
    {
        $merchantOrder = session('redsys_checkout_order');

        if (! $merchantOrder) {
            abort(404);
        }

        $payload = Cache::get(RedsysPaymentService::payloadCacheKey($merchantOrder));

        if (! $payload) {
            abort(404);
        }

        $key = get_payment_setting('key', REDSYS_PAYMENT_METHOD_NAME);
        $merchantCode = get_payment_setting('merchant_code', REDSYS_PAYMENT_METHOD_NAME);
        $terminal = get_payment_setting('terminal', REDSYS_PAYMENT_METHOD_NAME, '001');
        $environment = get_payment_setting('environment', REDSYS_PAYMENT_METHOD_NAME, 'test');
        $tradeName = get_payment_setting('trade_name', REDSYS_PAYMENT_METHOD_NAME, config('app.name'));

        $currencyNumeric = RedsysHelper::isoToNumeric($payload['currency']);

        try {
            Redsys::setAmount(round((float) $payload['amount'], 2));
            Redsys::setOrder($merchantOrder);
            Redsys::setMerchantcode($merchantCode);
            Redsys::setCurrency($currencyNumeric);
            Redsys::setTransactiontype('0');
            Redsys::setTerminal($terminal);
            Redsys::setMethod('T');
            Redsys::setNotification(route('payments.redsys.notification', [], true));
            Redsys::setUrlOk(route('payments.redsys.ok', [], true));
            Redsys::setUrlKo(route('payments.redsys.ko', [], true));
            Redsys::setVersion('HMAC_SHA256_V1');
            Redsys::setTradeName((string) $tradeName);
            Redsys::setProductDescription(
                trans('plugins/payment::payment.payment_description', [
                    'order_id' => implode(', #', (array) $payload['order_id']),
                    'site_url' => request()->getHost(),
                ])
            );
            Redsys::setEnviroment($environment === 'live' ? 'live' : 'test');

            $signature = Redsys::generateMerchantSignature($key);
            Redsys::setMerchantSignature($signature);

            $form = Redsys::createForm();
        } catch (Throwable $exception) {
            Log::error('Redsys checkout form failed', ['exception' => $exception]);

            abort(500, trans('plugins/redsys::redsys.messages.could_not_start_payment'));
        }

        return view('plugins/redsys::checkout', compact('form'));
    }

    public function notification(Request $request): \Illuminate\Http\Response
    {
        $key = get_payment_setting('key', REDSYS_PAYMENT_METHOD_NAME);

        if (! $key) {
            return response('Misconfigured', 500);
        }

        try {
            if (! Redsys::check($key, $request->input())) {
                return response('Invalid signature', 400);
            }

            $encoded = $request->input('Ds_MerchantParameters');
            $parameters = Redsys::getMerchantParameters($encoded);

            $normalized = [];
            foreach ($parameters as $key => $value) {
                $normalized[strtolower((string) $key)] = $value;
            }

            $orderNumber = (string) ($normalized['ds_order'] ?? '');
            $responseCode = isset($normalized['ds_response']) ? (int) $normalized['ds_response'] : 999;

            if ($responseCode > 99) {
                Cache::forget(RedsysPaymentService::payloadCacheKey($orderNumber));

                return response('OK', 200);
            }

            $payload = Cache::get(RedsysPaymentService::payloadCacheKey($orderNumber));

            if (! $payload || $orderNumber === '') {
                Log::warning('Redsys notification: missing payload cache', ['order' => $orderNumber]);

                return response('OK', 200);
            }

            $orderIds = array_values(array_filter(
                array_map(static fn ($id) => (int) $id, Arr::wrap(Arr::get($payload, 'order_id'))),
                static fn (int $id) => $id > 0
            ));

            if ($orderIds === []) {
                Log::warning('Redsys notification: payload has no valid order_id', ['order' => $orderNumber]);

                return response('OK', 200);
            }

            $amountMinor = (int) ($normalized['ds_amount'] ?? 0);
            $amount = round($amountMinor / 100, 2);
            $currencyCode = RedsysHelper::numericToIso((string) ($normalized['ds_currency'] ?? '978'));

            $authCode = trim((string) ($normalized['ds_authorisationcode'] ?? ''));
            $chargeId = sprintf(
                'redsys-%s-%s',
                $orderNumber,
                $authCode !== '' ? $authCode : uniqid('', true)
            );

            $existing = app(PaymentInterface::class)->getFirstBy([
                'charge_id' => $chargeId,
            ]);

            if ($existing) {
                Cache::forget(RedsysPaymentService::payloadCacheKey($orderNumber));

                return response('OK', 200);
            }

            $paymentData = [
                'amount' => $amount,
                'currency' => $currencyCode,
                'order_id' => $orderIds,
                'customer_id' => Arr::get($payload, 'customer_id'),
                'customer_type' => Arr::get($payload, 'customer_type'),
            ];

            Cache::put(RedsysPaymentService::chargeCacheKey($orderNumber), $chargeId, now()->addMinutes(30));
            Cache::put(RedsysPaymentService::chargeDataCacheKey($chargeId), $paymentData, now()->addMinutes(60));

            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, array_merge($paymentData, [
                'charge_id' => $chargeId,
                'payment_channel' => REDSYS_PAYMENT_METHOD_NAME,
                'status' => PaymentStatusEnum::COMPLETED,
            ]));

            Cache::forget(RedsysPaymentService::payloadCacheKey($orderNumber));
        } catch (Throwable $exception) {
            Log::error('Redsys notification error', ['exception' => $exception]);

            return response('Error', 500);
        }

        return response('OK', 200);
    }

    public function ok(Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $next = PaymentHelper::getRedirectURL();
        $chargeId = null;

        $key = get_payment_setting('key', REDSYS_PAYMENT_METHOD_NAME);

        if ($key && $request->filled('Ds_MerchantParameters') && $request->filled('Ds_Signature')) {
            try {
                if (Redsys::check($key, $request->input())) {
                    $encoded = $request->input('Ds_MerchantParameters');
                    $parameters = Redsys::getMerchantParameters($encoded);

                    $normalized = [];
                    foreach ($parameters as $paramKey => $value) {
                        $normalized[strtolower((string) $paramKey)] = $value;
                    }

                    $orderNumber = (string) ($normalized['ds_order'] ?? '');
                    $responseCode = isset($normalized['ds_response']) ? (int) $normalized['ds_response'] : 999;

                    if ($orderNumber !== '' && $responseCode <= 99) {
                        $payload = Cache::get(RedsysPaymentService::payloadCacheKey($orderNumber));

                        if ($payload) {
                            $orderIds = array_values(array_filter(
                                array_map(static fn ($id) => (int) $id, Arr::wrap(Arr::get($payload, 'order_id'))),
                                static fn (int $id) => $id > 0
                            ));

                            if ($orderIds !== []) {
                                $amountMinor = (int) ($normalized['ds_amount'] ?? 0);
                                $amount = round($amountMinor / 100, 2);
                                $currencyCode = RedsysHelper::numericToIso((string) ($normalized['ds_currency'] ?? '978'));

                                $authCode = trim((string) ($normalized['ds_authorisationcode'] ?? ''));
                                $chargeId = sprintf(
                                    'redsys-%s-%s',
                                    $orderNumber,
                                    $authCode !== '' ? $authCode : uniqid('', true)
                                );

                                $payment = app(PaymentInterface::class)->getFirstBy([
                                    'charge_id' => $chargeId,
                                ]);

                                if (! $payment) {
                                    do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                                        'amount' => $amount,
                                        'currency' => $currencyCode,
                                        'charge_id' => $chargeId,
                                        'order_id' => $orderIds,
                                        'customer_id' => Arr::get($payload, 'customer_id'),
                                        'customer_type' => Arr::get($payload, 'customer_type'),
                                        'payment_channel' => REDSYS_PAYMENT_METHOD_NAME,
                                        'status' => PaymentStatusEnum::COMPLETED,
                                    ]);
                                }
                            }
                        }
                    }
                }
            } catch (Throwable $exception) {
                Log::warning('Redsys ok callback parse failed', ['exception' => $exception]);
            }
        }

        if (! $chargeId) {
            $merchantOrder = session()->pull('redsys_checkout_order');
            $chargeId = $merchantOrder ? Cache::pull(RedsysPaymentService::chargeCacheKey($merchantOrder)) : null;
        }

        if ($chargeId) {
            $payment = app(PaymentInterface::class)->getFirstBy([
                'charge_id' => $chargeId,
            ]);

            if (! $payment) {
                $cachedData = Cache::get(RedsysPaymentService::chargeDataCacheKey($chargeId));

                if ($cachedData && ! empty($cachedData['order_id'])) {
                    do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, array_merge($cachedData, [
                        'charge_id' => $chargeId,
                        'payment_channel' => REDSYS_PAYMENT_METHOD_NAME,
                        'status' => PaymentStatusEnum::COMPLETED,
                    ]));
                }
            }

            $next .= (str_contains($next, '?') ? '&' : '?') . 'charge_id=' . urlencode($chargeId);
        }

        return $response
            ->setNextUrl($next)
            ->setMessage(trans('plugins/redsys::redsys.messages.checkout_successfully'));
    }

    public function ko(BaseHttpResponse $response): BaseHttpResponse
    {
        session()->forget('redsys_checkout_order');

        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL())
            ->withInput()
            ->setMessage(trans('plugins/redsys::redsys.messages.payment_failed_or_cancelled'));
    }
}
