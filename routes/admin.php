<?php

use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\BasicControlController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailConfigController;
use App\Http\Controllers\Admin\FirebaseConfigController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\ManageMenuController;
use App\Http\Controllers\Admin\ManualGatewayController;
use App\Http\Controllers\Admin\PaymentLogController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PayoutLogController;
use App\Http\Controllers\Admin\PayoutMethodController;
use App\Http\Controllers\Admin\PluginController;
use App\Http\Controllers\Admin\PusherConfigController;
use App\Http\Controllers\Admin\RolesPermissionController;
use App\Http\Controllers\Admin\SmsConfigController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\TransactionLogController;
use App\Http\Controllers\Admin\TranslateAPISettingController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\CommissionEntryController;
use App\Http\Controllers\InAppNotificationController;
use App\Http\Controllers\ReferralBonusController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminProfileSettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\MaintenanceModeController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\SecurityQuestionController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\ChargesLimitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




use Faker\Factory as Faker;

Route::get('migrate', function () {
    Illuminate\Support\Facades\Artisan::call('migrate');
})->name('migrate');

Route::get('clear', function () {
    
    
    
    Illuminate\Support\Facades\Artisan::call('optimize:clear');
    $previousUrl = url()->previous();
    $keywords = ['push-notification', 'ajax'];
    if (array_filter($keywords, fn($keyword) => str_contains($previousUrl, $keyword))) {
        return redirect('/')->with('success', 'Cache Cleared Successfully');
    }
    return redirect()->back(fallback: '/')->with('success', 'Cache Cleared Successfully');
})->name('clear');

Route::get('queue-work', function () {
    return Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
})->name('queue.work');

Route::get('schedule-run', function () {
    return Illuminate\Support\Facades\Artisan::call('schedule:run');
})->name('schedule:run');

