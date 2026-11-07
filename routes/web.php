<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Auth\LoginController as UserLoginController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicCartController;
use App\Http\Controllers\PublicChekoutController;
use App\Http\Controllers\QrCodePaymentController;
use App\Http\Controllers\StoreShopController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\ManualRecaptchaController;
use App\Http\Controllers\khaltiPaymentController;
use App\Http\Controllers\User\VoucherController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\User\VerificationController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Api\V1\FundController as APIFundController;
use Stichoza\GoogleTranslate\GoogleTranslate;


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


//auth()->guard('admin')->loginUsingId(1);
$basicControl = basicControl();
Route::get('language/{locale}', function ($locale) {
    $language = \App\Models\Language::where('short_name', $locale)->first();
    if (!$language) $locale = 'en';
    session()->put('lang', $locale);
    session()->put('rtl', $language ? $language->rtl : 0);
    return back();
})->name('language');

Route::get('maintenance-mode', function () {
    if (!basicControl()->is_maintenance_mode) {
        return redirect(route('page'));
    }
    $data['maintenanceMode'] = \App\Models\MaintenanceMode::first();
    return view(template() . 'maintenance', $data);
})->name('maintenance');

Route::get('payment/view/{utr}', [APIFundController::class, 'paymentView'])->name('paymentView');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPassword'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset')
    ->middleware('guest');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.reset.update');

Route::get('instruction/page', function () {
    return view('instruction-page');
})->name('instructionPage');

