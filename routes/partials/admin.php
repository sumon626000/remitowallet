<?php

use App\Http\Controllers\Admin\AdminDisputeController;
use App\Http\Controllers\Admin\AdminEscrowController;
use App\Http\Controllers\Admin\AdminExchangeController;
use App\Http\Controllers\Admin\AdminQrPaymentController;
use App\Http\Controllers\Admin\AdminRedeemCodeController;
use App\Http\Controllers\Admin\AdminRequestMoneyController;
use App\Http\Controllers\Admin\AdminTransferController;
use App\Http\Controllers\Admin\AdminVoucherController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\Admin\BillMethodController;
use App\Http\Controllers\Admin\BillServiceController;
use App\Http\Controllers\Admin\BillPayController;
use Illuminate\Support\Facades\Route;

$basicControl = basicControl();

Route::group(['prefix' => $basicControl->admin_prefix ?? 'admin', 'as' => 'admin.'], function () {
    Route::middleware(['auth:admin','demo'])->group(function () {

        //TRANSFER LIST
        Route::middleware('module:2')->group(function () {
            Route::get('transfer-list', [AdminTransferController::class, 'index'])->name('transfer.index');
            Route::get('transfer-list/search', [AdminTransferController::class, 'search'])->name('transfer.search');
        });

        //REQUEST MONEY
        Route::middleware('module:15')->group(function () {
            Route::get('request-money-list', [AdminRequestMoneyController::class, 'index'])->name('requestMoney.index');
            Route::get('request-money/search', [AdminRequestMoneyController::class, 'search'])->name('requestMoney.search');
        });

        //EXCHANGE MONEY
        Route::middleware('module:3')->group(function () {
            Route::get('exchange-list', [AdminExchangeController::class, 'index'])->name('exchange.index');
            Route::get('exchange-list/search', [AdminExchangeController::class, 'search'])->name('exchange.search');
        });

        //REDEEM CODE
        Route::middleware('module:4')->group(function () {
            Route::get('redeem-list', [AdminRedeemCodeController::class, 'index'])->name('redeem.index');
            Route::get('redeem-list/search', [AdminRedeemCodeController::class, 'search'])->name('redeem.search');
        });

        //ESCROW
        Route::middleware('module:5')->group(function () {
            Route::get('escrow-list', [AdminEscrowController::class, 'index'])->name('escrow.index');
            Route::get('escrow-list/search', [AdminEscrowController::class, 'search'])->name('escrow.search');
        });


        //DISPUTE
        Route::middleware('module:16')->group(function () {
            Route::get('dispute-list', [AdminDisputeController::class, 'index'])->name('dispute.index');
            Route::get('dispute-list/search', [AdminDisputeController::class, 'search'])->name('dispute.search');
            Route::put('defender-mute-unmute/{utr}/{option}', [AdminDisputeController::class, 'defenderMuteUnmute'])->name('defender.mute.unmute');
            Route::put('claimer-mute-unmute/{utr}/{option}', [AdminDisputeController::class, 'claimerMuteUnmute'])->name('claimer.mute.unmute');
            Route::put('dispute-status-change/{utr}/{option}', [AdminDisputeController::class, 'disputeStatusChange'])->name('dispute.status.change');

            Route::match(['get', 'put'], 'dispute-view/{utr}', [AdminDisputeController::class, 'adminDisputeView'])->name('dispute.view');
        });

        //QR PAYMENT
        Route::middleware('module:17')->group(function () {
            Route::get('qr-payment-list', [AdminQrPaymentController::class, 'index'])->name('qr.payment');
            Route::get('qr-payment-list/search', [AdminQrPaymentController::class, 'search'])->name('qr.payment.search');
        });

        //VOUCHER
        Route::middleware('module:6')->group(function () {
            Route::get('voucher-list', [AdminVoucherController::class, 'index'])->name('voucher.index');
            Route::get('voucher-list/search', [AdminVoucherController::class, 'search'])->name('voucher.search');
        });

        //INVOICE
        Route::middleware('module:9')->group(function () {
            Route::get('invoice-list', [AdminInvoiceController::class, 'index'])->name('invoice.index');
            Route::get('invoice-list/search', [AdminInvoiceController::class, 'search'])->name('invoice.search');
        });

        //STORE MANAGEMENT
        Route::middleware('module:7')->group(function () {
            Route::get('store-list', [AdminProductController::class, 'storeList'])->name('store.list');
            Route::get('store-list/search', [AdminProductController::class, 'storeListSearch'])->name('store.list.search');
            Route::get('store/list/view/{id}', [AdminProductController::class, 'storeView'])->name('store.view');
            Route::get('store/product/list', [AdminProductController::class, 'productList'])->name('product.list');
            Route::get('store/product/list/search', [AdminProductController::class, 'productListSearch'])->name('product.list.search');
            Route::get('store/product/view/{id}', [AdminProductController::class, 'productView'])->name('product.view');
            Route::get('store/product/order', [AdminProductController::class, 'orderList'])->name('order.list');
            Route::get('store/product/order/search', [AdminProductController::class, 'orderListSearch'])->name('order.list.search');
            Route::get('store/product/order/view/{orderNumber}', [AdminProductController::class, 'orderView'])->name('order.view');
            Route::post('store/product/order/stage/{orderId}', [AdminProductController::class, 'singleStageChange'])->name('single.stage.change');
            Route::get('store/contact/list', [AdminProductController::class, 'contactList'])->name('contact.list');
            Route::get('store/contact/list/search', [AdminProductController::class, 'contactListSearch'])->name('contact.list.search');
        });


        //VIRTUAL CARD
        Route::middleware('module:10')->group(function () {
            Route::get('virtual-card', [VirtualCardController::class, 'index'])->name('virtual.card');
            Route::get('virtual-card/edit/{id}', [VirtualCardController::class, 'edit'])->name('virtual.cardEdit');
            Route::put('virtual-card/update/{id}', [VirtualCardController::class, 'update'])->name('virtual.cardUpdate');
            Route::get('virtual-card/status-change/{id}', [VirtualCardController::class, 'statusChange'])->name('virtual.cardStatusCng');

            Route::get('virtual-card/order', [VirtualCardController::class, 'cardOrder'])->name('virtual.cardOrder');
            Route::get('virtual-card/order/search', [VirtualCardController::class, 'cardOrderSearch'])->name('virtual.cardOrder.search');
            Route::get('virtual-card/order/detail/{id}', [VirtualCardController::class, 'cardOrderDetail'])->name('virtual.cardOrderDetail');
            Route::post('virtual-card/order/rejected/{id}', [VirtualCardController::class, 'cardOrderRejected'])->name('virtual.cardOrderRejected');
            Route::get('virtual-card/order/approve/{id}', [VirtualCardController::class, 'cardOrderApprove'])->name('virtual.cardOrderApprove');

            Route::get('virtual-card/list/{type?}', [VirtualCardController::class, 'cardList'])->name('virtual.cardList');
            Route::get('virtual-card/list/search/{type?}', [VirtualCardController::class, 'cardListSearch'])->name('virtual.cardList.search');

            Route::get('virtual-card/list/view/{id}', [VirtualCardController::class, 'cardView'])->name('virtual.cardView');
            Route::get('virtual-card/transaction/{id}', [VirtualCardController::class, 'cardTransaction'])->name('virtual.cardTransaction');
            Route::post('virtual-card/block/{id}', [VirtualCardController::class, 'cardBlock'])->name('virtual.cardBlock');
            Route::post('virtual-card/un-block/{id}', [VirtualCardController::class, 'cardUnBlock'])->name('virtual.cardUnBlock');
            Route::post('virtual-card/fund-approve/{id}', [VirtualCardController::class, 'cardFundApprove'])->name('virtual.cardFundApprove');
            Route::post('virtual-card/fund-return/{id}', [VirtualCardController::class, 'cardFundReturn'])->name('virtual.cardFundReturn');
        });

        //BILL MODULE
        Route::middleware('module:11')->group(function () {
            /*Bill method*/
            Route::get('bill-method-list', [BillMethodController::class, 'index'])->name('bill.method.list');
            Route::get('bill-method-edit/{id}', [BillMethodController::class, 'edit'])->name('bill.method.edit');
            Route::post('bill-method-edit/{id}', [BillMethodController::class, 'save'])->name('bill.method.save');
            Route::get('bill-method-activated/{id}', [BillMethodController::class, 'activated'])->name('bill.method.activated');
            Route::put('bill-service-convert/{id}', [BillMethodController::class, 'serviceRate'])->name('bill.service.rate');

            /* Bill Operators Routes */
            Route::post('fetch/operators', [BillServiceController::class, 'fetchOperators'])->name('bill.fetch.operators');
            Route::post('fetch/balance', [BillServiceController::class, 'fetchBalance'])->name('fetch.balance');

            /* Bill Services Routes */
            Route::get('api/bill-service/{id}', [BillServiceController::class, 'fetchServices'])->name('bill.fetch.service');
            Route::post('api/add-service', [BillServiceController::class, 'addServices'])->name('bill.add.service');
            Route::post('api/add-service/bulk', [BillServiceController::class, 'addServicesBulk'])->name('bill.add.bulk.service');

            Route::get('bill-service-list', [BillServiceController::class, 'serviceList'])->name('bill.service.list');
            Route::get('bill-service-list/search', [BillServiceController::class, 'serviceListSearch'])->name('bill.service.list.search');
            Route::post('bill-charge-limit/add', [BillServiceController::class, 'chargeLimitAdd'])->name('bill.chargeLimit.add');
            Route::post('bill-charge-limit/{id}', [BillServiceController::class, 'chargeLimitEdit'])->name('bill.chargeLimit.edit');
            Route::post('bill-service-change/{id}', [BillServiceController::class, 'statusChange'])->name('bill.status.change');

            /* Bill Pay List */
            Route::get('bill-pay-list/{type?}', [BillPayController::class, 'billPayList'])->name('bill.pay.list');
            Route::get('bill-pay-list/search/{type?}', [BillPayController::class, 'billPaySearch'])->name('billPay.search');
            Route::get('bill-pay-list/view/{utr}', [BillPayController::class, 'billPayView'])->name('bill.pay.view');
            Route::post('bill-pay/confirm/{utr}', [BillPayController::class, 'billPayConfirm'])->name('bill.pay.confirm');
            Route::post('bill-pay/return/{utr}', [BillPayController::class, 'billPayReturn'])->name('bill.pay.return');
        });

    });
});
