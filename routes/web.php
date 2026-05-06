<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Redsys\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::get('payment/redsys/checkout', 'RedsysController@checkout')->name('payments.redsys.checkout');
    Route::post('payment/redsys/notification', 'RedsysController@notification')->name('payments.redsys.notification');
    Route::get('payment/redsys/ok', 'RedsysController@ok')->name('payments.redsys.ok');
    Route::get('payment/redsys/ko', 'RedsysController@ko')->name('payments.redsys.ko');
});