Route::group(['middleware' => ['maintenanceMode']], function () use ($basicControl) {
    Route::group(['middleware' => ['guest']], function () {
        Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [UserLoginController::class, 'login'])->name('login.submit');
    });

    Route::get('fund-initialize/wallet-selection', [FundController::class, 'fundWalletSelection'])
        ->name('fund.wallet.selection')->middleware('auth');
    Route::match(['get', 'post'], 'add-fund/{from?}/{id?}', [FundController::class, 'initialize'])
        ->name('fund.initialize')->middleware(['auth', 'Ensure:deposit']);

    Route::group(['middleware' => ['auth'], 'prefix' => 'user', 'as' => 'user.'], function () {

        Route::get('check', [VerificationController::class, 'check'])->name('check');
        Route::get('resend_code', [VerificationController::class, 'resendCode'])->name('resend.code');
        Route::post('mail-verify', [VerificationController::class, 'mailVerify'])->name('mail.verify');
        Route::post('sms-verify', [VerificationController::class, 'smsVerify'])->name('sms.verify');
        Route::post('twoFA-Verify', [VerificationController::class, 'twoFAverify'])->name('twoFA-Verify');

        require base_path('routes/partials/user.php');

    });

    // Voucher payment public view
    Route::match(['get', 'post'], 'voucher-payment-public-view/{utr}', [VoucherController::class, 'voucherPaymentPublicView'])->name('voucher.paymentPublicView');
    Route::match(['get', 'post'], 'voucher-public-payment/{utr}', [VoucherController::class, 'voucherPublicPayment'])->name('voucher.public.payment');

    //Invoice payment public view
    Route::get('/invoice/{hash_slug}', [InvoiceController::class, 'showPublicInvoice'])->name('public.invoice.show');
    Route::post('/invoice/payment-confirm/{hash_slug}', [InvoiceController::class, 'publicInvoicePaymentConfirm'])->name('public.invoice.payment.confirm');
    Route::match(['get', 'post'], 'invoice/public/payment/{hash_slug}', [InvoiceController::class, 'invoicePublicPayment'])->name('invoice.public.payment');
    Route::get('/reject-invoice-from-email/{hash_slug}', [InvoiceController::class, 'rejectInvoiceFromEmail'])->name('reject.invoice.from.email');
    Route::get('/download-pdf/{invoiceId}', [InvoiceController::class, 'downloadPdf'])->name('downloadPdf');


    // Public QR Code Payment
    Route::any('public/qr-payment/{link}', [QrCodePaymentController::class, 'qrPayment'])->name('public.qr.Payment');

    //Api Payment
    Route::get('make/payment/{mode}/{utr}', [ApiController::class, 'makePayment'])->name('make.payment');
    Route::post('make/payment/confirm/{mode}/{utr}', [ApiController::class, 'makePaymentConfirm'])->name('make.payment.confirm');

    /* Public Store */
    Route::get('store/product/{link?}', [StoreShopController::class, 'shopProduct'])->name('public.view');
    Route::get('store/product/{link?}/details/{title}/{id}', [StoreShopController::class, 'shopProductDetails'])->name('public.product.details');
    //Cart
    Route::get('store/product/stock/check', [PublicCartController::class, 'stockCheck'])->name('public.stock.check');
    Route::get('store/product/attr/check', [PublicCartController::class, 'stockAttrCheck'])->name('product.attr.check');
    Route::post('store/product/stock/check', [PublicCartController::class, 'ProductStockCheck'])->name('product.check');
    Route::get('store/product/attr/list', [PublicCartController::class, 'attrList'])->name('product.attributes.list');
    Route::get('store/product/{link}/cart', [PublicCartController::class, 'productCart'])->name('public.cart');

    Route::get('store/product/{link}/checkout', [PublicChekoutController ::class, 'productCheckout'])->name('public.checkout');
    Route::post('store/product/checkout/store', [PublicChekoutController ::class, 'productCheckoutStore'])->name('public.checkout.store');

    //Order Track
    Route::get('store/product/{link}/track', [PublicCartController::class, 'productTrack'])->name('public.product.track');
    Route::get('store/product/order/download/{orderId}', [PublicCartController::class, 'productOrderDownload'])->name('public.product.orderDownload');

    Route::get('store/product/{link}/seller', [StoreShopController::class, 'sellerDetails'])->name('public.seller.details');
    Route::post('store/product/{link}/seller/contact', [StoreShopController::class, 'sellerContact'])->name('public.seller.contact');


    Route::get('captcha', [ManualRecaptchaController::class, 'reCaptCha'])->name('captcha');

    /* Manage User Deposit */
    Route::get('supported-currency', [DepositController::class, 'supportedCurrency'])->name('supported.currency');
    Route::post('payment-request', [DepositController::class, 'paymentRequest'])->name('payment.request');
    Route::get('deposit-check-amount', [DepositController::class, 'checkAmount'])->name('deposit.checkAmount');

    Route::match(['get', 'post'], 'confirm-deposit/{utr}', [DepositController::class, 'confirmDeposit'])
        ->name('deposit.confirm')->middleware('Ensure:deposit');


    Route::get('payment-process/{trx_id}', [PaymentController::class, 'depositConfirm'])->name('payment.process');
    Route::match(['get', 'post'], 'add-fund/confirm/{trx_id}', [PaymentController::class, 'fromSubmit'])->name('addFund.submit');
    Route::match(['get', 'post'], 'addFundConfirm/{trx_id}', [PaymentController::class, 'fromSubmit'])->name('addFund.fromSubmit');
    Route::match(['get', 'post'], 'success', [PaymentController::class, 'success'])->name('success');
    Route::match(['get', 'post'], 'failed', [PaymentController::class, 'failed'])->name('failed');
    Route::get('{link}/order/success/{orderNumber}', [PaymentController::class, 'orderSuccess'])->name('order.success');
    Route::match(['get', 'post'], 'payment/{code}/{trx?}/{type?}', [PaymentController::class, 'gatewayIpn'])->name('ipn');

    Route::post('khalti/payment/verify/{trx}', [khaltiPaymentController::class, 'verifyPayment'])->name('khalti.verifyPayment');
    Route::post('khalti/payment/store', [khaltiPaymentController::class, 'storePayment'])->name('khalti.storePayment');

    Route::get('blogs', [BlogController::class, 'blog'])->name('blog');
    Route::get('blog-details/{slug}', [BlogController::class, 'blogDetails'])->name('blog.details');
    Route::get('blog-search', [BlogController::class, 'blogSearch'])->name('blog.search');
    Route::get('blog/category/{slug?}/{id}', [BlogController::class, 'blogByCategory'])->name('blog.category');

    Route::post('contact', [FrontendController::class, 'contactSend'])->name('contact');
    Route::post('subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');



    if (config('demo.IS_DEMO')) {
        Route::get('/demo-login', [\App\Http\Controllers\Auth\LoginController::class, 'demoLogin'])
            ->name('demo.login')
            ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class]);
    }



    Auth::routes();
    /*= Frontend Manage Controller =*/
    Route::get("/{slug?}", [FrontendController::class, 'page'])->name('page');
});

