<?php

use Illuminate\Support\Facades\Route;
use Modules\Agent\Http\Controllers\AdminController;
use Modules\Agent\Http\Controllers\AgentController;
use Modules\Agent\Http\Controllers\AgentTransactionController;
use Modules\Agent\Http\Controllers\CashInController;
use Modules\Agent\Http\Controllers\CashOutController;


Route::middleware(['auth', 'verified', 'web', 'agent'])->prefix('agent')->as('agent.')->group(function () {

    require base_path('routes/partials/user_global.php');

    Route::get('dashboard', [AgentController::class, 'dashboard'])->name('dashboard');
    Route::get('ajax/load-chart/{type}', [AgentController::class, 'loadChartData'])->name('get.load.chart');
    Route::get('qr-code', [AgentController::class, 'qrCode'])->name('qr.code');

    Route::get('statements', [AgentTransactionController::class, 'statement'])->name('statement.index');
    Route::get('statement-search', [AgentTransactionController::class, 'statementSearch'])->name('statement.search');

    Route::middleware('Ensure:cash_in')->controller(CashInController::class)
        ->prefix('cash-in')->as('cash_in.')
        ->group(function () {
            Route::get('/', 'cashInCreate')->name('create');
            Route::post('init', 'cashIn')->name('init');
            Route::get('check-recipient', 'checkRecipient')->name('checkRecipient');
            Route::get('check-amount', 'checkAmount')->name('checkAmount');
            Route::get('preview/{trx_id}', 'preview')->name('preview');
            Route::post('confirm/{trx_id}', 'cashInConfirm')->name('confirm');
        });
});


Route::middleware(['auth', 'web', 'userCheck', 'kyc', 'userType:user'])->prefix('user')->as('user.')->group(function () {
    Route::middleware('Ensure:cash_out')->group(function () {
        Route::controller(CashOutController::class)
            ->prefix('cash-out')->as('cash_out.')
            ->group(function () {
                Route::get('/', 'cashOutCreate')->name('create');
                Route::post('init', 'cashOut')->name('init');
                Route::get('check-agent', 'checkAgent')->name('checkAgent');
                Route::get('check-amount', 'checkAmount')->name('checkAmount');
                Route::get('preview/{trx_id}', 'preview')->name('preview');
                Route::post('confirm/{trx_id}', 'cashOutConfirm')->name('confirm');
            });
    });
});


Route::prefix('admin')->middleware(['auth:admin', 'demo'])->as('admin.')->group(function () {
    Route::get('agent/settings', [AdminController::class, 'settings'])->name('agent.settings');
    Route::get('agent/settings-create', [AdminController::class, 'settingsCreate'])->name('agent.settings.create');
    Route::post('agent/settings-store', [AdminController::class, 'settingsStore'])->name('agent.settings.store');
    Route::get('agent/settings-edit/{id}', [AdminController::class, 'settingsEdit'])->name('agent.settings.edit');
    Route::put('agent/settings-update/{id}', [AdminController::class, 'updateSettings'])->name('agent.settings.update');

    Route::get('agent/transactions', [AdminController::class, 'transactions'])->name('agent.transactions');



    Route::get('agents/{state?}', [AdminController::class, 'agentIndex'])->name('agents');
    Route::get('agents/search/{state?}', [AdminController::class, 'agentIndexSearch'])->name('agents.search');

});
