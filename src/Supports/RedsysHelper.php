<?php

namespace Botble\Redsys\Supports;

class RedsysHelper
{
    /** ISO 4217 alpha => Redsys numeric currency codes */
    public static function isoToNumeric(string $currency): ?string
    {
        return match (strtoupper(trim($currency))) {
            'EUR' => '978',
            'USD' => '840',
            'GBP' => '826',
            'CHF' => '756',
            'MXN' => '484',
            'CLP' => '152',
            'CAD' => '124',
            'AUD' => '036',
            'PLN' => '985',
            'RON' => '946',
            'SEK' => '752',
            'NOK' => '578',
            'DKK' => '208',
            'CZK' => '203',
            'HUF' => '348',
            'ISK' => '352',
            'TRY' => '949',
            'BRL' => '986',
            'ARS' => '032',
            'PEN' => '604',
            'CNY' => '156',
            'JPY' => '392',
            default => null,
        };
    }

    public static function numericToIso(string $numeric): string
    {
        $numeric = trim((string) $numeric);

        return match ($numeric) {
            '978' => 'EUR',
            '840' => 'USD',
            '826' => 'GBP',
            '756' => 'CHF',
            '484' => 'MXN',
            '152' => 'CLP',
            '124' => 'CAD',
            '036' => 'AUD',
            '985' => 'PLN',
            '946' => 'RON',
            '752' => 'SEK',
            '578' => 'NOK',
            '208' => 'DKK',
            '203' => 'CZK',
            '348' => 'HUF',
            '352' => 'ISK',
            '949' => 'TRY',
            '986' => 'BRL',
            '032' => 'ARS',
            '604' => 'PEN',
            '156' => 'CNY',
            '392' => 'JPY',
            default => 'EUR',
        };
    }

    /** @return array<int, string> */
    public static function supportedCurrencyCodes(): array
    {
        return [
            'EUR',
            'USD',
            'GBP',
            'CHF',
            'MXN',
            'CLP',
            'CAD',
            'AUD',
            'PLN',
            'RON',
            'SEK',
            'NOK',
            'DKK',
            'CZK',
            'HUF',
            'ISK',
            'TRY',
            'BRL',
            'ARS',
            'PEN',
            'CNY',
            'JPY',
        ];
    }
}
