<?php

use Illuminate\Support\Facades\Route;
use Modules\Merchant\Http\Controllers\AdminController;
use Modules\Merchant\Http\Controllers\AdminMerchantController;
use Modules\Merchant\Http\Controllers\MerchantController;


Route::middleware(['auth', 'verified', 'web', 'merchant'])->prefix('merchant')->as('merchant.')->group(function () {

    require base_path('routes/partials/user_global.php');

    Route::get('dashboard', [MerchantController::class, 'dashboard'])->name('dashboard');
    Route::get('ajax/load-chart/{type}', [MerchantController::class, 'loadChartData'])->name('get.load.chart');
    Route::get('qr-code', [MerchantController::class, 'qrCode'])->name('qr.code');

    Route::get('settings', [MerchantController::class, 'settings'])->name('settings.index');
    Route::post('settings-update', [MerchantController::class, 'settingsUpdate'])->name('settings.update');


    //Api settings
    Route::any('api-key', [MerchantController::class, 'apiKey'])->name('api.key');
    Route::get('api-key/mode-change/{mode}', [MerchantController::class, 'modeChange'])->name('mode.change');
    Route::any('api-documentation', [MerchantController::class, 'apiDocx'])->name('api.docx');

});


Route::middleware(['auth', 'web', 'userCheck', 'kyc','userType:user'])->prefix('user')->as('user.')->group(function () {
    Route::middleware('Ensure:make_payment')->group(function () {
        Route::controller(\Modules\Merchant\Http\Controllers\MakePaymentController::class)
            ->prefix('make-payment')->as('make_payment.')
            ->group(function () {
                Route::get('/', 'create')->name('create');
                Route::post('init', 'init')->name('init');
                Route::get('check-merchant', 'checkMerchant')->name('checkMerchant');
                Route::get('check-amount', 'checkAmount')->name('checkAmount');
                Route::get('preview/{trx_id}', 'preview')->name('preview');
                Route::post('confirm/{trx_id}', 'confirm')->name('confirm');
            });
    });
});


Route::prefix('admin')->middleware(['auth:admin', 'demo'])->as('admin.')->group(function () {
    Route::get('merchant/settings', [AdminController::class, 'settings'])->name('merchant.settings');
    Route::get('merchant/settings-create', [AdminController::class, 'settingsCreate'])->name('merchant.settings.create');
    Route::post('merchant/settings-store', [AdminController::class, 'settingsStore'])->name('merchant.settings.store');
    Route::get('merchant/settings-edit/{id}', [AdminController::class, 'settingsEdit'])->name('merchant.settings.edit');
    Route::put('merchant/settings-update/{id}', [AdminController::class, 'updateSettings'])->name('merchant.settings.update');
    Route::get('merchant/transactions', [AdminController::class, 'transactions'])->name('merchant.transactions');


    Route::get('merchants/{state?}', [AdminMerchantController::class, 'merchantIndex'])->name('merchants');
    Route::get('merchants-search/{state?}', [AdminMerchantController::class, 'merchantIndexSearch'])->name('merchants.search');


});
