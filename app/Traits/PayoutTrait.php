<?php

namespace App\Traits;

use App\Models\Currency;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

trait PayoutTrait
{
    use ApiValidation ,Notify ,Upload;

    public function validationCheck($amount, $selectedCurrency, $payoutMethodId): array
    {
        $withdrawalDays = config('withdrawaldays');
        $today = now()->format('l');
        if (!($withdrawalDays[$today] ?? 0)) {
            return ['status' => false, 'message' => "Withdrawals are not available today. Please try again on a day when withdrawals are permitted"];
        }

        $selectedPayoutMethod = PayoutMethod::where('id', $payoutMethodId)->where('is_active', 1)->first();
        if (!$selectedPayoutMethod) {
            return ['status' => false, 'message' => "Payment method not available for this transaction"];
        }

        $currency = Currency::where('code', $selectedCurrency)->first();
        if (!$currency) {
            return ['status' => false, 'message' => "Invalid currency selected"];
        }

        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency->id]);
        if ($wallet->is_active != 1) {
            return ['status' => false, 'message' => "Currency not available for this transfer"];
        }

        $selectedCurrencyIndex = array_search($selectedCurrency, $selectedPayoutMethod->supported_currency);
        if ($selectedCurrencyIndex !== false) {
            $selectedPayCurrency = $selectedPayoutMethod->supported_currency[$selectedCurrencyIndex];
        } else {
            return ['status' => false, 'message' => "Please choose a supported currency you'd like to use for payment"];
        }

        $payoutCurrencies = $selectedPayoutMethod->payout_currencies;
        if (is_array($payoutCurrencies)) {
            $currencyInfo = collect($payoutCurrencies)->firstWhere('name', $selectedPayCurrency) ?? collect($payoutCurrencies)->firstWhere('currency_symbol', $selectedPayCurrency);
        } else {
            return ['status' => false, 'message' => "Payout currency information not found"];
        }

        $currencyType = $selectedPayoutMethod->currency_type;
        $limit = $currencyType == 0 ? 8 : 2;
        $amount = getAmount($amount, $limit);
        $balance = getAmount($wallet->balance, $limit);

        if ($currencyInfo) {
            $percentage = getAmount($currencyInfo->percentage_charge, $limit);
            $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
            $fixed_charge = getAmount($currencyInfo->fixed_charge, $limit);
            $min_limit = getAmount($currencyInfo->min_limit, $limit);
            $max_limit = getAmount($currencyInfo->max_limit, $limit);
            $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        }

        $payout_amount = getAmount($amount + $charge, $limit);
        $payout_amount_in_base_currency = getAmount($amount / ($currencyInfo->conversion_rate ?? 1), $limit);
        $charge_in_base_currency = getAmount($charge / ($currencyInfo->conversion_rate ?? 1), $limit);
        $net_amount_in_base_currency = $payout_amount_in_base_currency + $charge_in_base_currency;

        if ($amount < $min_limit || $amount > $max_limit) {
            $message = "Minimum amount $min_limit and maximum amount limit $max_limit";
            $status = false;
        } elseif ($payout_amount > $balance) {
            $message = "Insufficient balance for transaction";
            $status = false;
        } else {
            $status = true;
            $message = "Amount: $amount $selectedPayCurrency";
        }

        return [
            'status' => $status,
            'message' => $message,
            'payout_method_id' => $selectedPayoutMethod->id,
            'fixed_charge' => $fixed_charge,
            'percentage' => $percentage,
            'percentage_charge' => $percentage_charge,
            'min_limit' => $min_limit,
            'max_limit' => $max_limit,
            'charge' => $charge,
            'amount' => $amount,
            'payout_charge' => $charge,
            'net_payout_amount' => $payout_amount,
            'amount_in_base_currency' => $payout_amount_in_base_currency,
            'charge_in_base_currency' => $charge_in_base_currency,
            'net_amount_in_base_currency' => $net_amount_in_base_currency,
            'conversion_rate' => getAmount($currencyInfo->conversion_rate ?? 1),
            'currency' => $currencyInfo->name ?? $currencyInfo->currency_symbol,
            'currency_id' => $currency->id,
            'base_currency' => basicControl()->base_currency,
            'currency_limit' => $limit,
            'remaining_balance' => getAmount($balance - $payout_amount, $limit)
        ];
    }

    public function createPayout($validateData)
    {
        $payout = new Payout();
        $payout->user_id = Auth::id();
        $payout->payout_method_id = $validateData['payout_method_id'];
        $payout->currency_id = $validateData['currency_id'];
        $payout->payout_currency_code = $validateData['currency'];
        $payout->amount = $validateData['amount'];
        $payout->charge = $validateData['payout_charge'];
        $payout->net_amount = $validateData['net_payout_amount'];
        $payout->amount_in_base_currency = $validateData['amount_in_base_currency'];
        $payout->charge_in_base_currency = $validateData['charge_in_base_currency'];
        $payout->net_amount_in_base_currency = $validateData['net_amount_in_base_currency'];
        $payout->information = $validateData['information'] ?? null;
        $payout->feedback = $validateData['feedback'] ?? null;
        $payout->status = 0;
        $payout->save();

        return $payout;
    }

    public function createPayoutTransaction($payout,$type,$remark): void
    {
        $transaction = new Transaction();
        $transaction->user_id = $payout->user_id;
        $transaction->currency_id = $payout->currency_id;
        $transaction->amount = $payout->amount;
        $transaction->charge = $payout->charge;
        $transaction->trx_type = $type;
        $transaction->trx_id = $payout->trx_id;
        $transaction->remarks = $remark;
        $transaction->transactional_id = $payout->id;
        $transaction->transactional_type = get_class($payout);
        $transaction->save();
    }


    public function getValidationRules($payoutMethod, $request): array
    {
        $rules = [];
        $params = $payoutMethod->inputForm ?? [];
        foreach ($params as $key => $cus) {
            $rule = '';
            switch ($cus->type) {
                case 'file':$rule = 'image|mimes:jpeg,jpg,png|max:4048';break;
                case 'text':$rule = 'max:191';break;
                case 'number':$rule = 'numeric';break;
                case 'textarea':$rule = 'min:3|max:300';break;
            }
            $validation = $cus->validation == 'required' ? 'required' : 'nullable';
            $rules[$key] = $validation . '|' . $rule;
        }
        return $rules;
    }

    public function getRequestFields($payoutMethod, $request)
    {
        $reqField = [];
        $params = $payoutMethod->inputForm ?? [];
        foreach ($request->except('_token', '_method', 'type', 'currency_code', 'bank') as $k => $v) {
            foreach ($params as $inKey => $inVal) {
                if ($k == $inVal->field_name) {
                    if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                        try {
                            $file = $this->fileUpload($request[$inKey], config('filelocation.payoutLog.path'),null, null, 'webp', 80);
                            $reqField[$inKey] = [
                                'field_name' => $inVal->field_name,
                                'field_value' => $file['path'],
                                'field_driver' => $file['driver'],
                                'validation' => $inVal->validation,
                                'type' => $inVal->type,
                            ];
                        } catch (\Exception $exp) {
                            session()->flash('error', 'Could not upload your ' . $inKey);
                            return back()->withInput();
                        }
                    } else {
                        $reqField[$inKey] = [
                            'field_name' => $inVal->field_name,
                            'validation' => $inVal->validation,
                            'field_value' => $v,
                            'type' => $inVal->type,
                        ];
                    }
                }
            }
        }
        return $reqField;
    }

    public function addRequestField(array &$reqField, string $key, string $name, $value): void
    {
        $reqField[$key] = [
            'field_name' => $name,
            'field_value' => $value,
            'type' => 'text',
        ];
    }

    private function validateSecurityPin($user, $securityPin)
    {
        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if (in_array('payout', $enable_for)) {
            if (!$securityPin || !is_numeric($securityPin) || strlen($securityPin) !== 5) {
                return ['error' => 'Security PIN required & must be a 5-digit number.'];
            }

            if (!Hash::check($securityPin, $twoFactorSetting->security_pin)) {
                return ['error' => 'You have entered an incorrect PIN'];
            }
        }

        return ['success' => true];
    }

    public function userNotify($user, $payout): void
    {
        $params = [
            'sender' => $user->name,
            'amount' => getAmount($payout->amount),
            'currency' => $payout->payout_currency_code,
            'transaction' => $payout->trx_id,
        ];

        $action = [
            "link" => '#',
            "icon" => "bi-cash"
        ];
        $firebaseAction = "#";
        $this->adminMail('PAYOUT_REQUEST_TO_ADMIN', $params);
        $this->adminPushNotification('PAYOUT_REQUEST_TO_ADMIN', $params, $action);
        $this->adminFirebasePushNotification('PAYOUT_REQUEST_TO_ADMIN', $params, $firebaseAction);

        $params = [
            'amount' => getAmount($payout->amount),
            'currency' => optional($payout->currency)->code,
            'transaction' => $payout->trx_id,
        ];
        $action = [
            "link" => route('user.payout.index') ?? '#',
            "icon" => "bi-cash"
        ];
        $firebaseAction = route('user.payout.index') ?? "#";
        $this->sendMailSms($user, 'PAYOUT_REQUEST_FROM', $params);
        $this->userPushNotification($user, 'PAYOUT_REQUEST_FROM', $params, $action);
        $this->userFirebasePushNotification($user, 'PAYOUT_REQUEST_FROM', $params, $firebaseAction);
    }


    public function cancelPayout($id, $feedback = null)
    {
        $payout = Payout::with('user', 'method')
            ->where('id', $id)
            ->whereIn('status', [1])
            ->first();
        throwIfInvalid(!$payout, 'Transaction not found or already processed.');
        // Refund back to wallet without charge
        $sender_wallet = updateWallet($payout->user_id, $payout->currency_id, $payout->amount, 1);

        $payout->feedback = $feedback;
        $payout->status = 3;
        $payout->save();

        $receivedUser = $payout->user;
        $params = [
            'sender' => $receivedUser->name,
            'amount' => getAmount($payout->amount),
            'currency' => $payout->payout_currency_code,
            'transaction' => $payout->trx_id,
        ];
        $action = [
            "link" => "#",
            "icon" => "bi-cash"
        ];
        $firebaseAction = "#";

        $this->sendMailSms($receivedUser, 'PAYOUT_CANCEL', $params);
        $this->userPushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $action);
        $this->userFirebasePushNotification($receivedUser, 'PAYOUT_CANCEL', $params, $firebaseAction);

        return true;
    }


    public function processPayout($payout)
    {
        try {
            $basic = basicControl();

            if (optional($payout->method)->is_automatic == 1 && $basic->automatic_payout_permission) {
                $methodClass = 'App\\Services\\Payout\\' . $payout->method?->code . '\\Card';
                $data = $methodClass::payouts($payout);
                throwIfInvalid(!$data, 'Method not available or payout failed.');

                if ($data['status'] == 'error') {
                    $payout->last_error = $data['data'] ?? 'Unknown Error';
                    $payout->status = 1;
                    $payout->save();
                    return ['success' => true, 'message' => 'Payout generated and will be reviewed manually.'];
                } else {
                    if (in_array(optional($payout->method)->code, ['coinbase', 'perfectmoney'])) {
                        $remark = 'Withdraw via ' . $payout->method?->name;
                        $this->createPayoutTransaction($payout, '-', $remark);
                        $payout->status = 2;
                        $payout->save();
                        return ['success' => true, 'message' => 'Payout succeeded.'];
                    } else {
                        $payout->response_id = $data['response_id'] ?? null;
                        $payout->status = 1;
                        $payout->save();
                        return ['success' => true, 'message' => 'Payout generated'];
                    }
                }
            }
            // Automatic is off
            else {
                $payout->status = 1;
                $payout->save();
                return ['success' => true, 'message' => 'Payout generated successfully.'];
            }
        } catch(\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }



}
