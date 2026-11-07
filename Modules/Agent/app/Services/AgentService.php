<?php

namespace Modules\Agent\Services;

use App\Models\TwoFactorSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Wallet;
use App\Models\Transaction;
use Modules\Agent\Models\AgentTransaction;
use Modules\Agent\Models\AgentSetting;

class AgentService
{
    public function process($agent, $amount, $type, $currencyId, $customer)
    {
        if ($agent->type !== 'agent') {
            throw new \Exception("Invalid agent.");
        }
        $this->checkAmountValidate($amount, $currencyId, $type);

        $this->checkLimits($customer->id, $type, $amount, $currencyId);

        $settings = AgentSetting::where('type', $type)
            ->where('currency_id', $currencyId)
            ->where('status', true)
            ->first();
        if (!$settings) {
            throw new \Exception("Agent settings not configured.");
        }

        [$charge, $commission] = $this->calculateChargeAndCommission($amount, $settings);

        if ($type === 'cash_in') {
            $trx_id = "CI".strRandomNum(10);
        }else{
            $trx_id = "CO".strRandomNum(10);
        }

        $agentTx = AgentTransaction::create([
            'type'         => $type,
            'currency_id'  => $currencyId,
            'user_id'      => $customer?->id,
            'agent_id'     => $agent->id,
            'amount'       => $amount,
            'charge'       => $charge,
            'net_amount'   => $amount + $charge,
            'commission'   => $commission,
            'trx_id'       => $trx_id,
        ]);
        return $agentTx;
    }

    public function getValidTransaction($user, $trx_id, $type)
    {
        $query = AgentTransaction::query()
            ->with([
                'user:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                'agent:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                'currency:id,code,symbol,exchange_rate',
            ])
            ->where('type', $type)
            ->where('trx_id', $trx_id);

        if ($type === 'cash_in') {
            $query->where('agent_id', $user->id);
        } else {
            $query->where('user_id', $user->id);
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
            'user_id'            => $userId,
            'currency_id'        => $currencyId,
            'amount'             => $amount,
            'charge'             => $charge,
            'trx_type'           => $type,
            'remarks'            => $remarks,
            'trx_id'             => $trxId,
            'transactional_type' => $transactional ? get_class($transactional) : null,
            'transactional_id'   => $transactional?->id,
        ]);
    }

    public function checkLimits($userId, $type, $amount, $currencyId): void
    {
        $settings = AgentSetting::where('type', $type)
            ->where('currency_id', $currencyId)
            ->where('status', true)
            ->first();

        if (!$settings) return;

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $dailyTotal = AgentTransaction::where('user_id', $userId)
            ->where('type', $type)
            ->where('currency_id', $currencyId)
            ->where('created_at', '>=', $today)
            ->sum('amount');

        $monthlyTotal = AgentTransaction::where('user_id', $userId)
            ->where('type', $type)
            ->where('currency_id', $currencyId)
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        if ($settings->daily_limit > 0 && ($dailyTotal + $amount) > $settings->daily_limit) {
            throw new \Exception("Daily limit exceeded.");
        }
        if ($settings->monthly_limit > 0 && ($monthlyTotal + $amount) > $settings->monthly_limit) {
            throw new \Exception("Monthly limit exceeded.");
        }
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

    public function calculateChargeAndCommission($amount, AgentSetting $settings)
    {
        $charge = ($amount * $settings->percent_charge) / 100 + $settings->fixed_charge;
        $commission = ($amount * $settings->percent_commission) / 100 + $settings->fixed_commission;

        return [$charge, $commission];
    }

    public function checkAmountValidate($amount, $currency_id, $type): array
    {
        $user_id = Auth::id();
        $settings = AgentSetting::where('type', $type)
            ->where('currency_id', $currency_id)
            ->where('status', true)
            ->first();

        if (!$settings) {
            return [
                'status' => false,
                'message' => Str::headline($type)." settings not configured.",
            ];
        }
        $limit = optional($settings->currency)->currency_type == 0 ? 8 : 2;

        $minLimit = getAmount($settings->min_limit, $limit);
        $maxLimit = getAmount($settings->max_limit, $limit);

        $wallet = $this->getWallet($user_id, $currency_id);
        $balance = getAmount($wallet->balance, $limit);

        $amount = getAmount($amount, $limit);
        $fixedCharge = getAmount($settings->fixed_charge, $limit);
        $percentCharge = getAmount($amount * $settings->percent_charge / 100, $limit);
        $charge = getAmount($fixedCharge + $percentCharge, $limit);

        $fixedCommission = getAmount($settings->fixed_commission, $limit);
        $percentCommission = getAmount($amount * $settings->percent_commission / 100, $limit);
        $commission = getAmount($fixedCommission + $percentCommission, $limit);

        $transfer_amount = $amount + $charge;
        $remaining_balance = getAmount($balance - $transfer_amount, $limit);
        if ($type == 'cash_in') {
            $received_amount = $amount;
        } else {
            $received_amount = $amount - $charge;
        }

        $status = false;
        $message = '';

        if ($wallet->is_active != 1) {
            $message = 'Wallet not available for this transaction.';
        } elseif ($amount < $minLimit || $amount > $maxLimit) {
            $message = "Amount must be between {$minLimit} and {$maxLimit}.";
        } elseif ($transfer_amount > $balance) {
            $message = 'Insufficient wallet balance.';
        } else {
            $status = true;
            $message = "Remaining balance: $remaining_balance";
        }

        return [
            'status'             => $status,
            'message'            => $message,
            'amount'             => $amount,
            'fixed_charge'       => $fixedCharge,
            'percentage'         => $settings->percent_charge,
            'percentage_charge'  => $percentCharge,
            'charge'             => $charge,
            'commission'         => $commission,
            'min_limit'          => $minLimit,
            'max_limit'          => $maxLimit,
            'balance'            => $balance,
            'transfer_amount'    => $transfer_amount,
            'received_amount'    => $amount,
            'remaining_balance'  => $remaining_balance,
            'currency_id'        => $currency_id,
            'currency_limit'     => $limit,
        ];
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
                ->where('type', 'agent')
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
