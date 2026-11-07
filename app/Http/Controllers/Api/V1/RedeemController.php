<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\RedeemCode;
use App\Models\TwoFactorSetting;
use App\Traits\ApiValidation;
use App\Traits\ChargeLimitTrait;
use App\Traits\Notify;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class RedeemController extends Controller
{
    use ApiValidation, ChargeLimitTrait, Notify;

    public function redeemGenerateCode()
    {
        try {
            $data['transactionTypeId'] = config('transactionType.redeem');
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function redeemCheckAmount(Request $request)
    {
        $amount = $request->amount;
        $currency_id = $request->currency_id;
        $transaction_type_id = $request->transaction_type_id;
        $charge_from = $request->charge_from;
        if (!$amount || !$currency_id || !$transaction_type_id) {
            return response()->json($this->withErrors('Some fields are missing'));
        }
        $data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from);
        return response()->json($this->withSuccess($data));
    }

    public function redeemGenerateCodeSubmit(Request $request)
    {
        $purifiedData = Purify::clean($request->all());
        $validationRules = [
            'amount' => 'required|numeric|min:1|not_in:0',
            'currency' => 'required|integer|min:1|not_in:0',
            'charge_from' => 'nullable|integer|min:0|max:1',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
        }

        try {
            $purifiedData = (object)$purifiedData;
            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $charge_from = isset($purifiedData->charge_from);

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.redeem'), $charge_from);//1 = transfer
            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            $redeemCode = new RedeemCode();
            $redeemCode->sender_id = Auth::id();
            $redeemCode->receiver_id = null;
            $redeemCode->currency_id = $checkAmountValidate['currency_id'];
            $redeemCode->percentage = $checkAmountValidate['percentage'];
            $redeemCode->charge_percentage = $checkAmountValidate['percentage_charge'];
            $redeemCode->charge_fixed = $checkAmountValidate['fixed_charge'];
            $redeemCode->charge = $checkAmountValidate['charge'];
            $redeemCode->amount = $checkAmountValidate['amount'];
            $redeemCode->transfer_amount = $checkAmountValidate['transfer_amount'];
            $redeemCode->received_amount = $checkAmountValidate['received_amount'];
            $redeemCode->charge_from = $checkAmountValidate['charge_from'];
            $redeemCode->note = $purifiedData->note ?? null;
            $redeemCode->email = null;
            $redeemCode->status = 0;
            $redeemCode->utr = 'R'.strRandomNum(10);
            $redeemCode->save();

            $data['utr'] = $redeemCode->utr;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function redeemPreview($utr)
    {
        try {
            $user = auth()->user();
            $redeemCode = RedeemCode::where('utr', $utr)->first();
            if (!$redeemCode || $redeemCode->status != 0) { //Check is transaction found and unpaid
                return response()->json($this->withErrors('Transaction already complete or invalid code'));
            }

            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $data['enable_for'] = in_array('exchange', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));

            $data['transactionTypeId'] = config('transactionType.redeem');
            $data['utr'] = $utr;
            $data['currency'] = optional($redeemCode->currency)->code;
            $data['percentage'] = getAmount($redeemCode->percentage);
            $data['percentageCharge'] = getAmount($redeemCode->charge_percentage);
            $data['fixedCharge'] = getAmount($redeemCode->charge_fixed);
            $data['totalCharge'] = getAmount($redeemCode->charge);
            $data['payableAmount'] = getAmount($redeemCode->transfer_amount);
            $data['receiverWillReceive'] = getAmount($redeemCode->received_amount);
            $data['chargeDeductFrom'] = $redeemCode->charge_from == 1 ? 'Receiver' : 'Sender';
            $data['note'] = $redeemCode->note;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function redeemPreviewConfirm(Request $request)
    {
        try {
            $user = Auth::user();
            $redeemCode = RedeemCode::where('utr', $request->utr)->first();

            if (!$redeemCode || $redeemCode->status != 0) { //Check is transaction found and unpaid
                return response()->json($this->withErrors('Transaction already complete or invalid code'));
            }

            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

            if (in_array('redeem', $enable_for)) {
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

            $checkAmountValidate = $this->checkAmountValidate($redeemCode->amount, $redeemCode->currency_id, config('transactionType.redeem'), $redeemCode->charge_from);//4 = redeem
            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            DB::beginTransaction();
            $sender_wallet = updateWallet($redeemCode->sender_id, $redeemCode->currency_id, $redeemCode->transfer_amount, 0);
            $remark = 'Balance debited from redeem code';
            BasicService::makeTransaction($redeemCode->sender, $redeemCode->currency_id, $redeemCode->transfer_amount,
                $redeemCode->charge_from == 1 ? 0 : $redeemCode->charge,
                '-', $redeemCode->utr, $remark, $redeemCode->id, RedeemCode::class);

            $redeemCode->status = 1;
            $redeemCode->save();
            DB::commit();

            $receivedUser = $user;
            $params = [
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];

            $action = [
                "link" => route('user.redeem.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.redeem.index');
            $this->sendMailSms($receivedUser, 'REDEEM_CODE_GENERATE', $params);
            $this->userPushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $firebaseAction);

            return response()->json($this->withSuccess("Your redeem code has been generated, your remaining amount of money $sender_wallet"));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function redeemList()
    {
        try {
            $userId = Auth::id();
            $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
            $data['status'] = [
                'Unused' => 1,
                'Used' => 2,
                'Pending' => 0,
            ];

            $data['redeemCodes'] = RedeemCode::with(['sender', 'receiver', 'currency'])
                ->where(fn($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))
                ->filter(request()->all())
                ->latest()
                ->paginate(20)
                ->through(fn($redeem) => $redeem->transformData());

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


    public function redeemInsert(Request $request)
    {
        try {
            $user = Auth::user();
            $purifiedData = Purify::clean($request->all());
            $validationRules = [
                'redeemCode' => 'required|uuid',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
            }
            $purifiedData = (object)$purifiedData;
            $utr = $purifiedData->redeemCode;
            $redeemCode = RedeemCode::where('utr', $utr)->first();

            if (!$redeemCode) { //Check is transaction found
                return response()->json($this->withErrors('Your redeem code is invalid'));
            } elseif ($redeemCode->sender_id == $user->id) { // Check is transaction try to used by own
                return response()->json($this->withErrors('Not allowed to self generated code'));
            } elseif ($redeemCode->status != 1) { // Check is transaction unused
                return response()->json($this->withErrors('Your redeem code is already used'));
            }

            DB::beginTransaction();
            try {
                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($user->id, $redeemCode->currency_id, $redeemCode->received_amount, 1);

                $remark = 'Balance credited from redeem code';
                BasicService::makeTransaction($user, $redeemCode->currency_id, $redeemCode->received_amount,
                    $redeemCode->charge_from == 1 ? $redeemCode->charge : 0,
                    '+', $redeemCode->utr, $remark, $redeemCode->id, RedeemCode::class);

                $redeemCode->receiver_id = $user->id;
                $redeemCode->email = $user->email;
                $redeemCode->status = 2;// 1 = success, 0 = pending, 2 = used
                $redeemCode->save();

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }


            $receivedUserUsedBy = $user;
            $params = [
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];
            $action = [
                "link" => route('user.redeem.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.redeem.index');
            $this->sendMailSms($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params);
            $this->userPushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $action);
            $this->userFirebasePushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $firebaseAction);

            $receivedUserSender = optional($redeemCode->sender)->name;
            $params = [
                'receiver' => optional($redeemCode->receiver)->name,
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];

            $this->sendMailSms($receivedUserSender, 'REDEEM_CODE_SENDER', $params);
            $this->userPushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $action);
            $this->userFirebasePushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $firebaseAction);

            return response()->json($this->withSuccess('Redeem code submitted successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }
}
