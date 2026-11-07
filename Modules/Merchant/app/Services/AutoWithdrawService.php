<?php

namespace Modules\Merchant\Services;

use App\Models\Currency;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Traits\PayoutTrait;
use Modules\Merchant\Models\MerchantSetting;

class AutoWithdrawService
{
    use PayoutTrait;

    public function process(MerchantSetting $setting, Wallet $wallet, PayoutMethod $payoutMethod, object $currencyInfo
    ): void {
        $currencyType = $payoutMethod->currency_type;
        $limit = $currencyType === 0 ? 8 : 2;

        $amount = getAmount($setting->withdraw_amount, $limit);
        $conversionRate = getAmount($currencyInfo->conversion_rate ?? 1, $limit);

        // Calculate payout charges
        $percentage = getAmount($currencyInfo->percentage_charge ?? 0, $limit);
        $percentageCharge = getAmount(($amount * $percentage) / 100, $limit);
        $fixedCharge = getAmount($currencyInfo->fixed_charge ?? 0, $limit);
        $payoutCharge = getAmount($percentageCharge + $fixedCharge, $limit);
        $payoutAmount = getAmount($amount + $payoutCharge, $limit);

        // Convert to base currency
        $amountInBase = getAmount($amount / $conversionRate, $limit);
        $chargeInBase = getAmount($payoutCharge / $conversionRate, $limit);
        $netInBase = $amountInBase + $chargeInBase;

        if ($payoutAmount <= 0){
            return;
        }

        updateWallet($wallet->user_id, $wallet->currency_id, $amount, 0);

        $payout = Payout::create([
            'user_id'                     => $wallet->user_id,
            'payout_method_id'            => $payoutMethod->id ?? null,
            'currency_id'                 => $wallet->currency_id,
            'payout_currency_code'        => $setting->withdraw_currency ?? null,
            'amount'                      => $amount,
            'charge'                      => $payoutCharge,
            'net_amount'                  => $payoutAmount,
            'amount_in_base_currency'     => $amountInBase ?? null,
            'charge_in_base_currency'     => $chargeInBase ?? null,
            'net_amount_in_base_currency' => $netInBase ?? null,
            'information'                 => $setting->withdraw_information,
            'feedback'                    => null,
            'status'                      => 1,
        ]);


//        $this->createPayoutTransaction($payout,'-','Merchant Auto Withdraw');
//        $this->sendNotification($payout);

    }



    public function sendNotification($payout): void
    {
        try {
            $user = $payout->user;
            $params = [
                'amount' => getAmount($payout->amount),
                'currency' => $payout->payout_currency_code,
                'transaction' => $payout->trx_id,
            ];

            $action = [
                "link" => "#",
                "icon" => "bi-cash"
            ];
            $firebaseAction = '#';
            $this->sendMailSms($user, 'MERCHANT_AUTO_WITHDRAW', $params);
            $this->userPushNotification($user, 'MERCHANT_AUTO_WITHDRAW', $params, $action);
            $this->userFirebasePushNotification($user, 'MERCHANT_AUTO_WITHDRAW', $params, $firebaseAction);

            $params = [
                'user' => optional($user)->username,
                'amount' => currencyPosition($payout->net_amount_in_base_currency),
                'transaction' => $payout->trx_id,
            ];
            $actionAdmin = [
                "name" => $user->name,
                "image" => $user?->getImage(),
                "link" => "#",
                "icon" => "bi-cash"
            ];

            $firebaseAction = "#";
            $this->adminMail('MERCHANT_AUTO_WITHDRAW_ADMIN', $params, $action);
            $this->adminPushNotification('MERCHANT_AUTO_WITHDRAW_ADMIN', $params, $actionAdmin);
            $this->adminFirebasePushNotification('MERCHANT_AUTO_WITHDRAW_ADMIN', $params, $firebaseAction);
        } catch (\Exception $e) {
            return;
        }
    }





}
