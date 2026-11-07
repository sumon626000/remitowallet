<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Traits\ApiValidation;
use App\Traits\ChargeLimitTrait;
use App\Traits\Notify;
use App\Traits\PaymentTrait;
use App\Traits\Upload;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class FundController extends Controller
{
    use ApiValidation, ChargeLimitTrait, Notify, Upload, PaymentTrait;

    public function fundGatewayCurrencyList()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name', 'is_active')->where('is_active', 1)->get();
        $data['methods'] = Gateway::query()
            ->orderBy('sort_by', 'ASC')
            ->where('status', 1)
            ->get()
            ->map(function ($query) {
                $query->imagePath = getFile($query->driver, $query->image);
                return $query;
            });

        return response()->json($this->withSuccess($data));
    }


    public function fundList(Request $request)
    {
        try {
            $data['status'] =['0=pending, 1=success, 2=request, 3=rejected'];

            $userId = Auth::id();

            $funds = Deposit::query()
                ->with([
                    'user:id,firstname,lastname,image,image_driver',
                    'currency:id,code,symbol',
                    'gateway:id,name,image,driver'
                ])
                ->where('user_id', $userId)
                ->search($request->all())
                ->latest()
                ->paginate(20);

            $data['funds'] = $funds->map(function ($fund) {
                return [
                    'id' => $fund->id,
                    'trx_id' => $fund->trx_id,
                    'amount' => $fund->amount,
                    'charge' => $fund->charge,
                    'payable_amount' => $fund->payable_amount,
                    'payable_amount_in_base_currency' => $fund->payable_amount_in_base_currency,
                    'status' => $fund->status,
                    'currency' => [
                        'code' => $fund->currency?->code ?? null,
                        'symbol' => $fund->currency?->symbol ?? null,
                    ],
                    'user' => $fund->user ? [
                        'name' => $fund->user->name,
                        'image' => $fund->user->getImage(),
                    ] : null,
                    'gateway' => [
                        'name' => $fund->gateway?->name ?? null,
                        'image' => $fund->gateway?->gatewayImg(),
                    ],
                    'created_at' => $fund->created_at,
                ];
            });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


    public function fundAdd(Request $request)
    {
        try {
            $purifiedData = $request->all();
            $validationRules = [
                'amount' => 'required|numeric|min:1|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0',
                'methodId' => 'required|integer|min:1|not_in:0',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return response()->json($this->withErrors(collect($validate->errors())->collapse()));
            }
            $purifiedData = (object)$purifiedData;

            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $methodId = $purifiedData->methodId;

            $cardOrderId = null;
            if ($request->has('orderCardId') && $request->orderCardId != null) {
                $cardOrder = $this->validateCardOrder($request->orderCardId, $purifiedData);
                if (!$cardOrder['status']) {
                    return response()->json($this->withErrors($cardOrder['message']));
                }
                $cardOrderId = $cardOrder['id'] ?? null;
            }

            $checkAmountValidate = $this->validatePayment($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit
            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            $deposit = $this->createDeposit($checkAmountValidate, null, null, $cardOrderId);

            return response()->json($this->withSuccess($deposit->trx_id));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }

    }

    public function automationPayment(Request $request)
    {
        $deposit = Deposit::select(['id', 'status', 'trx_id', 'created_at', 'updated_at'])
            ->where('status', 0)->where('trx_id', $request->trx_id)->latest()->first();

        if (!$deposit) {
            return response()->json($this->withErrors('Record not found'));
        }

        if ($deposit) {
            $val['url'] = route('paymentView', $deposit->trx_id);
            return response()->json($this->withSuccess($val));
        }
    }

    public function paymentView($utr)
    {
        $deposit = Deposit::latest()->where('trx_id', $utr)->first();
        try {
            if ($deposit) {
                $getwayObj = 'App\\Services\\Gateway\\' . $deposit->gateway->code . '\\Payment';
                $data = $getwayObj::prepareData($deposit, $deposit->gateway);
                $data = json_decode($data);
                if (isset($data->error)) {
                    return response()->json($this->withErrors($data->message));
                }

                if (isset($data->redirect)) {
                    return redirect($data->redirect_url);
                }

                if ($data->view) {
                    $parts = explode(".", $data->view);
                    $desiredValue = end($parts);
                    $newView = 'mobile-payment.' . $desiredValue;
                    return view($newView, compact('data', 'deposit'));
                }
                abort(404);
            }
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function cardPayment(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'utr' => 'required',
                'card_number' => 'required',
                'card_name' => 'required',
                'expiry_month' => 'required',
                'expiry_year' => 'required',
                'card_cvc' => 'required',
            ]);

        if ($validateUser->fails()) {
            return response()->json($this->withErrors(collect($validateUser->errors())->collapse()));
        }

        try {
            $deposit = Deposit::latest()->where('trx_id', $request->utr)->first();

            if (in_array($deposit->gateway->code, ['authorizenet', 'securionpay'])) {
                $getwayObj = 'App\\Services\\Gateway\\' . $deposit->gateway->code . '\\Payment';
                $data = $getwayObj::mobileIpn($request, $deposit->gateway, $deposit);
                if ($data == 'success') {
                    return response()->json($this->withSuccess('Payment has been complete'));
                } else {
                    return response()->json($this->withErrors('Unsuccessful transaction.'));
                }
            }
            return response()->json($this->withErrors('Unsuccessful transaction.'));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Unsuccessful transaction.'));
        }
    }

    public function paymentDone(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'utr' => 'required',
            ]);

        if ($validateUser->fails()) {
            return response()->json($this->withErrors(collect($validateUser->errors())->collapse()));
        }

        try {
            $deposit = Deposit::query()
                ->where('user_id', \auth()->id())
                ->where('status', 0)
                ->where('trx_id', $request->utr)
                ->latest()
                ->first();

            if (!$deposit) {
                return response()->json($this->withErrors('Record not found'));
            }
            if ($deposit) {
                BasicService::preparePaymentUpgradation($deposit);
                return response()->json($this->withSuccess('Payment has been completed'));
            }

            return response()->json($this->withErrors('Unsuccessful transaction.'));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Unsuccessful transaction.'));
        }
    }

    public function fromSubmit(Request $request, $trx_id)
    {
        set_time_limit(300);
        $data = Deposit::where('trx_id', $trx_id)->orderBy('id', 'DESC')->with(['gateway', 'user'])->first();
        if (is_null($data)) {
            return response()->json($this->withErrors('Invalid Request'));
        }

        $params = optional($data->gateway)->parameters;
        $reqData = $request->except('_token', '_method');
        $rules = [];

        foreach ($params ?? [] as $key => $cus) {
            if (is_object($cus)) {
                $validationRule = ($cus->validation == 'required') ? 'required' : 'nullable';
                $rules[$key] = [$validationRule];
                if ($cus->type === 'file') {
                    $rules[$key][] = 'image';
                    $rules[$key][] = 'mimes:jpeg,jpg,png';
                    $rules[$key][] = 'max:5048';
                } elseif ($cus->type === 'text') {
                    $rules[$key][] = 'max:191';
                } elseif ($cus->type === 'number') {
                    $rules[$key][] = 'integer';
                } elseif ($cus->type === 'textarea') {
                    $rules[$key][] = 'min:3';
                    $rules[$key][] = 'max:300';
                }
            }
        }

        $validator = Validator::make($reqData, $rules);
        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()));
        }

        $reqField = [];
        if ($params != null) {
            foreach ($request->except('_token', '_method', 'type') as $k => $v) {
                foreach ($params as $inKey => $inVal) {
                    if ($k == $inKey) {
                        if ($inVal->type == 'file' && $request->hasFile($inKey)) {
                            try {
                                $file = $this->fileUpload($request[$inKey], config('filelocation.deposit.path'), null, null, 'webp', 80);
                                $reqField[$inKey] = [
                                    'field_name' => $inVal->field_name,
                                    'field_value' => $file['path'],
                                    'field_driver' => $file['driver'],
                                    'validation' => $inVal->validation,
                                    'type' => $inVal->type,
                                ];
                            } catch (\Exception $exp) {
                                return response()->json($this->withErrors(" Could not upload your {$inKey} "));
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
        }

        $data->update([
            'information' => $reqField,
            'created_at' => Carbon::now(),
            'status' => 2,
        ]);

        $msg = [
            'username' => optional($data->user)->username,
            'amount' => currencyPosition($data->amount),
            'gateway' => optional($data->gateway)->name
        ];
        $action = [
            "name" => optional($data->user)->firstname . ' ' . optional($data->user)->lastname,
            "image" => getFile(optional($data->user)->image_driver, optional($data->user)->image),
            "link" => route('admin.user.payment', $data->user_id) ?? '#',
            "icon" => "fa fa-money-bill-alt text-white"
        ];

        $this->adminPushNotification('PAYMENT_REQUEST', $msg, $action);
        $this->adminFirebasePushNotification('PAYMENT_REQUEST', $msg, $action);
        $this->adminMail('PAYMENT_REQUEST', $msg);

        return response()->json($this->withSuccess('You request has been taken.'));
    }

}
