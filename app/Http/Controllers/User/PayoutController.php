<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\Notify;
use App\Traits\PayoutTrait;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class PayoutController extends Controller
{
    use PayoutTrait;

    private string $ppMsg;
    public string $payoutIndexRoute;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            $this->payoutIndexRoute = userRoute('payout.index');
            return $next($request);
        });
        $this->ppMsg= 'Payout processing';
    }

    public function index(Request $request)
    {
        $payouts = Payout::with(['user','method'])
            ->where(['user_id' => Auth::id()])
            ->orderBy('id', 'desc')
            ->searchPayouts($request)
            ->paginate(20);
        return view('user.payout.index', compact('payouts'));
    }

    public function payout()
    {
        $data['basic'] = basicControl();
        $data['payoutMethod'] = PayoutMethod::where('is_active', 1)->get();
        return view('user.payout.request', $data);
    }

    public function payoutSupportedCurrency(Request $request)
    {
        $gateway = PayoutMethod::where('id', $request->gateway)->firstOrFail();
        return $gateway->supported_currency;
    }

    public function checkAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $supportedCurrency = $request->selected_currency;
            $payoutMethodId = $request->selected_payout_method;

            $data = $this->validationCheck($amount, $supportedCurrency, $payoutMethodId);
            return response()->json($data);
        }
        return response()->json(['error' => 'Invalid request'], 400);
    }

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
                return back()->withErrors($validator)->withInput();
            }

            $validateData = $this->validationCheck($amount, $supportedCurrency, $payoutMethodId);
            if (!$validateData['status']) {
                return back()->withInput()->with('error', $validateData['message']);
            }
            $payout = $this->createPayout($validateData);

            return redirect(route('user.payout.confirm', $payout->trx_id))->with('success', 'Payout initiated successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmPayout(Request $request,$trx_id)
    {
        $user = Auth::user();
        $basic = basicControl();
        $payout = Payout::with('method')->where('trx_id', $trx_id)
            ->where('user_id', $user->id)
            ->first();
        if (!$payout) {
            return back()->with('error', 'Record not found');
        }
        $payoutMethod = PayoutMethod::find($payout->payout_method_id);
        if (!$payoutMethod) {
            return back()->with('error', 'Method not found');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
        $data = [
            'basic' => $basic,
            'payout' => $payout,
            'payoutMethod' => $payoutMethod,
            'enable_for' => $enable_for,
        ];

        if ($request->isMethod('get')) {
            if ($payoutMethod->code == 'flutterwave') {
                return view('user.payout.gateway.' . $payoutMethod->code, $data);
            } elseif ($payoutMethod->code == 'paystack') {
                return view('user.payout.gateway.' . $payoutMethod->code, $data);
            }
            return view('user.payout.confirm', $data);

        }
        elseif ($request->isMethod('post'))
        {

            DB::beginTransaction();
            try {
                $validator = Validator::make($request->all(), $this->getValidationRules($payoutMethod, $request));
                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $securityPin = $request->security_pin;
                $pinValidation = $this->validateSecurityPin($user, $securityPin);
                if (isset($pinValidation['error'])) {
                    return back()->with('error', $pinValidation['error']);
                }

                $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
                $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
                if (!$validateData['status']) {
                    return back()->with('error', $validateData['message']);
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
                return redirect($this->payoutIndexRoute)->with('success',$message);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', $e->getMessage());
            }
        }

    }

    public function flutterwavePayout(Request $request, $trx_id)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $payout = Payout::with('method')->where('trx_id', $trx_id)
                ->where('user_id', $user->id)
                ->first();
            if (!$payout) {
                return back()->with('error', 'Record not found');
            }
            $payoutMethod = PayoutMethod::find($payout->payout_method_id);
            if ($payoutMethod->code != 'flutterwave') {
                return back()->with('error', 'Method not allowed');
            }

            $purifiedData = Purify::clean($request->all());
            if (empty($purifiedData['transfer_name'])) {
                return back()->with('error', 'Transfer field is required');
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
                return back()->withErrors($validate)->withInput();
            }

            $securityPin = $request->security_pin;
            $pinValidation = $this->validateSecurityPin($user, $securityPin);
            if (isset($pinValidation['error'])) {
                return back()->withInput()->with('error', $pinValidation['error']);
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
            if (!$validateData['status']) {
                return back()->withInput()->with('error', $validateData['message']);
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
                    'field_value' => in_array($transferName, ['NGN BANK', 'NGN DOM', 'GHS BANK', 'KES BANK', 'ZAR BANK'])
                        ? $request->bank
                        : $transferName,
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
            return redirect($this->payoutIndexRoute)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

    }

    public function paystackPayout(Request $request,  $trx_id)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $basic = basicControl();
            $payout = Payout::with('method')->where('trx_id', $trx_id)
                ->where('user_id', $user->id)
                ->first();
            if (!$payout) {
                return back()->with('error', 'Record not found');
            }
            $payoutMethod = PayoutMethod::find($payout->payout_method_id);
            if ($payoutMethod->code != 'paystack') {
                return back()->with('error', 'Method not allowed');
            }

            $validator = Validator::make($request->all(), $this->getValidationRules($payoutMethod, $request));
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            if (empty($request->bank)) {
                return back()->with('error', 'Bank field is required');
            }

            $securityPin = $request->security_pin;
            $pinValidation = $this->validateSecurityPin($user, $securityPin);
            if (isset($pinValidation['error'])) {
                return back()->withInput()->with('error', $pinValidation['error']);
            }

            $wallet = Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $payout->currency_id]);
            $validateData = $this->validationCheck($payout->amount, $payout->payout_currency_code, $payout->payout_method_id);
            if (!$validateData['status']) {
                return back()->withInput()->with('error', $validateData['message']);
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
            return redirect($this->payoutIndexRoute)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getBankList(Request $request)
    {
        $currencyCode = $request->currencyCode;
        $methodObj = 'App\\Services\\Payout\\paystack\\Card';
        $data = $methodObj::getBank($currencyCode);
        return $data;
    }

    public function getBankForm(Request $request)
    {
        $bankName = $request->bankName;
        $bankArr = config('banks.' . $bankName);

        if ($bankArr['api'] != null) {
            $methodObj = 'App\\Services\\Payout\\flutterwave\\Card';
            $data = $methodObj::getBank($bankArr['api']);
            $value['bank'] = $data;
        }
        $value['input_form'] = $bankArr['input_form'];
        return $value;
    }




}
