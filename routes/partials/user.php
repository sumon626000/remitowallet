<?php

use App\Http\Controllers\CommissionEntryController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TransferController;
use App\Http\Controllers\User\RequestMoneyController;
use App\Http\Controllers\User\ExchangeController;
use App\Http\Controllers\User\RedeemCodeController;
use App\Http\Controllers\User\EscrowController;
use App\Http\Controllers\User\DisputeController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\VirtualCardController;
use App\Http\Controllers\User\VoucherController;
use App\Http\Controllers\User\StoreCategoryController;
use App\Http\Controllers\User\StoreController;
use App\Http\Controllers\User\StoreProductAttrController;
use App\Http\Controllers\User\StoreShippingController;
use App\Http\Controllers\User\StoreProductController;
use App\Http\Controllers\User\UserSettingController;
use App\Http\Controllers\User\StoreProductStockController;
use App\Http\Controllers\User\ProductOrderController;
use App\Http\Controllers\User\PayBillController;
use App\Http\Controllers\User\PlayGameController;
use App\Http\Controllers\User\AllStocksController;

Route::middleware(['userCheck'])->group(function () {

    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::middleware(['kyc','userType:user'])->group(function () {

        /*user-dashboard-charts*/
        Route::get('ajax/get-activity-chart', [HomeController::class, 'getActivityChart'])->name('get.activity.chart');
        Route::get('ajax/transfer-request-summary', [HomeController::class, 'getTransferRequestSummary'])->name('get.transfer.request.summary');
        Route::get('ajax/transaction-daily-count', [HomeController::class, 'getDailyTransactionCount'])->name('get.transaction.count');
        Route::get('ajax/get-transfer-chart', [HomeController::class, 'getTransferChart'])->name('get.transfer.chart');
        Route::get('ajax/load-chart/{type}', [HomeController::class, 'loadChartData'])->name('get.load.chart');

        Route::get('commission-list', [CommissionEntryController::class, 'index'])->name('commission.index');

        /* PLAY GAME */
        Route::get('play-game', [PlayGameController::class, 'index'])->name('play.game');
        Route::post('play-game/fx/bet', [PlayGameController::class, 'fxBet'])->name('play.game.fx.bet');
        Route::post('play-game/royal/start', [PlayGameController::class, 'royalStart'])->name('play.game.royal.start');
        Route::post('play-game/royal/climb', [PlayGameController::class, 'royalClimb'])->name('play.game.royal.climb');
        Route::post('play-game/royal/cash', [PlayGameController::class, 'royalCash'])->name('play.game.royal.cash');
        Route::post('play-game/bot/bet', [PlayGameController::class, 'botBet'])->name('play.game.bot.bet');
        Route::get('play-game/stocks/state', [AllStocksController::class, 'state'])->name('play.game.stocks.state');
        Route::post('play-game/stocks/open', [AllStocksController::class, 'open'])->name('play.game.stocks.open');
        Route::get('play-game/{slug}', [PlayGameController::class, 'play'])->name('play.game.show');

        /* SEND MONEY BY USER */
        Route::middleware('Ensure:transfer')->group(function () {
            Route::get('transfer-history', [TransferController::class, 'index'])->name('transfer.index');
            Route::get('transfer-history/search', [TransferController::class, 'search'])->name('transfer.search');
            Route::match(['get', 'post'], 'transfer', [TransferController::class, 'initialize'])->name('transfer.initialize');
            Route::match(['get', 'post'], 'confirm-transfer/{utr}', [TransferController::class, 'confirmTransfer'])->name('transfer.confirm');
            Route::get('transfer-check-recipient', [TransferController::class, 'checkRecipient'])->name('transfer.checkRecipient');
            Route::get('transfer-check-amount', [TransferController::class, 'checkAmount'])->name('transfer.checkAmount');
        });

        /* REQUEST MONEY BY USER */
        Route::middleware('Ensure:request')->group(function () {
            Route::get('request-money-history', [RequestMoneyController::class, 'index'])->name('requestMoney.index');
            Route::get('request-money/search', [RequestMoneyController::class, 'search'])->name('requestMoney.search');
            Route::match(['get', 'post'], 'request-money', [RequestMoneyController::class, 'initialize'])->name('requestMoney.initialize');
            Route::get('requestMoney-check-recipient', [RequestMoneyController::class, 'checkRecipient'])->name('requestMoney.checkRecipient');
            Route::get('requestMoney-check-amount', [RequestMoneyController::class, 'checkInitiateAmount'])->name('requestMoney.checkAmount');
            Route::match(['get', 'post'], 'check-request-money/{utr}', [RequestMoneyController::class, 'checkRequestMoney'])->name('requestMoney.check');
            Route::match(['get', 'post'], 'confirm-request-money/{utr}', [RequestMoneyController::class, 'confirmRequestMoney'])->name('requestMoney.confirm');
            Route::get('cancel-request-money/{utr}', [RequestMoneyController::class, 'cancelRequestMoney'])->name('requestMoney.cancel');

        });

        /* EXCHANGE CURRENCY */
        Route::middleware('Ensure:exchange')->group(function () {
            Route::get('exchange-history', [ExchangeController::class, 'index'])->name('exchange.index');
            Route::get('exchange-search', [ExchangeController::class, 'search'])->name('exchange.search');
            Route::match(['get', 'post'], 'exchange', [ExchangeController::class, 'initialize'])->name('exchange.initialize');
            Route::match(['get', 'post'], 'confirm-exchange/{utr}', [ExchangeController::class, 'confirmExchange'])->name('exchange.confirm');
            Route::get('currencies-except-selected', [ExchangeController::class, 'currenciesExceptSelected'])->name('currencies.except.selected');
            Route::get('exchange-check-amount', [ExchangeController::class, 'checkAmount'])->name('exchange.checkAmount');
        });

        /* REDEEM CODE BY USER */
        Route::middleware('Ensure:redeem')->group(function () {
            Route::get('redeem-history', [RedeemCodeController::class, 'index'])->name('redeem.index');
            Route::get('redeem-list/search', [RedeemCodeController::class, 'search'])->name('redeem.search');
            Route::match(['get', 'post'], 'generate-redeem-code', [RedeemCodeController::class, 'initialize'])->name('redeem.initialize');
            Route::match(['get', 'post'], 'generate-redeem-confirm/{utr}', [RedeemCodeController::class, 'confirmGenerate'])->name('redeem.confirm');
            Route::match(['get', 'post'], 'insert-redeem-code', [RedeemCodeController::class, 'insertRedeemCode'])->name('redeem.insert');
            Route::get('redeem-check-amount', [RedeemCodeController::class, 'checkAmount'])->name('redeem.checkAmount');
        });

        /* ESCROW BY USER */
        Route::middleware('Ensure:escrow')->group(function () {
            Route::get('escrow-history', [EscrowController::class, 'index'])->name('escrow.index');
            Route::get('escrow-search', [EscrowController::class, 'search'])->name('escrow.search');
            Route::match(['get', 'post'], 'escrow-create-request', [EscrowController::class, 'create'])->name('escrow.createRequest');
            Route::match(['get', 'post'], 'escrow-confirm-init/{utr}', [EscrowController::class, 'confirmInit'])->name('escrow.confirmInit');
            Route::get('escrow-check-recipient', [EscrowController::class, 'checkRecipient'])->name('escrow.checkRecipient');
            Route::get('escrow-check-init-amount', [EscrowController::class, 'checkInitiateAmount'])->name('escrow.checkInitiateAmount');
        });

        /* payment and disbursed request action from here */
        Route::match(['get', 'post'], 'escrow-payment-view/{utr}', [EscrowController::class, 'escrowPaymentView'])
            ->name('escrow.paymentView');

        /* USER DISPUTE */
        Route::get('dispute-history', [DisputeController::class, 'index'])->name('dispute.index');
        Route::get('dispute-list/search', [DisputeController::class, 'search'])->name('dispute.search');
        Route::match(['get', 'put'], 'dispute-view/{utr}', [DisputeController::class, 'userDisputeView'])->name('dispute.view');
        Route::get('dispute-file-download/{utr}/{file}', [DisputeController::class, 'userDisputeDownload'])->name('dispute.file.download');

        /* Virtual Card */
        Route::middleware('Ensure:virtual_card')->group(function () {
            Route::get('virtual-card', [VirtualCardController::class, 'index'])->name('virtual.card');
            Route::get('virtual-card/order', [VirtualCardController::class, 'order'])->name('virtual.card.order');
            Route::post('virtual-card/order/submit', [VirtualCardController::class, 'orderSubmit'])->name('virtual.card.orderSubmit');
            Route::match(['get', 'post'], 'virtual-card/confirm/{utr}', [VirtualCardController::class, 'confirmOrder'])->name('order.confirm');
            Route::post('virtual-card/order/payment/{orderId}', [VirtualCardController::class, 'cardOrderPayment'])->name('virtual.card.orderPayment');
            Route::any('virtual-card/order/re-submit', [VirtualCardController::class, 'orderReSubmit'])->name('virtual.card.orderReSubmit');

            Route::post('virtual-card/block/{id}', [VirtualCardController::class, 'cardBlock'])->name('virtual.cardBlock');
            Route::get('virtual-card/transaction/{id}', [VirtualCardController::class, 'cardTransaction'])->name('virtual.cardTransaction');
            Route::get('virtual-card/search/{id}', [VirtualCardController::class, 'cardTransactionSearch'])->name('virtual.cardTransactionSearch');
        });

        /* VOUCHER PAYMENT BY USER */
        Route::middleware('Ensure:voucher')->group(function () {
            Route::get('voucher-history', [VoucherController::class, 'index'])->name('voucher.index');
            Route::get('voucher-search', [VoucherController::class, 'search'])->name('voucher.search');
            Route::match(['get', 'post'], 'voucher-create-request', [VoucherController::class, 'create'])->name('voucher.createRequest');
            Route::match(['get', 'post'], 'voucher-confirm-init/{utr}', [VoucherController::class, 'confirmInit'])->name('voucher.confirmInit');
            Route::get('voucher-check-recipient', [VoucherController::class, 'checkRecipient'])->name('voucher.checkRecipient');
            Route::get('voucher-check-init-amount', [VoucherController::class, 'checkInitiateAmount'])->name('voucher.checkInitiateAmount');
            Route::match(['get', 'post'], 'voucher-payment-view/{utr}', [VoucherController::class, 'voucherPaymentView'])->name('voucher.paymentView');
        });

        /* Invoice Payment */
        Route::middleware('Ensure:invoice')->group(function () {
            Route::get('invoice/list', [InvoiceController::class, 'index'])->name('invoice.index')->middleware('Ensure:invoice');
            Route::get('invoice/search', [InvoiceController::class, 'search'])->name('invoice.search')->middleware('Ensure:invoice');
            Route::get('/invoice/view/{id}', [InvoiceController::class, 'viewInvoice'])->name('invoice.view')->middleware('Ensure:invoice');
            Route::post('/invoice/reminder', [InvoiceController::class, 'invoiceReminder'])->name('invoiceReminder')->middleware('Ensure:invoice');
            Route::get('/invoice/generate-pdf', [InvoiceController::class, 'generatePdf'])->name('generatePdf')->middleware('Ensure:invoice');
            Route::get('/invoice', [InvoiceController::class, 'create'])->name('invoice.create');
            Route::post('/invoice/store', [InvoiceController::class, 'store'])->name('invoice.store')->middleware('Ensure:invoice');
            Route::post('/currency/check', [InvoiceController::class, 'currencyCheck'])->name('currency.check')->middleware('Ensure:invoice');
        });

        /*store middleware check*/
        Route::middleware('Ensure:store')->group(function () {
            /* Store Category */
            Route::get('store/category/list', [StoreCategoryController::class, 'categoryList'])->name('category.list');
            Route::get('store/category/list/search', [StoreCategoryController::class, 'categoryListSearch'])->name('category.list.search');
            Route::post('store/category/save', [StoreCategoryController::class, 'categorySave'])->name('category.save');
            Route::post('store/category/update/{id}', [StoreCategoryController::class, 'categoryUpdate'])->name('category.update');
            Route::delete('store/category/delete/{id}', [StoreCategoryController::class, 'categoryDelete'])->name('category.delete');

            /* Store Management */
            Route::get('store/list', [StoreController::class, 'storeList'])->name('store.list');
            Route::get('store/list/search', [StoreController::class, 'storeListSearch'])->name('store.list.search');
            Route::any('store/create', [StoreController::class, 'storeCreate'])->name('store.create');
            Route::any('store/edit/{id}', [StoreController::class, 'storeEdit'])->name('store.edit');
            Route::delete('store/delete/{id}', [StoreController::class, 'storeDelete'])->name('store.delete');
            Route::post('store/link/check', [StoreController::class, 'storeLinkCheck'])->name('store.link.check');

            /* Store Product Attribute */
            Route::get('store/product/attribute', [StoreProductAttrController::class, 'attrList'])->name('attr.list');
            Route::any('store/product/attribute/create', [StoreProductAttrController::class, 'attrCreate'])->name('attr.create');
            Route::any('store/product/attribute/edit/{id}', [StoreProductAttrController::class, 'attrEdit'])->name('attr.edit');
            Route::delete('store/product/attribute/delete/{id}', [StoreProductAttrController::class, 'attrDelete'])->name('attr.delete');

            /* Store Product */
            Route::get('store/product/list', [StoreProductController::class, 'productList'])->name('product.list');
            Route::get('store/product/list/search', [StoreProductController::class, 'productListSearch'])->name('product.list.search');
            Route::any('store/product/create', [StoreProductController::class, 'productCreate'])->name('product.create');
            Route::get('store/product/view/{id}', [StoreProductController::class, 'productView'])->name('product.view');
            Route::any('store/product/edit/{id}', [StoreProductController::class, 'productEdit'])->name('product.edit');
            Route::delete('store/product/image/delete/{id}', [StoreProductController::class, 'productImageDelete'])->name('product.image.delete');
            Route::delete('store/product/delete/{id}', [StoreProductController::class, 'productDelete'])->name('product.delete');

            /* Product Stock */
            Route::get('store/product/stock/list', [StoreProductStockController::class, 'stockList'])->name('stock.list');
            Route::get('store/product/stock/list/search', [StoreProductStockController::class, 'stockListSearch'])->name('stock.list.search');
            Route::any('store/product/stock/create', [StoreProductStockController::class, 'stockCreate'])->name('stock.create');
            Route::get('store/product/stock/view/{productId}', [StoreProductStockController::class, 'stockEdit'])->name('stock.view');
            Route::get('store/product/stock-edit/fetch', [StoreProductStockController::class, 'stockEditFetch'])->name('stock.edit.fetch');
            Route::get('store/product/stock-attr/fetch', [StoreProductStockController::class, 'stockAttrFetch'])->name('stock.attr.fetch');

            /* Store Shipping */
            Route::get('store/shipping/list', [StoreShippingController::class, 'shippingList'])->name('shipping.list');
            Route::post('store/shipping/save', [StoreShippingController::class, 'shippingSave'])->name('shipping.save');
            Route::post('store/shipping/update', [StoreShippingController::class, 'shippingUpdate'])->name('shipping.update');
            Route::delete('store/shipping/delete/{id}', [StoreShippingController::class, 'shippingDelete'])->name('shipping.delete');

            /* Product Order */
            Route::get('store/product/order', [ProductOrderController::class, 'orderList'])->name('order.list');
            Route::get('store/product/order/search', [ProductOrderController::class, 'orderListSearch'])->name('order.list.search');
            Route::get('store/product/order/view/{orderNumber}', [ProductOrderController::class, 'orderView'])->name('order.view');
            Route::post('store/product/order/stage/change', [ProductOrderController::class, 'stageChange'])->name('order.stage.change');
            Route::post('store/product/order/single/stage/change/{orderId}', [ProductOrderController::class, 'singleStageChange'])->name('single.stage.change');
        });

        /* PAY Bill BY USER */
        Route::middleware('Ensure:bill_payment')->group(function () {
            Route::get('pay-bill', [PayBillController::class, 'payBill'])->name('pay.bill');
            Route::post('pay-bill/submit', [PayBillController::class, 'payBillSubmit'])->name('pay.bill.submit');
            Route::any('pay-bill/confirm/{utr}', [PayBillController::class, 'payBillConfirm'])->name('pay.bill.confirm');
            Route::post('pay-bill/fetch-services', [PayBillController::class, 'fetchServices'])->name('fetch.services');
            Route::post('pay-bill/fetch-amount', [PayBillController::class, 'fetchAmount'])->name('fetch.amount');
            Route::get('pay-bill/list', [PayBillController::class, 'payBillList'])->name('pay.bill.list');
        });

        Route::any('currency-setting', [UserSettingController::class, 'index'])->name('currency.setting');

        /* USER QR PAYMENT */
        Route::get('qr-payment-list', [HomeController::class, 'qrPaymentList'])->name('qr.payment')
            ->middleware('Ensure:qr_payment');
        Route::get('qr-payment-list/search', [HomeController::class, 'qrPaymentListSearch'])
            ->name('qr.payment.search')->middleware('Ensure:qr_payment');
        Route::get('qr-code', [HomeController::class, 'qrCode'])->name('qr.code')->middleware('Ensure:qr_payment');

    });

});

require base_path('routes/partials/user_global.php');