$basicControl = basicControl();
Route::group(['prefix' => $basicControl->admin_prefix ?? 'admin', 'as' => 'admin.'], function () {
    Route::get('/themeMode/{themeType?}', function ($themeType = 'true') {
        session()->put('themeMode', $themeType);
        return $themeType;
    })->name('themeMode');

    /*== Authentication Routes ==*/
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest:admin');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request')
        ->middleware('guest:admin');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset')->middleware('guest:admin');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.update');


    Route::middleware(['auth:admin', 'demo'])->group(function () {

        Route::get('profile', [AdminProfileSettingController::class, 'profile'])->name('profile');
        Route::put('profile', [AdminProfileSettingController::class, 'profileUpdate'])->name('profile.update');
        Route::put('password', [AdminProfileSettingController::class, 'passwordUpdate'])->name('password.update');
        Route::post('notification-permission', [AdminProfileSettingController::class, 'notificationPermission'])->name('notification.permission');

        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('save-token', [DashboardController::class, 'saveToken'])->name('save.token');

        Route::get('ajax/get-transaction-chart', [DashboardController::class, 'getTransactionChart'])->name('get.transaction.chart');

        Route::get('ajax/get-deposit-chart', [DashboardController::class, 'getDepositChart'])->name('get.deposit.chart');
        Route::get('ajax/get-payout-chart', [DashboardController::class, 'getPayoutChart'])->name('get.payout.chart');
        Route::get('ajax/get-trx-chart', [DashboardController::class, 'getTrxChart'])->name('get.trx.chart');

        Route::get('ajax/get-commission-chart', [DashboardController::class, 'getCommissionChart'])->name('get.commission.chart');

        Route::get('ajax/user-locations', [DashboardController::class, 'getUserLocations'])->name('user.overview');


        Route::get('ajax/dashboard/monthly-deposit-withdraw', [DashboardController::class, 'monthlyDepositWithdraw'])->name('monthly.deposit.withdraw');
        Route::get('ajax/dashboard/chartUserRecords', [DashboardController::class, 'chartUserRecords'])->name('chartUserRecords');
        Route::get('ajax/dashboard/chartTicketRecords', [DashboardController::class, 'chartTicketRecords'])->name('chartTicketRecords');
        Route::get('ajax/dashboard/chartKycRecords', [DashboardController::class, 'chartKycRecords'])->name('chartKycRecords');
        Route::get('ajax/dashboard/chartTransactionRecords', [DashboardController::class, 'chartTransactionRecords'])->name('chartTransactionRecords');

        Route::get('ajax/chartBrowserHistory', [DashboardController::class, 'chartBrowserHistory'])->name('chart.browser.history');
        Route::get('ajax/chartOsHistory', [DashboardController::class, 'chartOsHistory'])->name('chart.os.history');
        Route::get('ajax/chartDeviceHistory', [DashboardController::class, 'chartDeviceHistory'])->name('chart.device.history');

        /*== Wallet Transaction Summary ==*/
        Route::get('wallet-transaction-summary/{code}', [CurrencyController::class, 'walletSummary'])->name('wallet.summary');
        Route::get('wallet-monthly-transaction/{code?}', [CurrencyController::class, 'monthlyTransaction'])->name('wallet.monthly.transaction');


        /*== Control Panel ==*/

        Route::get('theme', [BasicControlController::class, 'theme'])->name('theme');
        Route::get('theme-update', [BasicControlController::class, 'themeUpdate'])->name('themeUpdate');
        Route::get('user-layout-update', [BasicControlController::class, 'userLayoutUpdate'])->name('userLayoutUpdate');

        Route::get('settings/{settings?}', [BasicControlController::class, 'index'])->name('settings');
        Route::get('basic-control', [BasicControlController::class, 'basicControl'])->name('basic.control');
        Route::post('basic-control-update', [BasicControlController::class, 'basicControlUpdate'])->name('basic.control.update');
        Route::post('basic-control-activity-update', [BasicControlController::class, 'basicControlActivityUpdate'])->name('basic.control.activity.update');
        Route::get('currency-exchange-api-config', [BasicControlController::class, 'currencyExchangeApiConfig'])->name('currency.exchange.api.config');
        Route::post('currency-exchange-api-config/update', [BasicControlController::class, 'currencyExchangeApiConfigUpdate'])->name('currency.exchange.api.config.update');

        Route::match(['get', 'post'], 'service-control', [BasicControlController::class, 'serviceControl'])->name('service.control');
        Route::match(['get', 'post'], 'voucher-settings', [BasicControlController::class, 'voucherSettings'])->name('voucher.settings');
        Route::match(['get', 'post'], 'invoice-settings', [BasicControlController::class, 'invoiceSettings'])->name('invoice.settings');
        Route::match(['get', 'post'], 'virtual-card.settings', [BasicControlController::class, 'virtualCardSettings'])->name('virtual-card.settings');
        Route::match(['get', 'post'], 'fx-trade-settings', [BasicControlController::class, 'fxTradeSettings'])->name('fx.trade.settings');
        Route::post('fx-trade-settings/assets', [BasicControlController::class, 'stocksAssets'])->name('fx.trade.assets');


        Route::get('addon-management', [BasicControlController::class, 'addonIndex'])->name('addon.index');
        Route::post('addon-toggle/{module}', [BasicControlController::class, 'addonToggle'])->name('addon.toggle');


        /*===== CURRENCY SETTINGS =====*/
        Route::middleware('module:23')->group(function () {
            Route::get('currencies', [CurrencyController::class, 'index'])->name('currency.index');
            Route::get('currency/create', [CurrencyController::class, 'create'])->name('currency.create');
            Route::post('currency/create', [CurrencyController::class, 'store'])->name('currency.store');
            Route::get('currency/{currency}', [CurrencyController::class, 'edit'])->name('currency.edit');
            Route::put('currency/{currency}', [CurrencyController::class, 'update'])->name('currency.update');
        });

        /* ===== CHARGES & LIMITS SETTINGS ===== */
        Route::middleware('module:24')->group(function () {
            Route::get('charges', [ChargesLimitController::class, 'index'])->name('charge.index');
            Route::get('charge/{chargesLimit}', [ChargesLimitController::class, 'edit'])->name('charge.edit');
            Route::get('charge/{transactionTypeID}/{currencyID}', [ChargesLimitController::class, 'chargeEdit'])->name('charge.chargeEdit');
            Route::put('charge/{chargesLimit}', [ChargesLimitController::class, 'update'])->name('charge.update');

            Route::match(['get', 'post'], 'api-sandbox', [BasicControlController::class, 'sandBoxApi'])->name('api.sandbox.index');
            Route::get('charge-payment-method/{currencyID}', [ChargesLimitController::class, 'chargePaymentMethod'])->name('charge.payment.method');
            Route::get('get-deposit-charge/{currency_id}/{payment_method_id}', [ChargesLimitController::class, 'getCharge'])->name('get.deposit.charge');
            Route::post('set-deposit-charge', [ChargesLimitController::class, 'setCharge'])->name('set.deposit.charge');
        });

        /* ===== ADMIN SECURITY QUESTION SETTINGS ===== */
        Route::get('security-questions', [SecurityQuestionController::class, 'index'])->name('securityQuestion.index');
        Route::get('security-question/create', [SecurityQuestionController::class, 'create'])->name('securityQuestion.create');
        Route::post('security-question/create', [SecurityQuestionController::class, 'store'])->name('securityQuestion.store');
        Route::get('security-question/{securityQuestion}', [SecurityQuestionController::class, 'edit'])->name('securityQuestion.edit');
        Route::put('security-question/{securityQuestion}', [SecurityQuestionController::class, 'update'])->name('securityQuestion.update');

        /* ===== STORAGE ===== */
        Route::get('storage', [StorageController::class, 'index'])->name('storage.index');
        Route::any('storage/edit/{id}', [StorageController::class, 'edit'])->name('storage.edit');
        Route::any('storage/update/{id}', [StorageController::class, 'update'])->name('storage.update');
        Route::post('storage/set-default/{id}', [StorageController::class, 'setDefault'])->name('storage.setDefault');

        /* ===== Maintenance Mode ===== */
        Route::get('maintenance-mode', [MaintenanceModeController::class, 'index'])->name('maintenance.index');
        Route::post('maintenance-mode/update', [MaintenanceModeController::class, 'maintenanceModeUpdate'])->name('maintenance.mode.update');

        /* ===== LOGO, FAVICON UPDATE ===== */
        Route::get('logo-setting', [LogoController::class, 'logoSetting'])->name('logo.settings');
        Route::post('logo-update', [LogoController::class, 'logoUpdate'])->name('logo.update');


        /* ===== FIREBASE CONFIG ===== */
        Route::get('firebase-config', [FirebaseConfigController::class, 'firebaseConfig'])->name('firebase.config');
        Route::post('firebase-config-update', [FirebaseConfigController::class, 'firebaseConfigUpdate'])->name('firebase.config.update');
        Route::post('firebase-config-/file-upload', [FirebaseConfigController::class, 'firebaseConfigFileUpload'])->name('firebase.config.file.upload');
        Route::get('firebase-config-/file-download', [FirebaseConfigController::class, 'firebaseConfigFileDownload'])->name('firebase.config.file.download');


        /* ===== PUSHER CONFIG ===== */
        Route::get('pusher-config', [PusherConfigController::class, 'pusherConfig'])->name('pusher.config');
        Route::post('pusher-config-update', [PusherConfigController::class, 'pusherConfigUpdate'])->name('pusher.config.update');

        /* ===== EMAIL CONFIG ===== */
        Route::get('email-controls', [EmailConfigController::class, 'emailControls'])->name('email.control');
        Route::get('email-config/edit/{method}', [EmailConfigController::class, 'emailConfigEdit'])->name('email.config.edit');
        Route::post('email-config/update/{method}', [EmailConfigController::class, 'emailConfigUpdate'])->name('email.config.update');
        Route::post('email-config/set-as-default/{method}', [EmailConfigController::class, 'emailSetAsDefault'])->name('email.set.default');
        Route::post('test.email', [EmailConfigController::class, 'testEmail'])->name('test.email');


        /* Notification Templates Routes */
        Route::match(['get', 'post'], 'default-template', [NotificationTemplateController::class, 'defaultTemplate'])->name('email.template.default');
        Route::get('email-templates', [NotificationTemplateController::class, 'emailTemplates'])->name('email.templates');
        Route::get('email-template/edit/{id}', [NotificationTemplateController::class, 'editEmailTemplate'])->name('email.template.edit');
        Route::put('email-template/{id?}/{language_id}', [NotificationTemplateController::class, 'updateEmailTemplate'])->name('email.template.update');

        Route::get('sms-templates', [NotificationTemplateController::class, 'smsTemplates'])->name('sms.templates');
        Route::get('sms-template/edit/{id}', [NotificationTemplateController::class, 'editSmsTemplate'])->name('sms.template.edit');
        Route::put('sms-template/{id?}/{language_id}', [NotificationTemplateController::class, 'updateSmsTemplate'])->name('sms.template.update');

        Route::get('in-app-notification-templates', [NotificationTemplateController::class, 'inAppNotificationTemplates'])
            ->name('in.app.notification.templates');
        Route::get('in-app-notification-template/edit/{id}', [NotificationTemplateController::class, 'editInAppNotificationTemplate'])
            ->name('in.app.notification.template.edit');
        Route::put('in-app-notification-template/{id?}/{language_id}', [NotificationTemplateController::class, 'updateInAppNotificationTemplate'])
            ->name('in.app.notification.template.update');
        Route::get('push-notification-templates', [NotificationTemplateController::class, 'pushNotificationTemplates'])->name('push.notification.templates');
        Route::get('push-notification-template/edit/{id}', [NotificationTemplateController::class, 'editPushNotificationTemplate'])->name('push.notification.template.edit');
        Route::put('push-notification-template/{id?}/{language_id}', [NotificationTemplateController::class, 'updatePushNotificationTemplate'])->name('push.notification.template.update');


        /* ===== EMAIL CONFIG ===== */
        Route::get('sms-configuration', [SmsConfigController::class, 'index'])->name('sms.controls');
        Route::get('sms-config-edit/{method}', [SmsConfigController::class, 'smsConfigEdit'])->name('sms.config.edit');
        Route::post('sms-config-update/{method}', [SmsConfigController::class, 'smsConfigUpdate'])->name('sms.config.update');
        Route::post('sms-method-update/{method}', [SmsConfigController::class, 'manualSmsMethodUpdate'])->name('manual.sms.method.update');
        Route::post('sms-config/set-as-default/{method}', [SmsConfigController::class, 'smsSetAsDefault'])->name('sms.set.default');

        /* ===== PLUGIN CONFIG ===== */
        Route::get('plugin', [PluginController::class, 'pluginConfig'])->name('plugin.config');
        Route::get('plugin/tawk', [PluginController::class, 'tawkConfiguration'])->name('tawk.configuration');
        Route::post('plugin/tawk/Configuration/update', [PluginController::class, 'tawkConfigurationUpdate'])->name('tawk.configuration.update');
        Route::get('plugin/fb-messenger-configuration', [PluginController::class, 'fbMessengerConfiguration'])->name('fb.messenger.configuration');
        Route::post('plugin/fb-messenger-configuration/update', [PluginController::class, 'fbMessengerConfigurationUpdate'])->name('fb.messenger.configuration.update');
        Route::get('plugin/google-recaptcha', [PluginController::class, 'googleRecaptchaConfiguration'])->name('google.recaptcha.configuration');
        Route::post('plugin/google-recaptcha/update', [PluginController::class, 'googleRecaptchaConfigurationUpdate'])->name('google.recaptcha.Configuration.update');
        Route::get('plugin/google-analytics', [PluginController::class, 'googleAnalyticsConfiguration'])->name('google.analytics.configuration');
        Route::post('plugin/google-analytics', [PluginController::class, 'googleAnalyticsConfigurationUpdate'])->name('google.analytics.configuration.update');
        Route::get('plugin/manual-recaptcha', [PluginController::class, 'manualRecaptcha'])->name('manual.recaptcha');
        Route::post('plugin/manual-recaptcha/update', [PluginController::class, 'manualRecaptchaUpdate'])->name('manual.recaptcha.update');
        Route::post('plugin/active-recaptcha', [PluginController::class, 'activeRecaptcha'])->name('active.recaptcha');

        /* ===== ADMIN GOOGLE API SETTING ===== */
        Route::get('translate-api-setting', [TranslateAPISettingController::class, 'translateAPISetting'])->name('translate.api.setting');
        Route::get('translate-api-config/edit/{method}', [TranslateAPISettingController::class, 'translateAPISettingEdit'])->name('translate.api.config.edit');
        Route::post('translate-api-setting/update/{method}', [TranslateAPISettingController::class, 'translateAPISettingUpdate'])->name('translate.api.setting.update');
        Route::post('translate-api-setting/set-as-default/{method}', [TranslateAPISettingController::class, 'translateSetAsDefault'])->name('translate.set.default');


        /* ===== ADMIN LANGUAGE SETTINGS ===== */
        Route::get('languages', [LanguageController::class, 'index'])->name('language.index');
        Route::get('language/create', [LanguageController::class, 'create'])->name('language.create');
        Route::post('language/store', [LanguageController::class, 'store'])->name('language.store');
        Route::get('language/edit/{id}', [LanguageController::class, 'edit'])->name('language.edit');
        Route::put('language/update/{id}', [LanguageController::class, 'update'])->name('language.update');
        Route::delete('language-delete/{id}', [LanguageController::class, 'destroy'])->name('language.delete');
        Route::put('change-language-status/{id}', [LanguageController::class, 'changeStatus'])->name('change.language.status');


        Route::get('{short_name}/keywords', [LanguageController::class, 'keywords'])->name('language.keywords');
        Route::post('language-keyword/{short_name}', [LanguageController::class, 'addKeyword'])->name('add.language.keyword');
        Route::put('language-keyword/{short_name}/{key}', [LanguageController::class, 'updateKeyword'])->name('update.language.keyword');
        Route::delete('language-keyword/{short_name}/{key}', [LanguageController::class, 'deleteKeyword'])->name('delete.language.keyword');
        Route::post('language-import-json', [LanguageController::class, 'importJson'])->name('language.import.json');
        Route::put('update-key/{language}', [LanguageController::class, 'updateKey'])->name('language.update.key');
        Route::post('language/keyword/translate', [LanguageController::class, 'singleKeywordTranslate'])->name('single.keyword.translate');
        Route::post('language/all-keyword/translate/{shortName}', [LanguageController::class, 'allKeywordTranslate'])->name('all.keyword.translate');


        /* ===== ADMIN SUPPORT TICKET ===== */
        Route::get('tickets/{status?}', [SupportTicketController::class, 'tickets'])->name('ticket');
        Route::get('tickets-search/{status}', [SupportTicketController::class, 'ticketSearch'])->name('ticket.search');
        Route::get('tickets-view/{id}', [SupportTicketController::class, 'ticketView'])->name('ticket.view');
        Route::put('ticket-reply/{id}', [SupportTicketController::class, 'ticketReplySend'])->name('ticket.reply');
        Route::get('ticket-download/{ticket}', [SupportTicketController::class, 'ticketDownload'])->name('ticket.download');
        Route::post('ticket-closed/{id}', [SupportTicketController::class, 'ticketClosed'])->name('ticket.closed');
        Route::post('ticket-delete', [SupportTicketController::class, 'ticketDelete'])->name('ticket.delete');


        /* ===== InAppNotificationController SETTINGS ===== */
        Route::get('push-notification-show', [InAppNotificationController::class, 'showByAdmin'])->name('push.notification.show');
        Route::get('push.notification.readAll', [InAppNotificationController::class, 'readAllByAdmin'])->name('push.notification.readAll');
        Route::get('push-notification-readAt/{id}', [InAppNotificationController::class, 'readAt'])->name('push.notification.readAt');

        /* PAYMENT METHOD MANAGE BY ADMIN*/
        Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment.methods');
        Route::get('edit-payment-methods/{id}', [PaymentMethodController::class, 'edit'])->name('edit.payment.methods');
        Route::put('update-payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('update.payment.methods');
        Route::post('sort-payment-methods', [PaymentMethodController::class, 'sortPaymentMethods'])->name('sort.payment.methods');
        Route::post('payment-methods/deactivate', [PaymentMethodController::class, 'deactivate'])->name('payment.methods.deactivate');


        /*=* MANUAL METHOD MANAGE BY ADMIN *=*/
        Route::get('payment-methods/manual', [ManualGatewayController::class, 'index'])->name('deposit.manual.index');
        Route::get('payment-methods/manual/create', [ManualGatewayController::class, 'create'])->name('deposit.manual.create');
        Route::post('payment-methods/manual/store', [ManualGatewayController::class, 'store'])->name('deposit.manual.store');
        Route::get('payment-methods/manual/edit/{id}', [ManualGatewayController::class, 'edit'])->name('deposit.manual.edit');
        Route::put('payment-methods/manual/update/{id}', [ManualGatewayController::class, 'update'])->name('deposit.manual.update');

        /*= PAYOUT METHOD MANAGE BY ADMIN =*/
        Route::get('withdraw-methods/list', [PayoutMethodController::class, 'index'])->name('payout.method.list');
        Route::get('withdraw-methods/create', [PayoutMethodController::class, 'create'])->name('payout.method.create');
        Route::post('withdraw-methods/store', [PayoutMethodController::class, 'store'])->name('payout.method.store');
        Route::get('withdraw-methods/manual/edit/{id}', [PayoutMethodController::class, 'manualEdit'])->name('payout.manual.method.edit');
        Route::get('withdraw-methods/edit/{id}', [PayoutMethodController::class, 'edit'])->name('payout.method.edit');
        Route::put('withdraw-methods/update/{id}', [PayoutMethodController::class, 'update'])->name('payout.method.update');
        Route::post('withdraw-methods/auto-update/{id}', [PayoutMethodController::class, 'autoUpdate'])->name('payout.method.auto.update');
        Route::post('withdraw-methods/active-deactivate', [PayoutMethodController::class, 'activeDeactivate'])->name('payout.active.deactivate');
        Route::get('withdraw-methods/days/setting', [PayoutMethodController::class, 'withdrawDays'])->name('payout.withdraw.days');
        Route::post('withdraw-methods/days/setting', [PayoutMethodController::class, 'withdrawDaysUpdate'])->name('withdrawal.days.update');
        Route::match(['get', 'post'], 'currency-exchange-api-config', [BasicControlController::class, 'currencyExchangeApiConfig'])->name('currency.exchange.api.config');

        /*= MANAGE KYC =*/
        Route::middleware('module:14')->group(function () {
            Route::get('kyc-setting/list', [KycController::class, 'index'])->name('kyc.form.list');
            Route::get('kyc-setting/create', [KycController::class, 'create'])->name('kyc.create');
            Route::post('manage-kyc/store', [KycController::class, 'store'])->name('kyc.store');
            Route::get('manage-kyc/edit/{id}', [KycController::class, 'edit'])->name('kyc.edit');
            Route::post('manage-kyc/update/{id}', [KycController::class, 'update'])->name('kyc.update');
            Route::get('kyc/{status?}', [KycController::class, 'userKycList'])->name('kyc.list');
            Route::get('kyc/search/{status?}', [KycController::class, 'userKycSearch'])->name('kyc.search');
            Route::get('kyc/view/{id}', [KycController::class, 'view'])->name('kyc.view');
            Route::post('user/kyc/action/{id}', [KycController::class, 'action'])->name('kyc.action');
            Route::get('user/kyc-search', [KycController::class, 'searchKyc'])->name('userKyc.search');
        });

        /*= Frontend Manage =*/
        Route::middleware('module:20')->group(function () {
            Route::get('frontend/pages/{theme}', [PageController::class, 'index'])->name('page.index');
            Route::get('frontend/create-page/{theme}', [PageController::class, 'create'])->name('create.page');
            Route::post('frontend/create-page/store/{theme}', [PageController::class, 'store'])->name('create.page.store');
            Route::get('frontend/edit-page/{id}/{theme}/{language?}', [PageController::class, 'edit'])->name('edit.page');
            Route::post('frontend/update-page/{id}/{theme}', [PageController::class, 'update'])->name('update.page');
            Route::post('frontend/page/update-slug', [PageController::class, 'updateSlug'])->name('update.slug');
            Route::delete('frontend/page/delete/{id}', [PageController::class, 'delete'])->name('page.delete');

            Route::get('frontend/edit-static-page/{id}/{theme}/{language?}', [PageController::class, 'editStaticPage'])->name('edit.static.page');
            Route::post('frontend/update-static-page/{id}/{theme}', [PageController::class, 'updateStaticPage'])->name('update.static.page');

            Route::get('frontend/page/seo/{id}', [PageController::class, 'pageSEO'])->name('page.seo');
            Route::post('frontend/page/seo/update/{id}', [PageController::class, 'pageSeoUpdate'])->name('page.seo.update');

            Route::get('frontend/manage-menu', [ManageMenuController::class, 'manageMenu'])->name('manage.menu');
            Route::post('frontend/header-menu-item/store', [ManageMenuController::class, 'headerMenuItemStore'])->name('header.menu.item.store');
            Route::post('frontend/footer-menu-item/store', [ManageMenuController::class, 'footerMenuItemStore'])->name('footer.menu.item.store');
            Route::post('frontend/manage-menu/add-custom-link', [ManageMenuController::class, 'addCustomLink'])->name('add.custom.link');
            Route::get('frontend/manage-menu/edit-custom-link/{pageId}', [ManageMenuController::class, 'editCustomLink'])->name('edit.custom.link');
            Route::post('frontend/manage-menu/update-custom-link/{pageId}', [ManageMenuController::class, 'updateCustomLink'])->name('update.custom.link');
            Route::delete('frontend/manage-menu/delete-custom-link/{pageId}', [ManageMenuController::class, 'deleteCustomLink'])->name('delete.custom.link');
            Route::get('frontend/manage-menu/get-custom-link-data', [ManageMenuController::class, 'getCustomLinkData'])->name('get.custom.link');

            Route::get('frontend/contents/{name}', [ContentController::class, 'index'])->name('manage.content');
            Route::post('frontend/contents/store/{name}/{language}', [ContentController::class, 'store'])->name('content.store');
            Route::get('frontend/contents/item/{name}', [ContentController::class, 'manageContentMultiple'])->name('manage.content.multiple');
            Route::post('frontend/contents/item/store/{name}/{language}', [ContentController::class, 'manageContentMultipleStore'])->name('content.multiple.store');
            Route::get('frontend/contents/item/edit/{name}/{id}', [ContentController::class, 'multipleContentItemEdit'])->name('content.item.edit');
            Route::post('frontend/contents/item/update/{name}/{id}/{language}', [ContentController::class, 'multipleContentItemUpdate'])->name('multiple.content.item.update');
            Route::delete('frontend/contents/delete/{id}', [ContentController::class, 'ContentDelete'])->name('content.item.delete');
        });
        /*====Manage Users ====*/
        Route::get('login/as/user/{id}', [UsersController::class, 'loginAsUser'])->name('login.as.user');
        Route::post('block-profile/{id}', [UsersController::class, 'blockProfile'])->name('block.profile');
        Route::get('users/{state?}', [UsersController::class, 'index'])->name('users');
        Route::get('users/search/{state?}', [UsersController::class, 'search'])->name('users.search');
        Route::get('user/edit/{id}', [UsersController::class, 'userEdit'])->name('user.edit');
        Route::get('users/get-balance/{id}', [UsersController::class, 'getBalance'])->name('users.getBalance');

        Route::post('users-delete-multiple', [UsersController::class, 'deleteMultiple'])->name('user.delete.multiple');
        Route::post('user/update/{id}', [UsersController::class, 'userUpdate'])->name('user.update');
        Route::post('user/email/{id}', [UsersController::class, 'EmailUpdate'])->name('user.email.update');
        Route::post('user/username/{id}', [UsersController::class, 'usernameUpdate'])->name('user.username.update');
        Route::post('user/update-balance/{id}', [UsersController::class, 'updateBalanceUpdate'])->name('user.update.balance');
        Route::post('user/password/{id}', [UsersController::class, 'passwordUpdate'])->name('user.password.update');
        Route::post('user/preferences/{id}', [UsersController::class, 'preferencesUpdate'])->name('user.preferences.update');
        Route::post('user/two-fa-security/{id}', [UsersController::class, 'userTwoFaUpdate'])->name('user.twoFa.update');
        Route::post('user/balance-update/{id}', [UsersController::class, 'userBalanceUpdate'])->name('user-balance-update');

        Route::get('user/send-email/{id}', [UsersController::class, 'sendEmail'])->name('send.email');
        Route::post('user/send-email/{id?}', [UsersController::class, 'sendMailUser'])->name('user.email.send');
        Route::get('mail-all-user', [UsersController::class, 'mailAllUser'])->name('mail.all.user');

        Route::get('user/kyc/{id}', [UsersController::class, 'userKyc'])->name('user.kyc.list');
        Route::get('user/kyc/search/{id}', [UsersController::class, 'KycSearch'])->name('user.kyc.search');

        Route::get('user/transaction/{id}', [UsersController::class, 'transaction'])->name('user.transaction');
        Route::get('user/transaction/search/{id}', [UsersController::class, 'userTransactionSearch'])->name('user.transaction.search');

        Route::get('user/payment/{id}', [UsersController::class, 'payment'])->name('user.payment');
        Route::get('user/payment/search/{id}', [UsersController::class, 'userPaymentSearch'])->name('user.payment.search');

        Route::get('user/withdraw/{id}', [UsersController::class, 'payout'])->name('user.payout');
        Route::get('user/withdraw/search/{id}', [UsersController::class, 'userPayoutSearch'])->name('user.payout.search');

        Route::get('/email-send', [UsersController::class, 'emailToUsers'])->name('email-send');
        Route::post('/email-send', [UsersController::class, 'sendEmailToUsers'])->name('email-send.store');
        Route::delete('user/delete/{id}', [UsersController::class, 'userDelete'])->name('user.delete');

        Route::get('add-new/{type?}', [UsersController::class, 'userAdd'])->name('users.add');
        Route::post('users/store', [UsersController::class, 'userStore'])->name('user.store');
        Route::get('users/added-successfully/{id?}', [UsersController::class, 'userCreateSuccessMessage'])
            ->name('user.create.success.message');
        Route::get('user/view-profile/{id}', [UsersController::class, 'userViewProfile'])->name('user.view.profile');


        /* ====== Transaction Log =====*/
        Route::middleware('module:18')->group(function () {
            Route::get('transaction', [TransactionLogController::class, 'transaction'])->name('transaction');
            Route::get('transaction-search/{type?}', [TransactionLogController::class, 'transactionSearch'])->name('transaction.search');
        });

        /* ====== Payment Log =====*/
        Route::middleware('module:1')->group(function () {
            Route::get('payment/log', [PaymentLogController::class, 'index'])->name('payment.log');
            Route::get('payment-search/{type?}', [PaymentLogController::class, 'search'])->name('payment.search');
            Route::get('payment/pending', [PaymentLogController::class, 'pending'])->name('payment.pending');
            Route::get('payment-pending-request/{type?}', [PaymentLogController::class, 'paymentRequest'])->name('payment.request');
            Route::put('payment/action/{id}', [PaymentLogController::class, 'action'])->name('payment.action');
        });

        /* ====== Payout Log =====*/
        Route::middleware('module:8')->group(function () {
            Route::get('withdraw/log', [PayoutLogController::class, 'index'])->name('payout.log');
            Route::get('withdraw-search/{type?}', [PayoutLogController::class, 'search'])->name('payout.search');
            Route::get('withdraw/pending', [PayoutLogController::class, 'pending'])->name('payout.pending');
            Route::put('withdraw/action/{id}', [PayoutLogController::class, 'action'])->name('payout.action');
        });

        /* ====== Blog Category Controller =====*/
        Route::resource('blog-category', BlogCategoryController::class);
        Route::resource('blogs', BlogController::class);
        Route::get('blogs/edit/{id}/{language}', [BlogController::class, 'blogEdit'])->name('blog.edit');
        Route::post('blogs/update/{id}/{language}', [BlogController::class, 'blogUpdate'])->name('blog.update');
        Route::post('blogs/slug/update', [BlogController::class, 'slugUpdate'])->name('slug.update');
        Route::get('blogs/seo-page/{id}', [BlogController::class, 'blogSeo'])->name('blog.seo');
        Route::post('blogs/seo-update/{id}', [BlogController::class, 'blogSeoUpdate'])->name('blog.seo.update');

        /* ====== Subscriber =====*/
        Route::resource('subscriber', SubscriberController::class);
        Route::get('send-mail', [SubscriberController::class, 'sendEmailForm'])->name('subscriber.mail');
        Route::post('send-email', [SubscriberController::class, 'sendEmail'])->name('subscriber.email');


        Route::middleware('module:19')->group(function () {
            Route::match(['get', 'post'], 'referral-bonus', [ReferralBonusController::class, 'show'])->name('referral.bonus.index');
            Route::get('commission-list', [CommissionEntryController::class, 'indexAdmin'])->name('commission.index');
            Route::get('commission-search', [CommissionEntryController::class, 'searchAdmin'])->name('commission.search');
        });

        /* ROLES AND PERMISSION BY ADMIN */
        Route::middleware('module:12')->group(function () {
            Route::get('role/list', [RolesPermissionController::class, 'index'])->name('role');
            Route::post('role/create', [RolesPermissionController::class, 'roleCreate'])->name('role.create');
            Route::post('role/update', [RolesPermissionController::class, 'roleUpdate'])->name('role.update');
            Route::delete('role/delete/{id}', [RolesPermissionController::class, 'roleDelete'])->name('role.delete');

            Route::any('manage/staffs', [RolesPermissionController::class, 'staffList'])->name('role.staff');
            Route::post('manage/staffs/create', [RolesPermissionController::class, 'staffCreate'])->name('role.usersCreate');
            Route::post('manage/staffs/status/change/{id}', [RolesPermissionController::class, 'statusChange'])->name('role.statusChange');
            Route::post('manage/staffs/login/{id}', [RolesPermissionController::class, 'userLogin'])->name('role.usersLogin');
        });


    });

});


