<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\PayoutTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class PayoutController extends Controller
{
    use PayoutTrait ;

    public function payoutRequest(Request $request)
    {
        try {
            $amount = $request->amount;
            $supportedCurrency = $request->supported_currency;
            $payoutMethodId = $request->payout_method_id;

            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'supported_currency' => 'required',
                'payout_method_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($this->withErrors(collect($validator->errors())->collapse()));
            }

            $validateData = $this->validationCheck($amount, $supportedCurrency, $payoutMethodId);
            if (!$validateData['status']) {
                return response()->json($this->withErrors($validateData['message']));
            }

            $payout = $this->createPayout($validateData);

            $data['trx_id'] = $payout->trx_id;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payoutConfirmPreview($utr)
    {
        try {
            $user = Auth::user();
            $payout = Payout::where('trx_id', $utr)->where('user_id',$user->id)->first();
            if (!$payout) {
                return response()->json($this->withErrors('Record not found'));
            }
            $payoutMethod = PayoutMethod::query()->find($payout->payout_method_id);

            $data['dynamicForm'] = $payoutMethod->inputForm;
            $data['bankName'] = $payoutMethod->banks ?? [];
            $data['supportedCurrency'] = (!empty($payoutMethod->supported_currency)) ? $payoutMethod->supported_currency : null;
            $data['isAutomatic'] = $payoutMethod->is_automatic;
            $data['wallet'] = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $data['payout'] = $payout;
            $data['payout']['method'] = $payout->method?->name ?? null;
            $data['payout']['currencyCode'] = $payout->payout_currency_code ?? null;
            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $data['enable_for'] = in_array('payout', is_null($twoFactorSetting->enable_for)
                ? [] : json_decode($twoFactorSetting->enable_for, true));

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payoutConfirmSubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            $trx_id = request('trx_id');
            $user = Auth::user();
            $payout = Payout::with('method')->where('trx_id', $trx_id)->where('user_id',$user->id)->first();
            if (!$payout) {
                return response()->json($this->withErrors('Record not found'));
            }
            $payoutMethod = PayoutMethod::find($payout->payout_method_id);
            if (!$payoutMethod) {
                return response()->json($this->withErrors('Method not found'));
            }

            $validator = Validator::make($request->all(), $this->getValidationRules($payoutMethod, $request));
            if ($validator->fails()) {
                return response()->json($this->withErrors(collect($validator->errors())->collapse()));
            }

            $securityPin = $request->security_pin;
            $pinValidation = $this->validateSecurityPin($user, $securityPin);
            if (isset($pinValidation['error'])) {
                return response()->json($this->withErrors($pinValidation['error']));
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
            if (!$validateData['status']) {
                return response()->json($this->withErrors($validateData['message']));
            }

            $reqField = $this->getRequestFields($payoutMethod, $request);
            $this->addRequestField($reqField, 'amount', 'Amount', getAmount($payout->amount));
            $this->addRequestField($reqField, 'currency', 'Currency', $payout->payout_currency_code);
            if ($payoutMethod->code == "paypal") {
                $this->addRequestField($reqField, 'recipient_type', 'Recipient Type', $request->recipient_type);
            }

            $payout->information = $reqField;
            $payout->save();
            updateWallet($payout->user_id, $payout->currency_id, $payout->net_amount, 0);

            $result = $this->processPayout($payout);
            $message = 'Payout generated successfully';
            if ($result['success']) {
                $message = $result['message'];
            }
            if ($payout->status == 1){
                $this->userNotify($user, $payout);
            }

            DB::commit();
            return response()->json($this->withSuccess($message));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payoutConfirmSubmitFlutter(Request $request)
    {
        DB::beginTransaction();
        try {
            $trx_id = request('trx_id');
            $user = Auth::user();
            $payout = Payout::with('method')->where('trx_id', $trx_id)->where('user_id',$user->id)->first();
            if (!$payout) {
                return response()->json($this->withErrors('Record not found'));
            }
            $payoutMethod = PayoutMethod::find($payout->payout_method_id);
            if ($payoutMethod->code != 'flutterwave') {
                return response()->json($this->withErrors('Method not allowed'));
            }

            $purifiedData = Purify::clean($request->all());
            if (empty($purifiedData['transfer_name'])) {
                return response()->json($this->withErrors('Transfer field is required'));
            }
            $validation = config('banks.' . $purifiedData['transfer_name'] . '.validation');

            $rules = [];
            if ($validation != null) {
                foreach ($validation as $key => $cus) {
                    $rules[$key] = 'required';
                }
            }
            if (in_array($request->transfer_name, ['NGN BANK', 'NGN DOM', 'GHS BANK', 'KES BANK', 'ZAR BANK'])) {
                $rules['bank'] = 'required';
            }
            $validate = Validator::make($request->all(), $rules);
            if ($validate->fails()) {
                return response()->json($this->withErrors(collect($validate->errors())->collapse()));
            }

            $securityPin = $request->security_pin;
            $pinValidation = $this->validateSecurityPin($user, $securityPin);
            if (isset($pinValidation['error'])) {
                return response()->json($this->withErrors($pinValidation['error']));
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
            if (!$validateData['status']) {
                return response()->json($this->withErrors($validateData['message']));
            }

            $collection = collect($purifiedData);
            $reqField = [];
            $metaField = [];
            $inputForm = config('banks.' . $purifiedData['transfer_name'] . '.input_form');

            if ($inputForm != null) {
                foreach ($collection as $k => $v) {
                    foreach ($inputForm as $inKey => $inVal) {
                        if ($k != $inKey) {
                            continue;
                        } else {
                            if ($inVal == 'meta') {
                                $metaField[$inKey] = [
                                    'field_name' => $k,
                                    'field_value' => $v,
                                    'type' => 'text',
                                ];
                            } else {
                                $reqField[$inKey] = [
                                    'field_name' => $k,
                                    'field_value' => $v,
                                    'type' => 'text',
                                ];
                            }
                        }
                    }
                }
                $transferName = $request->transfer_name;

                if (in_array($transferName, ['NGN BANK', 'NGN DOM', 'GHS BANK', 'KES BANK', 'ZAR BANK'])) {
                    $accountBank = 'Account Bank';
                } elseif ($transferName == 'XAF/XOF MOMO') {
                    $accountBank = 'MTN';
                } elseif (in_array($transferName, ['FRANCOPGONE', 'mPesa', 'Rwanda Momo', 'Uganda Momo', 'Zambia Momo'])) {
                    $accountBank = 'MPS';
                } elseif (in_array($transferName, ['Barter', 'flutterwave'])) {
                    $accountBank = 'barter';
                }

                $reqField['account_bank'] = [
                    'field_name' => $accountBank ?? '',
                    'field_value' => $transferName ?? '',
                    'type' => 'text',
                ];
                $reqField['amount'] = [
                    'field_name' => 'Amount',
                    'field_value' => getAmount($payout->amount),
                    'type' => 'text',
                ];
                $reqField['currency_code'] = [
                    'field_name' => 'Currency',
                    'field_value' => $payout->payout_currency_code,
                    'type' => 'text',
                ];
                $payout->information = $reqField;
                $payout->meta_field = $metaField;
            } else {
                $payout->information = null;
                $payout->meta_field = null;
            }
            $payout->save();
            updateWallet($payout->user_id, $payout->currency_id, $payout->net_amount, 0);

            $result = $this->processPayout($payout);
            $message = 'Payout generated successfully';
            if ($result['success']) {
                $message = $result['message'];
            }
            if ($payout->status == 1){
                $this->userNotify($user, $payout);
            }

            DB::commit();
            return response()->json($this->withSuccess($message));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withErrors($e->getMessage()));
        }

    }

    public function payoutConfirmSubmitPaystack(Request $request)
    {
        DB::beginTransaction();
        try {
            $trx_id = $request->trx_id;
            $user = auth()->user();
            $basicControl = basicControl();
            $payout = Payout::where('trx_id', $trx_id)->where('user_id',$user->id)->first();
            if (!$payout) {
                return response()->json($this->withErrors('Record not found'));
            }
            $payoutMethod = PayoutMethod::find($payout->payout_method_id);
            if ($payoutMethod->code != 'paystack') {
                return response()->json($this->withErrors('Method not allowed'));
            }

            $validate = Validator::make($request->all(), $this->getValidationRules($payoutMethod, $request));
            if ($validate->fails()) {
                return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
            }
            if (empty($request->bank)) {
                return response()->json($this->withErrors('Bank field is required'));
            }

            $securityPin = $request->security_pin;
            $pinValidation = $this->validateSecurityPin($user, $securityPin);
            if (isset($pinValidation['error'])) {
                return response()->json($this->withErrors($pinValidation['error']));
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
            if (!$validateData['status']) {
                return response()->json($this->withErrors($validateData['message']));
            }

            $reqField = $this->getRequestFields($payoutMethod, $request);
            $this->addRequestField($reqField, 'bank_code', 'Bank Code', $request->bank);
            $this->addRequestField($reqField, 'amount', 'Amount', getAmount($payout->amount));
            $this->addRequestField($reqField, 'currency', 'Currency', $payout->payout_currency_code);
            if (!empty($request->type)) {
                $this->addRequestField($reqField, 'type', 'Type', $request->type);
            }

            $payout->information = $reqField;
            $payout->save();
            updateWallet($payout->user_id, $payout->currency_id, $payout->net_amount, 0);

            $result = $this->processPayout($payout);
            $message = 'Payout generated successfully';
            if ($result['success']) {
                $message = $result['message'];
            }
            if ($payout->status == 1){
                $this->userNotify($user, $payout);
            }

            DB::commit();
            return response()->json($this->withSuccess($message));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


    public function payoutGetBankList(Request $request)
    {
        try {
            $currencyCode = $request->currencyCode;
            $methodObj = 'App\\Services\\Payout\\paystack\\Card';
            $data = $methodObj::getBank($currencyCode);
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payoutGetBankFrom(Request $request)
    {
        try {
            $bankName = $request->bankName;
            $bankArr = config('banks.' . $bankName);
            $value['bank'] = null;
            if ($bankArr['api'] != null) {

                $methodObj = 'App\\Services\\Payout\\flutterwave\\Card';
                $data = $methodObj::getBank($bankArr['api']);
                $value['bank'] = $data;
            }
            $value['input_form'] = $bankArr['input_form'];
            return response()->json($this->withSuccess($value));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payout()
    {
        try {
            $data['gateways'] = PayoutMethod::whereIs_active(1)->get()->map(function ($query) {
                $method['id'] = $query->id;
                $method['name'] = $query->name;
                $method['image'] = getFile($query->driver, $query->logo);
                $method['description'] = $query->description;

                $method['supported_currency'] = $query->supported_currency;
                $method['payout_currencies'] = $query->payout_currencies;
                $method['inputForm'] = $query->inputForm;
                $method['isAutomatic'] = $query->is_automatic;
                $method['currency_type'] = $query->currency_type;
                return $method;
            });
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function payoutList(Request $request)
    {
        try {
            $userId = Auth::id();
            $filters = $request->all();

            $data['payouts'] = Payout::query()
                ->where('user_id', $userId)
                ->search($filters, $userId)
                ->latest()->paginate(15)
                ->through(fn($payout) => $this->transformPayout($payout));

            $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    private function transformPayout($payout): array
    {
        return [
            'amount' => getAmount($payout->amount),
            'charge' => getAmount($payout->charge),
            'currency' => $payout->payout_currency_code,
            'transactionId' => $payout->trx_id,
            'gateway' => $payout->method?->name,
            'status' => match ($payout->status) {
                0 => 'Pending',
                1 => 'Generated',
                2 => 'Payout Done',
                3 => 'Canceled',
                6 => 'Failed',
                default => 'Unknown'
            },
            'createdTime' => $payout->created_at,
        ];
    }

}
