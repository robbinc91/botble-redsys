<?php

namespace Botble\Redsys;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_redsys_name',
            'payment_redsys_description',
            'payment_redsys_merchant_code',
            'payment_redsys_terminal',
            'payment_redsys_key',
            'payment_redsys_environment',
            'payment_redsys_trade_name',
            'payment_redsys_status',
        ]);
    }
}
