<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AgentApiController;
use Modules\Agent\Http\Controllers\AgentController;
use Modules\Agent\Http\Controllers\CashInController;
use Modules\Agent\Http\Controllers\CashOutController;

Route::middleware(['auth:sanctum','userCheckApi','agent'])->prefix('agent')->group(function () {

    Route::get('dashboard', [AgentApiController::class, 'dashboardApi']);
    Route::get('statements', [AgentApiController::class, 'statement']);

    Route::middleware('Ensure:cash_in')->controller(CashInController::class)
        ->prefix('cash-in')->as('cash_in.')
        ->group(function () {
            Route::get('/', 'cashInCreate');
            Route::post('init', 'cashIn');
            Route::post('check-recipient', 'checkRecipientApi');
            Route::post('check-amount', 'checkAmountApi');
            Route::get('preview/{trx_id}', 'preview');
            Route::post('confirm/{trx_id}', 'cashInConfirm');
        });

});


Route::middleware(['auth:sanctum','userCheckApi'])->prefix('user')->group(function () {

    Route::middleware('Ensure:cash_out')->group(function () {
        Route::controller(CashOutController::class)
            ->prefix('cash-out')->as('cash_out.')
            ->group(function () {
                Route::get('/', 'cashOutCreate');
                Route::post('init', 'cashOut');
                Route::post('check-agent', 'checkAgentApi');
                Route::post('check-amount', 'checkAmountApi');
                Route::get('preview/{trx_id}', 'preview');
                Route::post('confirm/{trx_id}', 'cashOutConfirm');
            });
    });

});
