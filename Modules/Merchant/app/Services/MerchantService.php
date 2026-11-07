<?php

namespace Modules\Merchant\Services;

use App\Models\TwoFactorSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Wallet;
use App\Models\Transaction;
use Modules\Merchant\Models\MakePayment;
use Modules\Merchant\Models\MerchantGlobalSetting;
use Modules\Merchant\Models\MerchantSetting;

class MerchantService
{

    public function processPayment($data, $user_id, $merchant_id): ?MakePayment
    {
        $trx_id = 'MP' . strRandomNum(10);

        return MakePayment::create([
            'user_id'           => $user_id,
            'merchant_id'       => $merchant_id,
            'currency_id'       => $data->currency_id,
            'amount'            => $data->amount,
            'charge'            => $data->charge,
            'payable_amount'    => $data->payable_amount,
            'received_amount'   => $data->received_amount,
            'trx_id'            => $trx_id,
            'charge_applied_to' => $data->charge_applied_to ?? 'user',
        ]);
    }

    public function getValidTransaction($user, $trx_id, $type)
    {
        $query = MakePayment::query()
            ->with([
                'user:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                'merchant:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                'currency:id,code,symbol,exchange_rate',
            ])
            ->where('trx_id', $trx_id);

        if ($type === 'user') {
            $query->where('user_id', $user->id);
        } else {
            $query->where('merchant_id', $user->id);
        }

        $transaction = $query->first();
        if (!$transaction || $transaction->status) {
            $message = !$transaction ? 'Unauthorized or invalid transaction.' : 'Transaction already complete.';
            return ['error' => $message];
        }
        return $transaction;
    }

    public function createTransaction($userId, $currencyId, $amount, $charge, $type, $remarks, $trxId, $transactional = null)
    {
        return Transaction::create([
            'user_id' => $userId,
            'currency_id' => $currencyId,
            'amount' => $amount,
            'charge' => $charge,
            'trx_type' => $type,
            'remarks' => $remarks,
            'trx_id' => $trxId,
            'transactional_type' => $transactional ? get_class($transactional) : null,
            'transactional_id' => $transactional?->id,
        ]);
    }

    public function getWallet($user_id, $currency_id)
    {
        $wallet = Wallet::firstOrCreate([
            'user_id' => $user_id,
            'currency_id' => $currency_id,
        ]);
        $limit = optional($wallet->currency)->currency_type == 0 ? 8 : 2;
        $wallet->balance = getAmount($wallet->balance, $limit);
        return $wallet;
    }

    public function checkAmountValidate($amount, $currency_id, $recipient): array
    {
        try {
            $merchantSettings = MerchantSetting::query()
                ->whereHas('merchant', function ($query) use ($recipient) {
                    $query->where(function ($q) use ($recipient) {
                        $q->where('username', $recipient)
                            ->orWhere('email', $recipient);
                    })
                        ->where('type', 'merchant')
                        ->where('status', 1);
                })
                ->first();
            throwIfInvalid(!$merchantSettings, 'Merchant not found or inactive.');

            $user_id = Auth::id();
            $settings = MerchantGlobalSetting::where('currency_id', $currency_id)
                ->where('status', true)
                ->first();
            throwIfInvalid(!$settings, 'Settings not configured for this currency.');

            $limit = optional($settings->currency)->currency_type == 0 ? 8 : 2;

            $wallet = $this->getWallet($user_id, $currency_id);
            throwIfInvalid($wallet->is_active != 1, 'Wallet not available for this transaction.');

            $balance = getAmount($wallet->balance, $limit);
            $amount = getAmount($amount, $limit);

            throwIfInvalid($amount <= 0, 'Amount must be greater than 0.');

            $fixedCharge = getAmount($settings->fixed_charge, $limit);
            $percentCharge = getAmount($amount * $settings->percent_charge / 100, $limit);
            $charge = getAmount($fixedCharge + $percentCharge, $limit);

            if ($merchantSettings->charge_applied_to == 'user') {
                $payable_amount = $amount + $charge;
                $received_amount = $amount;
            } else {
                $payable_amount = $amount;
                $received_amount = $amount - $charge;
            }

            throwIfInvalid($payable_amount < $charge, "Amount must cover the transaction charge");
            throwIfInvalid($payable_amount > $balance, 'Insufficient wallet balance.');

            $remaining_balance = getAmount($balance - $payable_amount, $limit);

            return [
                'status' => true,
                'message' => "Remaining balance: $remaining_balance",
                'amount' => $amount,
                'fixed_charge' => $fixedCharge,
                'percentage' => $settings->percent_charge,
                'percentage_charge' => $percentCharge,
                'charge' => $charge,
                'balance' => $balance,
                'payable_amount' => $payable_amount,
                'received_amount' => $received_amount,
                'remaining_balance' => $remaining_balance,
                'currency_id' => $currency_id,
                'currency_limit' => $limit,
                'charge_applied_to' => $merchantSettings->charge_applied_to,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function securityPinStatus($user, $type): bool
    {
        $setting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enabledTypes = json_decode($setting->enable_for ?? '[]', true);

        return in_array($type, $enabledTypes);
    }

    public function validateSecurityPin($user, $securityPin, $type): array
    {
        $setting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = json_decode($setting->enable_for ?? '[]', true);
        if (in_array($type, $enable_for)) {
            if (!$securityPin || !is_numeric($securityPin) || strlen($securityPin) !== 5) {
                return ['error' => 'Security PIN required & must be a 5-digit number.'];
            }

            if (!Hash::check($securityPin, $setting->security_pin)) {
                return ['error' => 'You have entered an incorrect PIN'];
            }
        }

        return ['success' => true];
    }


    public static function getSidebarCounts(): object
    {
        try {
            $agentCounts = DB::table('users')
                ->where('type', 'merchant')
                ->select([
                    DB::raw('COUNT(CASE WHEN status = 1 THEN 1 END) as active'),
                    DB::raw('COUNT(CASE WHEN status = 0 THEN 1 END) as blocked'),
                    DB::raw('COUNT(CASE WHEN email_verification = 0 THEN 1 END) as email_unverified'),
                    DB::raw('COUNT(CASE WHEN sms_verification = 0 THEN 1 END) as sms_unverified')
                ])
                ->first();
            return (object)array_merge((array)$agentCounts);
        } catch (\Throwable $e) {
            return (object)[
                'active' => 0,
                'blocked' => 0,
                'email_unverified' => 0,
                'sms_unverified' => 0,
            ];
        }

    }

}
