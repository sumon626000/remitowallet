<?php

namespace App\Services;


use App\Jobs\BonusCommissionJob;
use App\Mail\SendOrderMail;
use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\Invoice;
use App\Models\ProductOrder;
use App\Models\QRCode;
use App\Models\StoreProductStock;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualCardOrder;
use App\Models\Voucher;
use App\Traits\Notify;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BasicService
{
    use Notify;

    public function setEnv($value)
    {
        $envPath = base_path('.env');
        $env = file($envPath);
        foreach ($env as $env_key => $env_value) {
            $entry = explode("=", $env_value, 2);
            $env[$env_key] = array_key_exists($entry[0], $value) ? $entry[0] . "=" . $value[$entry[0]] . "\n" : $env_value;
        }
        $fp = fopen($envPath, 'w');
        fwrite($fp, implode($env));
        fclose($fp);
    }

    public function preparePaymentUpgradation($deposit)
    {
        DB::beginTransaction();
        try {
            $user = $deposit->user;
            if (!$user || !in_array($deposit->status, [0, 2])) {
                return false;
            }

            if (is_null($deposit->depositable_type)) {
                $this->processGeneralDeposit($deposit);
            } elseif ($deposit->depositable instanceof ProductOrder) {
                $this->processProductOrder($deposit);
            } elseif ($deposit->depositable instanceof Voucher) {
                $this->processVoucher($deposit);
            } elseif ($deposit->depositable instanceof Invoice) {
                $this->processInvoice($deposit);
            } elseif ($deposit->depositable instanceof QRCode) {
                $this->processQrCode($deposit);
            } elseif ($deposit->depositable instanceof ApiOrder) {
                $this->processApiOrder($deposit);
            } elseif ($deposit->depositable instanceof ApiOrderTest) {
                $apiOrderTest = ApiOrderTest::find($deposit->depositable_id);
                $apiOrderTest->status = 1;
                $apiOrderTest->paid_at = Carbon::now();
                $apiOrderTest->save();
                $deposit->status = 1;
                $deposit->save();
            } else {
                return false;
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            info($e->getMessage());
            return false;
        }
    }

    private function processGeneralDeposit($deposit)
    {
        if ($deposit->card_order_id != null) {
            $cardOrder = VirtualCardOrder::findOrFail($deposit->card_order_id);
            $cardOrder->status = 8;

            $info = cardCurrencyCheck($cardOrder->id);
            $percentValue = ($info['PercentCharge'] * $deposit->amount) / 100;
            $charge = $info['FixedCharge'] + $percentValue;

            $cardOrder->fund_amount = $deposit->amount - $charge;
            $cardOrder->fund_charge = $charge;
            $cardOrder->save();

            $remarks = 'Virtual Card Deposit via ' . optional($deposit->gateway)->name;
        } else {
            $receiveDetails = getConvertAmount($deposit);
            $deposit->receive_amount = $receiveDetails['amount'];
            $deposit->save();
            $wallet = updateWallet($deposit->user_id, $receiveDetails['currency_id'], $receiveDetails['amount'], 1);
            $remarks = 'Wallet Deposit via ' . optional($deposit->gateway)->name;

        }
        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->currency_id = $deposit->currency_id;
        $transaction->amount = $deposit->payable_amount_in_base_currency;
        $transaction->charge = getAmount($deposit->base_currency_charge);
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = $remarks;
        $deposit->transactional()->save($transaction);

        if (basicControl()->deposit_commission == 1) {
            $commissionType = 'deposit';
            $this->levelCommission($deposit->user_id, $deposit->payable_amount_in_base_currency, $commissionType);
        }

        $deposit->status = 1;
        $deposit->save();
        $this->sendDepositNotification($deposit);
    }

    private function processProductOrder($deposit)
    {
        $order = ProductOrder::with(['store', 'store.user', 'orderDetails'])->find($deposit->depositable_id);
        if (!$order) {
            return false;
        }
        $receiveAmount = $order->total_amount + $order->shipping_charge;
        $wallet = updateWallet(optional($order->store)->user_id, $deposit->currency_id, $receiveAmount, 1);
        $order->status = 1;
        foreach ($order->orderDetails as $singleProduct) {
            $stock = StoreProductStock::where('product_id', $singleProduct->product_id)
                ->whereJsonContains('product_attr_lists_id', $singleProduct->attributes_id)->first();
            $newStock = $stock->quantity - $singleProduct->quantity;
            $stock->update(['quantity' => $newStock]);
        }

        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = $receiveAmount;
        $transaction->charge = 0;
        $transaction->currency_id = $order->store?->user?->store_currency_id;
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = 'Product Order via ' . optional($deposit->gateway)->name;
        $order->transactional()->save($transaction);
        $order->save();
        $deposit->status = 1;
        $deposit->save();
        $this->sendOrderNotification($order, $receiveAmount);
    }

    private function processVoucher($deposit)
    {
        $voucher = Voucher::find($deposit->depositable_id);
        if (!$voucher) {
            return false;
        }
        $wallet = updateWallet($voucher->sender_id, $voucher->currency_id, $voucher->received_amount, 1);
        $voucher->status = 2;
        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = $voucher->amount;
        $transaction->charge = $voucher->charge;
        $transaction->currency_id = $voucher->currency_id;
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = 'Voucher payment via ' . optional($deposit->gateway)->name;
        $voucher->transactional()->save($transaction);
        $voucher->save();
        $deposit->status = 1;
        $deposit->save();
        $this->sendVoucherNotification($voucher);
    }

    private function processInvoice($deposit)
    {
        $invoice = Invoice::find($deposit->depositable_id);
        if (!$invoice) {
            return false;
        }
        $receiveAmount = $invoice->grand_total;
        if ($invoice->charge_pay == 0) {
            $receiveAmount -= $invoice->charge;
        }

        $wallet = updateWallet($invoice->sender_id, $invoice->currency_id, $receiveAmount, 1);
        $invoice->status = 'paid';
        $invoice->paid_at = Carbon::now();

        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = $invoice->grand_total;
        $transaction->charge = $invoice->charge;
        $transaction->currency_id = $invoice->currency_id;
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = 'Invoice payment via ' . optional($deposit->gateway)->name;
        $invoice->transactional()->save($transaction);
        $invoice->save();

        $deposit->status = 1;
        $deposit->save();
        $this->sendInvoiceNotification($invoice);
    }

    private function processQrCode($deposit)
    {
        $qrCode = QRCode::find($deposit->depositable_id);
        if (!$qrCode) {
            return false;
        }
        $receiveAmount = $qrCode->amount;

        $wallet = updateWallet($qrCode->user_id, $qrCode->currency_id, $receiveAmount, 1);
        $qrCode->status = 1;

        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = $qrCode->amount;
        $transaction->charge = $qrCode->charge;
        $transaction->currency_id = $qrCode->currency_id;
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = 'Qr payment via ' . optional($deposit->gateway)->name;
        $qrCode->transactional()->save($transaction);
        $qrCode->save();

        $deposit->status = 1;
        $deposit->save();

        $this->sendQrNotification($qrCode);
    }

    private function processApiOrder($deposit)
    {
        $apiOrder = ApiOrder::find($deposit->depositable_id);
        if (!$apiOrder) {
            return false;
        }
        $wallet = updateWallet($apiOrder->user_id, $apiOrder->currency_id, $apiOrder->amount, 1);
        $apiOrder->status = 1;
        $apiOrder->paid_at = Carbon::now();
        $apiOrder->save();

        $transaction = new Transaction();
        $transaction->user_id = $deposit->user_id;
        $transaction->amount = $apiOrder->amount;
        $transaction->charge = $apiOrder->charge;
        $transaction->currency_id = $apiOrder->currency_id;
        $transaction->trx_type = '+';
        $transaction->trx_id = $deposit->trx_id;
        $transaction->remarks = 'Api Order payment via ' . optional($deposit->gateway)->name;
        $apiOrder->transactional()->save($transaction);

        $deposit->status = 1;
        $deposit->save();

        $this->sendApiOrderNotification($apiOrder);
    }


    public function levelCommission($id, $amount, $commissionType = '')
    {
        BonusCommissionJob::dispatch($id, $amount, $commissionType);
        return 1;
    }


    /*Send Notifications*/
    public function sendDepositNotification($deposit): void
    {
        try {
            $user = $deposit->user;
            $params = [
                'amount' => getAmount($deposit->amount),
                'currency' => $deposit->payment_method_currency,
                'transaction' => $deposit->trx_id,
            ];

            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = '#';
            $this->sendMailSms($user, 'ADD_FUND_USER_USER', $params);
            $this->userPushNotification($user, 'ADD_FUND_USER_USER', $params, $action);
            $this->userFirebasePushNotification($user, 'ADD_FUND_USER_USER', $params, $firebaseAction);

            $params = [
                'user' => optional($user)->username,
                'amount' => currencyPosition($deposit->payable_amount_in_base_currency),
                'transaction' => $deposit->trx_id,
            ];
            $actionAdmin = [
                "name" => optional($user)->firstname . ' ' . optional($user)->lastname,
                "image" => getFile(optional($user)->image_driver, optional($user)->image),
                "link" => "#",
                "icon" => "fas fa-ticket-alt text-white"
            ];

            $firebaseAction = "#";
            $this->adminMail('ADD_FUND_USER_ADMIN', $params, $action);
            $this->adminPushNotification('ADD_FUND_USER_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('ADD_FUND_USER_ADMIN', $params, $firebaseAction);
        } catch (\Exception $e) {
            return;
        }
    }

    public function sendOrderNotification($order, $receiveAmount): void
    {
        try {
            $params = [
                'amount' => $receiveAmount,
                'currency' => $deposit->payment_method_currency ?? null,
                'orderNumber' => $order->order_number,
            ];
            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $this->userPushNotification(optional($order->store)->user, 'PRODUCT_ORDER', $params, $action);
            $this->userFirebasePushNotification(optional($order->store)->user, 'PRODUCT_ORDER', $params);

            Mail::to($order->email)->queue(new SendOrderMail($order));

            Mail::to(optional($order->store?->user)->email)->queue(new SendOrderMail($order));
        } catch (\Exception $e) {
            return;
        }
    }

    public function sendVoucherNotification($voucher): void
    {
        try {
            $params = [
                'receiver' => $voucher->email,
                'amount' => $voucher->amount,
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
            ];
            $action = [
                "link" => route('user.voucher.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.voucher.index');
            $sender = $voucher->sender;
            $this->sendMailSms($sender, 'VOUCHER_PAYMENT_TO', $params);
            $this->userPushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $action);
            $this->userFirebasePushNotification($sender, 'VOUCHER_PAYMENT_TO', $params, $firebaseAction);

            $user = new User();
            $user->name = 'Concern';
            $user->email = $voucher->email;
            // send mail sms notification who make payment
            $params = [
                'sender' => optional($voucher->sender)->name,
                'amount' => $voucher->amount,
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
            ];
            $this->sendMailSms($user, 'VOUCHER_PAYMENT_FROM', $params);
            $this->userPushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $action);
            $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $firebaseAction);
        } catch (\Exception $e) {
            return;
        }
    }

    public function sendInvoiceNotification($invoice): void
    {
        try {
            $params = [
                'receiver' => $invoice->customer_email,
                'amount' => $invoice->grand_total,
                'currency' => optional($invoice->currency)->code,
                'transaction' => $invoice->has_slug ?? null,
            ];
            $action = [
                "link" => route('user.invoice.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.invoice.index');
            $sender = $invoice->sendBy;
            $this->sendMailSms($sender, 'INVOICE_PAYMENT_TO', $params);
            $this->userPushNotification($sender, 'INVOICE_PAYMENT_TO', $params, $action);
            $this->userFirebasePushNotification($sender, 'INVOICE_PAYMENT_TO', $params, $firebaseAction);

            $user = new User();
            $user->name = 'Concern';
            $user->email = $invoice->customer_email;
            // send mail sms notification who make payment
            $params = [
                'sender' => optional($invoice->sendBy)->name,
                'amount' => $invoice->grand_total,
                'currency' => optional($invoice->currency)->code,
                'transaction' => $invoice->has_slug ?? null,
            ];
            $this->sendMailSms($user, 'INVOICE_PAYMENT_FROM', $params);
            $this->userPushNotification($user, 'INVOICE_PAYMENT_FROM', $params, $action);
            $this->userFirebasePushNotification($user, 'INVOICE_PAYMENT_FROM', $params, $firebaseAction);
        } catch (\Exception $e) {
            return;
        }
    }

    public function sendQrNotification($qrCode): void
    {
        try {
            $params = [
                'email' => $qrCode->email,
                'amount' => $qrCode->amount,
                'currency' => optional($qrCode->currency)->code,
            ];
            $action = [
                "link" => "",
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $paymentReciver = $qrCode->user;
            $this->sendMailSms($paymentReciver, 'QR_PAYMENT_TO', $params);
            $this->userPushNotification($paymentReciver, 'QR_PAYMENT_TO', $params, $action);
            $this->userFirebasePushNotification($paymentReciver, 'QR_PAYMENT_TO', $params);

            $user = new User();
            $user->name = 'Concern';
            $user->email = $qrCode->email;
            // send mail sms notification who make payment
            $params = [
                'sender' => optional($qrCode->user)->name,
                'amount' => $qrCode->amount,
                'currency' => optional($qrCode->currency)->code,
            ];
            $this->sendMailSms($user, 'QR_PAYMENT_FROM', $params);
            $this->userPushNotification($user, 'QR_PAYMENT_FROM', $params, $action);
            $this->userFirebasePushNotification($user, 'QR_PAYMENT_FROM', $params);
        } catch (\Exception $e) {
            return;
        }
    }

    public function sendApiOrderNotification($apiOrder): void
    {
        try {
            $params = [
                'amount' => $apiOrder->amount,
                'currency' => optional($apiOrder->currency)->code,
                'transaction' => $apiOrder->utr,
            ];
            $action = [
                "link" => route('user.fund.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.fund.index');
            $user = $apiOrder->user;
            $this->sendMailSms($user, 'API_PAYMENT', $params);
            $this->userPushNotification($user, 'API_PAYMENT', $params, $action);
            $this->userFirebasePushNotification($user, 'API_PAYMENT', $params, $firebaseAction);

            $params = [
                'receiver' => optional($apiOrder->user)->username,
                'amount' => $apiOrder->amount,
                'currency' => optional($apiOrder->currency)->code,
                'transaction' => $apiOrder->utr,
            ];
            $action = [
                "link" => "",
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $this->adminPushNotification('ADMIN_API_PAYMENT', $params, $action);
            $this->adminFirebasePushNotification('ADMIN_API_PAYMENT', $params);
        } catch (\Exception $e) {
            return;
        }
    }


    /*cryptoQr*/
    public function cryptoQR($wallet, $amount, $crypto = null)
    {
        $varb = $wallet . "?amount=" . $amount;
        return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$varb&choe=UTF-8";
    }

    /*global make trx*/
    public function makeTransaction($user, $currencyId, $amount, $charge, $trxType, $trxId, $remark, $transactionalId = null, $transactionalType = null): void
    {
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->currency_id = $currencyId;
        $transaction->amount = $amount;
        $transaction->charge = $charge;
        $transaction->trx_type = $trxType;
        $transaction->trx_id = $trxId;
        $transaction->remarks = $remark;
        $transaction->transactional_id = $transactionalId;
        $transaction->transactional_type = $transactionalType;
        $transaction->save();
    }

}
