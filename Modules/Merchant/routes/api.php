<?php

use Illuminate\Support\Facades\Route;
use Modules\Merchant\Http\Controllers\MerchantController;



Route::middleware(['auth:sanctum','userCheckApi','merchant'])->prefix('merchant')->group(function () {

    Route::get('dashboard', [MerchantController::class, 'dashboard']);
    Route::get('settings', [MerchantController::class, 'settings']);
    Route::post('settings-update', [MerchantController::class, 'settingsUpdate']);

});

Route::middleware(['auth:sanctum','userCheckApi'])->group(function () {

    Route::middleware('Ensure:make_payment')->group(function () {
        Route::controller(\Modules\Merchant\Http\Controllers\MakePaymentController::class)
            ->prefix('make-payment')->as('make_payment.')
            ->group(function () {
                Route::post('check-merchant', 'checkMerchantApi');
                Route::post('check-amount', 'checkAmountApi');
                Route::post('init', 'init');
                Route::get('preview/{trx_id}', 'preview');
                Route::post('confirm/{trx_id}', 'confirm');
            });
    });

});
