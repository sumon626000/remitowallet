<?php

namespace App\Traits;

use App\Models\ChargesLimit;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Gateway;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;

trait ChargeLimitTrait
{
    public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from, $methodId = null, $requestFrom = null)
    {
        if ($methodId) {
            $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'payment_method_id' => $methodId, 'is_active' => 1])->first();
        } else {
            $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'is_active' => 1])->first();
        }

        if (!$chargesLimit) {
            // Fallback to gateway receivable_currencies when no ChargesLimit DB record exists
            if ($methodId) {
                $gateway = Gateway::where('id', $methodId)->where('status', 1)->first();
                $currencyModel = Currency::find($currency_id);
                if ($gateway) {
                    $receivableCurrencies = $gateway->receivable_currencies;
                    $arr = is_string($receivableCurrencies) ? json_decode($receivableCurrencies, true) : $receivableCurrencies;

                    $code = $currencyModel->code ?? null;
                    $match = null;
                    if ($code && is_array($arr)) {
                        foreach ($arr as $item) {
                            if ((isset($item['currency']) && strcasecmp($item['currency'], $code) === 0)
                                || (isset($item['name']) && strcasecmp($item['name'], $code) === 0)
                                || (isset($item['currency_symbol']) && strcasecmp($item['currency_symbol'], $code) === 0)) {
                                $match = $item;
                                break;
                            }
                        }
                    }

                    if ($match) {
                        // Create a temporary chargesLimit-like object from gateway config
                        $chargesLimit = (object)[
                            'id' => null,
                            'percentage_charge' => $match['percentage_charge'] ?? $match['percentage'] ?? 0,
                            'fixed_charge' => $match['fixed_charge'] ?? 0,
                            'min_limit' => $match['min_limit'] ?? 0,
                            'max_limit' => $match['max_limit'] ?? 0,
                            'convention_rate' => $match['conversion_rate'] ?? $match['convention_rate'] ?? 1,
                            'currency' => $currencyModel,
                        ];
                    }
                }
            }

            if (!$chargesLimit) {
                $data['status'] = false;
                $data['message'] = 'This transaction can’t be processed at the moment due to missing charge or limit settings. Please contact support.';
                return $data;
            }
        }
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);

        $limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

        $balance = getAmount($wallet->balance, $limit);
        $amount = getAmount($amount, $limit);
        $status = false;
        $charge = 0;
        $min_limit = 0;
        $max_limit = 0;
        $fixed_charge = 0;
        $percentage = 0;
        $percentage_charge = 0;

        if ($chargesLimit) {
            $percentage = getAmount($chargesLimit->percentage_charge, $limit);
            $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
            $fixed_charge = getAmount($chargesLimit->fixed_charge, $limit);
            $min_limit = getAmount($chargesLimit->min_limit, $limit);
            $max_limit = getAmount($chargesLimit->max_limit, $limit);
            $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        }

        $payable_amount = $amount + $charge;

        if ($charge_from) {
            $transfer_amount = $amount;
            $received_amount = getAmount($amount - $charge, $limit);
        } else {
            $transfer_amount = getAmount($amount + $charge, $limit);
            $received_amount = $amount;
        }

        $remaining_balance = getAmount($balance - $transfer_amount, $limit);


        if ($transfer_amount <= 0 || $received_amount <= 0) {
            $message = 'Amount is too low to cover the charges. Please increase the amount.';
        } elseif ($wallet->is_active != 1) {
            $message = 'Currency not available for this transfer';
        } elseif ($amount < $min_limit || $amount > $max_limit) {
            $message = "minimum payment $min_limit and maximum payment limit $max_limit";
        } elseif ($requestFrom != 'requester' && $transfer_amount > $balance) {
            $message = 'Does not have enough money to transfer';
        } else {
            $status = true;
            $message = "";
            if (!in_array($transaction_type_id, [config('transactionType.request')])) {
                $message = "Remaining balance : $remaining_balance";
            }
        }

        $data['status'] = $status;
        $data['message'] = $message;
        $data['charges_limit_id'] = $chargesLimit->id;
        $data['fixed_charge'] = $fixed_charge;
        $data['percentage'] = $percentage;
        $data['percentage_charge'] = $percentage_charge;
        $data['min_limit'] = $min_limit;
        $data['max_limit'] = $max_limit;
        $data['balance'] = $balance;
        $data['transfer_amount'] = $transfer_amount;
        $data['received_amount'] = $received_amount;
        $data['remaining_balance'] = $remaining_balance;
        $data['charge'] = $charge;
        $data['charge_from'] = $charge_from;
        $data['amount'] = $amount;
        $data['currency_id'] = $currency_id;
        $data['currency_limit'] = $limit;
        $data['payable_amount'] = $payable_amount;
        $data['convention_rate'] = $chargesLimit->convention_rate;
        return $data;
    }

    public function checkRecipientValidate($recipient)
    {
        $receiver = User::where('username', $recipient)
            ->orWhere('email', $recipient)
            ->byType('user')
            ->first();

        if ($receiver && $receiver->id == Auth::id()) {
            $data['status'] = false;
            $data['message'] = 'Transfer not allowed to self email';
        } elseif ($receiver && $receiver->id != Auth::id()) {
            $data['status'] = true;
            $data['message'] = "User found. Are you looking for $receiver->name?";
            $data['receiver'] = $receiver;
        } else {
            $data['status'] = false;
            $data['message'] = 'No user found';
        }
        return $data;
    }
}
