<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class ExchangeController extends Controller
{
    use ApiValidation, Notify;

    public function exchangeMoney()
    {
        try {
            $data['transactionTypeId'] = config('transactionType.exchange');
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function exchangeMoneySubmit(Request $request)
    {
        $purifiedData = Purify::clean($request->all());
        $validationRules = [
            'from_wallet' => 'required|integer|min:1|not_in:0',
            'to_wallet' => 'required|integer|min:1|not_in:0',
            'amount' => 'required|numeric|min:1|not_in:0',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
        }

        $purifiedData = (object)$purifiedData;

        try {
            $userId = Auth::id();
            $fromCurrencyId = $purifiedData->from_wallet;
            $toCurrencyId = $purifiedData->to_wallet;
            $amount = $purifiedData->amount;

            $checkAmountValidate = $this->checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount);
            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            $exchange = new Exchange();
            $exchange->user_id = $userId;
            $exchange->from_wallet = $checkAmountValidate['from_wallet'];
            $exchange->to_wallet = $checkAmountValidate['to_wallet'];
            $exchange->percentage = $checkAmountValidate['percentage'];
            $exchange->charge_percentage = $checkAmountValidate['charge_percentage'];
            $exchange->charge_fixed = $checkAmountValidate['charge_fixed'];
            $exchange->charge = $checkAmountValidate['charge'];
            $exchange->exchange_rate = $checkAmountValidate['exchange_rate'];
            $exchange->amount = $checkAmountValidate['amount'];
            $exchange->transfer_amount = $checkAmountValidate['transfer_amount'];
            $exchange->received_amount = $checkAmountValidate['received_amount'];
            $exchange->utr = 'E';
            $exchange->status = 0; //pending
            $exchange->save();

            $data['utr'] = $exchange->utr;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount)
    {
        $defaultCurrency = Currency::where('code', basicControl()->base_currency)->first();
        $defaultCurrencyRate = $defaultCurrency->exchange_rate;

        $fromWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $fromCurrencyId])->first();
        $toWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $toCurrencyId])->first();
        $toLimit = optional($toWallet->currency)->currency_type == 0 ? 8 : 4;
        $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $fromCurrencyId, 'transaction_type_id' => config('transactionType.exchange'), 'is_active' => 1])->first();
        $limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 4;

        $amount = getAmount($amount, $limit);
        $status = false;
        $percentage = 0;
        $chargeFixed = 0;
        $chargePercentage = 0;
        $charge = 0;
        $minLimit = 0;
        $maxLimit = 0;

        if ($chargesLimit) {
            $percentage = getAmount($chargesLimit->percentage_charge, $limit);
            $chargeFixed = getAmount($chargesLimit->fixed_charge, $limit);
            $chargePercentage = getAmount(($amount * $percentage) / 100, $limit);
            $charge = getAmount($chargePercentage + $chargeFixed, $limit);
            $minLimit = getAmount($chargesLimit->min_limit, $limit);
            $maxLimit = getAmount($chargesLimit->max_limit, $limit);
        }

        $fromExchangeRate = getAmount($fromWallet->currency->exchange_rate, $limit);
        $toExchangeRate = getAmount($toWallet->currency->exchange_rate, $toLimit);
        $exchangeRate = getAmount(($defaultCurrencyRate / $fromExchangeRate) * $toExchangeRate, $toLimit);

        $transferAmount = getAmount($amount + $charge, $limit);
        $receivedAmount = getAmount($amount * $exchangeRate, $toLimit);

        $fromWalletBalance = getAmount($fromWallet->balance, $limit);
        $fromWalletUpdateBalance = getAmount($fromWalletBalance - $transferAmount, $limit);
        $toWalletUpdateBalance = getAmount($toWallet->balance + $receivedAmount, $toLimit);

        if ($amount < $minLimit || $amount > $maxLimit) {
            $message = "minimum transfer $minLimit and maximum transfer limit $maxLimit";
        } elseif ($transferAmount > $fromWalletBalance) {
            $message = 'Does not have enough money to cover transfer';
        } else {
            $status = true;
            $message = "Remaining balance : $fromWalletUpdateBalance " . optional($fromWallet->currency)->code;
        }

        $data = [
            'balance' => $fromWalletBalance,
            'user_id' => $userId,
            'from_wallet' => $fromWallet->id,
            'to_wallet' => $toWallet->id,
            'percentage' => $percentage,
            'charge_percentage' => $chargePercentage,
            'charge_fixed' => $chargeFixed,
            'charge' => $charge,
            'exchange_rate' => $exchangeRate,
            'amount' => $amount,
            'transfer_amount' => $transferAmount,
            'received_amount' => $receivedAmount,
            'status' => $status,
            'message' => $message,
            'fromWalletUpdateBalance' => $fromWalletUpdateBalance,
            'toWalletUpdateBalance' => $toWalletUpdateBalance,
            'min_limit' => $minLimit,
            'max_limit' => $maxLimit,
            'currency_limit' => $limit,
        ];

        return $data;
    }

    public function exchangeMoneyPreview($utr)
    {
        try {
            $user = Auth::user();
            $exchange = Exchange::with(['fromWallet', 'toWallet'])->where('utr', $utr)
                ->where('user_id', $user->id)
                ->first();
            if (!$exchange || $exchange->status) {
                return response()->json($this->withErrors('Invalid Transaction or already complete'));
            }

            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $data['enable_for'] = in_array('exchange', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
            $data['exchangeFrom'] = optional(optional($exchange->fromWallet)->currency)->code;
            $data['exchangeTo'] = optional(optional($exchange->toWallet)->currency)->code;
            $data['exchangeRate'] = getAmount($exchange->exchange_rate);
            $data['percentage'] = getAmount($exchange->percentage);
            $data['percentageCharge'] = getAmount($exchange->charge_percentage);
            $data['fixedCharge'] = getAmount($exchange->charge_fixed);
            $data['totalCharge'] = getAmount($exchange->charge);
            $data['payableAmount'] = getAmount($exchange->transfer_amount);
            $data['youWillGet'] = getAmount($exchange->received_amount);
            $data['utr'] = $exchange->utr;

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function exchangeMoneyPreviewConfirm(Request $request)
    {
        try {
            $user = Auth::user();
            $exchange = Exchange::with(['fromWallet', 'toWallet'])->where('utr', $request->utr)
                ->where('user_id', $user->id)
                ->first();
            if (!$exchange || $exchange->status) {
                return response()->json($this->withErrors('Invalid Transaction or already complete'));
            }
            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

            if (in_array('exchange', $enable_for)) {
                $purifiedData = Purify::clean($request->all());
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return response()->json($this->withErrors('You have entered an incorrect PIN'));
                }
            }

            $checkAmountValidate = $this->checkAmountValidate($exchange->user_id, optional($exchange->fromWallet)->currency_id, optional($exchange->toWallet)->currency_id, $exchange->amount);
            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }


            DB::beginTransaction();
            try {
                /*Deduct money from Wallet */
                $sender_wallet = updateWallet($exchange->user_id, optional($exchange->fromWallet)->currency_id, $exchange->transfer_amount, 0);
                $remark = 'Balance debited from exchange money';
                BasicService::makeTransaction($user, optional($exchange->fromWallet)->currency_id, $exchange->transfer_amount,
                    $exchange->charge, '-', $exchange->utr, $remark, $exchange->id, Exchange::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($exchange->user_id, optional($exchange->toWallet)->currency_id, $exchange->received_amount, 1);
                $remark = 'Balance credited from exchange money';
                BasicService::makeTransaction($user, optional($exchange->toWallet)->currency_id, $exchange->received_amount,
                    0, '+', $exchange->utr, $remark, $exchange->id, Exchange::class);

                $exchange->status = 1;
                $exchange->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }

            $receivedUser = $user;
            $params = [
                'from_amount' => getAmount($exchange->transfer_amount),
                'from_currency' => optional(optional($exchange->fromWallet)->currency)->code,
                'to_amount' => getAmount($exchange->received_amount),
                'to_currency' => optional(optional($exchange->toWallet)->currency)->code,
                'transaction' => $exchange->utr,
            ];

            $action = [
                "link" => route('user.exchange.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.exchange.index');
            $this->sendMailSms($receivedUser, 'MONEY_EXCHANGE', $params);
            $this->userPushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $firebaseAction);

            return response()->json($this->withSuccess("Your exchange has been completed your remaining amount of money $sender_wallet"));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


    public function exchangeMoneyList(Request $request)
    {
        try {
            $userId = Auth::id();
            $data['wallets'] = Wallet::with(['currency:id,name,code'])
                ->select(['id', 'currency_id', 'user_id'])
                ->where('user_id', $userId)
                ->get();

            $data['exchanges'] = Exchange::with(['fromWallet', 'toWallet'])
                ->where('user_id', $userId)
                ->search($request->all())
                ->latest()
                ->paginate(20)
                ->through(fn($exchange) => $exchange->formatted());

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

}
