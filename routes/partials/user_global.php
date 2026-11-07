<?php

use App\Http\Controllers\FundController;
use App\Http\Controllers\InAppNotificationController;
use App\Http\Controllers\TwoFaSecurityController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\KycVerificationController;
use App\Http\Controllers\User\PayoutController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\SupportTicketController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\UserSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('userCheck')->group(function () {

    Route::get('toggle-layout', function () {
        $currentLayout = session('user_layout', 'horizontal');
        $newLayout = $currentLayout === 'vertical' ? 'horizontal' : 'vertical';
        session(['user_layout' => $newLayout]);
        return back();
    })->name('toggle.layout');


    Route::view('confirm-success', 'layouts.confirm_success')->name('confirm.success');
    Route::post('save-token', [HomeController::class, 'saveToken'])->name('save.token');


    Route::group(['prefix' => 'ticket', 'as' => 'ticket.'], function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('list');
        Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
        Route::post('/create', [SupportTicketController::class, 'store'])->name('store');
        Route::get('/view/{ticket}', [SupportTicketController::class, 'ticketView'])->name('view');
        Route::put('/reply/{ticket}', [SupportTicketController::class, 'reply'])->name('reply');
        Route::get('/download/{ticket}', [SupportTicketController::class, 'download'])->name('download');
    });

    /* TRANSACTION LIST*/
    Route::get('transaction-history', [TransactionController::class, 'index'])->name('transaction');
    Route::get('transaction-search', [TransactionController::class, 'search'])->name('transaction.search');

    /* ADD MONEY BY USER */
    Route::get('fund-history', [FundController::class, 'index'])->name('fund.index')->middleware('Ensure:deposit');

    /* PAYOUT REQUEST BY USER */
    Route::middleware('Ensure:payout')->group(function () {
        Route::get('payout-list', [PayoutController::class, 'index'])->name('payout.index');

        Route::get('payout', [PayoutController::class, 'payout'])->name('payout');
        Route::get('payout-supported-currency', [PayoutController::class, 'payoutSupportedCurrency'])
            ->name('payout.supported.currency');
        Route::get('payout-check-amount', [PayoutController::class, 'checkAmount'])->name('payout.checkAmount');
        Route::post('request-payout', [PayoutController::class, 'payoutRequest'])->name('payout.request');

        Route::match(['get', 'post'], 'confirm-payout/{trx_id}', [PayoutController::class, 'confirmPayout'])->name('payout.confirm');
        Route::post('confirm-payout/flutterwave/{trx_id}', [PayoutController::class, 'flutterwavePayout'])->name('payout.flutterwave');
        Route::post('confirm-payout/paystack/{trx_id}', [PayoutController::class, 'paystackPayout'])->name('payout.paystack');
        Route::get('payout-check-limit', [PayoutController::class, 'checkLimit'])->name('payout.checkLimit');
        Route::post('payout-bank-form', [PayoutController::class, 'getBankForm'])->name('payout.getBankForm');
        Route::post('payout-bank-list', [PayoutController::class, 'getBankList'])->name('payout.getBankList');
    });

    /* ===== Kyc Verification ===== */
    Route::get('verification/kyc', [KycVerificationController::class, 'kyc'])->name('verification.kyc');
    Route::get('verification/kyc-form/{id}', [KycVerificationController::class, 'kycForm'])->name('verification.kyc.form');
    Route::post('verification/kyc/submit', [KycVerificationController::class, 'verificationSubmit'])->name('kyc.verification.submit');
    Route::get('verification/kyc/history', [KycVerificationController::class, 'history'])->name('kyc.history');

    //USER PROFILE UPDATE
    Route::controller(ProfileController::class)->group(function () {
        Route::match(['get', 'post'], 'profile', 'index')->name('profile');
        Route::post('change-password', 'changePassword')->name('change.password');
    });

    /* ===== Push Notification ===== */
    Route::get('ajax/push-notification-show', [InAppNotificationController::class, 'show'])->name('push.notification.show');
    Route::get('ajax/push.notification.readAll', [InAppNotificationController::class, 'readAll'])->name('push.notification.readAll');
    Route::get('ajax/push-notification-readAt/{id}', [InAppNotificationController::class, 'readAt'])->name('push.notification.readAt');

    // Manage user notifications
    Route::get('notifications', [UserSettingController::class, 'notifications'])->name('notifications');
    Route::post('notifications-change', [UserSettingController::class, 'notifyPermissionChange'])->name('notificationPermission');

    /* ===== Manage Two Step ===== */
    Route::get('two-step-security', [TwoFaSecurityController::class, 'twoStepSecurity'])->name('twostep.security');
    Route::post('twoStep-enable', [TwoFaSecurityController::class, 'twoStepEnable'])->name('twoStepEnable');
    Route::post('twoStep-disable', [TwoFaSecurityController::class, 'twoStepDisable'])->name('twoStepDisable');
    Route::post('twoStep/re-generate', [TwoFaSecurityController::class, 'twoStepRegenerate'])->name('twoStepRegenerate');


    /* MANAGE SECURITY PIN BY USER */
    Route::get('security-pin/create', [TwoFaSecurityController::class, 'create'])->name('securityPin.create');
    Route::post('security-pin/store', [TwoFaSecurityController::class, 'store'])->name('securityPin.store');
    Route::match(['get', 'post'], 'security-pin/reset', [TwoFaSecurityController::class, 'reset'])->name('securityPin.reset');
    Route::match(['get', 'post'], 'security-pin/manage', [TwoFaSecurityController::class, 'manage'])->name('securityPin.manage');


});
