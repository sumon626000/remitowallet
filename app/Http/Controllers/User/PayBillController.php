<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BillMethod;
use App\Models\BillPay;
use App\Models\BillService;
use App\Models\Currency;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\Notify;
use App\Traits\Upload;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;


class PayBillController extends Controller
{
    use Notify, Upload;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function payBill()
    {
        $currencies = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
        $data['billMethod'] = BillMethod::with(['billServices'])->where('is_active', 1)->firstOrFail();

        $countryLists = [];
        $countries = BillService::select(['id', 'bill_method_id', 'status', 'country'])
            ->where('bill_method_id', $data['billMethod']->id)
            ->where('status', 1)->groupBy('country')->get()->map(function ($query) use ($countryLists) {
                foreach (config('country') as $county) {
                    if ($county['code'] == $query->country) {
                        $countryLists[$query->country] = [
                            'name' => $county['name'],
                            'currency' => $county['iso_code'],
                        ];
                    }
                }
                return $countryLists;
            });

        $data['countries'] = collect($countries)->collapse();
        return view('user.bill_pay.request', $data, compact('countryLists', 'currencies'));
    }

    public function payBillSubmit(Request $request)
    {
        $service = BillService::query()->with('method')->where('id', $request->service)->first();
        if (!$service) {
            return back()->with('error', 'Service not found');
        }

        $purifiedData = $request->all();
        $validationRules = [
            'category' => 'required',
            'country' => 'required',
            'service' => 'required',
            'amount' => 'required',
            'from_wallet' => 'required',
        ];

        if ($service->label_name != null) {
            foreach ($service->label_name as $key => $cus) {
                $validationRules[$cus] = ['required'];
                array_push($validationRules[$cus], 'max:250');
                $input[$cus] = [
                    $cus => $request->$cus
                ];
            }
        }
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $amount = ($service->amount > 0) ? $service->amount : $purifiedData['amount'];
        if ($service->method->code == 'reloadly' && $service->amount == -1) {
            $amount = getReloadlyAmount($service, $purifiedData['amount']);
        }

        if ($service->min_amount > $amount) {
            session()->flash('error', 'Amount must be greater than ' . $service->min_amount . ' ' . $service->currency);
            return back()->withInput();
        }
        if ($service->max_amount > 0 && $service->max_amount < $amount) {
            session()->flash('error', 'Amount must be smaller than ' . $service->max_amount . ' ' . $service->currency);
            return back()->withInput();
        }

        $value = $this->convertRate($service->currency, $service->method, $purifiedData['from_wallet']);
        if (!$value) {
            return back()->withInput()->with('error', 'Something went wrong');
        }

        if ($service->label_name != null) {
            foreach ($service->label_name as $key => $cus) {
                $customerInput[$cus] = [
                    $cus => $request->$cus
                ];
            }
        }

        $charge = $this->calculateCharge($amount, $service->fixed_charge, $service->percent_charge);
        $billPay = new BillPay();
        $billPay->method_id = $service->bill_method_id;
        $billPay->user_id = auth()->id();
        $billPay->service_id = $service->id;
        $billPay->from_wallet = $purifiedData['from_wallet'];
        $billPay->customer = $customerInput;
        $billPay->type = $service->type;
        $billPay->category_name = $purifiedData['category'];
        $billPay->country_name = $purifiedData['country'];
        $billPay->amount = $amount;
        $billPay->charge = $charge;
        $billPay->payable_amount = $amount + $charge;
        $billPay->currency = $service->currency;
        $billPay->exchange_rate = $value['rate'];
        $billPay->utr = (string)Str::uuid();
        $billPay->status = 0;
        $billPay->amount_id = ($service->amount == -1) ? $purifiedData['amount'] : null;

        $billPay->save();

        return redirect(route('user.pay.bill.confirm', $billPay->utr))->with('success', 'Bill initiated successfully');
    }

    public function payBillConfirm(Request $request, $utr)
    {
        $user = Auth::user();
        $data['billPay'] = BillPay::with(['service', 'walletCurrency'])
            ->where('utr', $utr)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->method() == 'GET') {
            return view('user.bill_pay.confirm', $data, compact('enable_for'));
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validationRules = [];
            if (in_array('bill_payment', $enable_for)) {
                $validationRules['security_pin'] = 'required|integer|digits:5';
            }

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }

            if (in_array('bill_payment', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
            }

            $value = $this->convertRate($data['billPay']->currency, $data['billPay']->method, $data['billPay']->from_wallet);
            if (!$value) {
                return back()->withInput()->with('error', 'Something went wrong');
            }

            $payableAmount = ($data['billPay']->payable_amount / $value['rate']) + ($data['billPay']->charge / $value['rate']);
            $fromWallet = Wallet::where('is_active', 1)->where('user_id', $data['billPay']->user_id)->where('currency_id', $data['billPay']->from_wallet)->firstOrFail();
            if ($payableAmount > $fromWallet->balance) {
                return back()->withInput()->with('error', 'Please add fund ' . $data['billPay']->walletCurrency->name . ' wallet to payment bill');
            }

            updateWallet($data['billPay']->user_id, $data['billPay']->from_wallet, $payableAmount, 0);

            $data['billPay']->pay_amount_in_base = $payableAmount;
            $data['billPay']->base_currency_id = $fromWallet->currency_id;
            $data['billPay']->status = 2;
            $data['billPay']->save();

            BasicService::makeTransaction($user, $data['billPay']->base_currency_id, $data['billPay']->amount, $data['billPay']->charge,
                '-', $data['billPay']->utr, 'Debited balance for Bill Pay', $data['billPay']->id, BillPay::class);


            $billPay = $data['billPay'];
            $method = $billPay->method;

            $methodObj = 'App\\Services\\Bill\\' . $method->code . '\\Card';

            if ($method->code == 'reloadly' && $billPay->category_name == 'AIRTIME') {
                $response = $methodObj::payAirtimeBill($billPay, $method);
            } else {
                $response = $methodObj::payBill($billPay, $method);
            }

            if ($response['status'] == 'success') {
                $billPay->status = 3;
                $billPay->save();

                $params = [
                    'amount' => $billPay->amount,
                    'currency' => $billPay->currency,
                    'transaction' => $billPay->utr,
                ];
                $action = [
                    "link" => "#",
                    "icon" => "fa fa-money-bill-alt text-white"
                ];

                $this->sendMailSms($billPay->user, 'BILL_PAY', $params);
                $this->userPushNotification($billPay->user, 'BILL_PAY', $params, $action);
                $this->userFirebasePushNotification($billPay->user, 'BILL_PAY', $params);

                return redirect()->route('user.pay.bill')->with('success', 'Bill has been paid successfully');
            } elseif ($response['status'] == 'processing') {
                $billPay->status = 5;
                $billPay->reference_id = $response['data'];
                $billPay->save();
                return redirect()->route('user.pay.bill')->with('success', 'Bill has been processing');
            } else {
                $billPay->last_api_error = $response['data'];
                $billPay->save();
                return redirect()->route('user.pay.bill')->with('error', 'Please wait some time. administration will contact you');
            }
        }
    }

    public function convertRate($inputCurrency, $method, $fromWallet)
    {
        $data = array();
        $currency = Currency::find($fromWallet);
        if ($currency) {
            $data['currency_id'] = $currency->id;
            if ($method) {
                foreach ($method->convert_rate as $key => $rate) {
                    if ($key == $inputCurrency) {
                        $rate = $rate;
                        break;
                    }
                }
            }
        }
        if (isset($rate)) {
            if ($currency->exchange_rate != 0) {
                $data['rate'] = $rate / $currency->exchange_rate;
            } else {
                $data['rate'] = $rate;
            }
        } else {
            if ($currency->exchange_rate != 0) {
                $data['rate'] = 1 / $currency->exchange_rate;
            } else {
                $data['rate'] = 1;
            }
        }

        return $data;
    }

    function calculateCharge($amount, $fixed_charge, $percent_charge)
    {
        $fromPercent = $amount * $percent_charge / 100;
        $charge = $fromPercent + $fixed_charge;
        return $charge;
    }

    public function fetchServices(Request $request)
    {
        $billMethod = BillMethod::select(['id', 'code', 'is_active'])->where('is_active', 1)->first();
        if ($billMethod) {
            $category = $request->category;
            $code = $request->code;

            if ($billMethod->code == 'reloadly') {
                switch ($category) {
                    case "airtime":
                        $category = 'AIRTIME';
                        break;
                    case "power":
                        $category = 'ELECTRICITY_BILL_PAYMENT';
                        break;
                    case "cables":
                        $category = 'TV_BILL_PAYMENT';
                        break;
                    case "internet":
                        $category = 'INTERNET_BILL_PAYMENT';
                        break;
                    default:
                        $category = 'WATER_BILL_PAYMENT';
                        break;
                }
            }

            $services = BillService::where('country', $code)
                ->where('service', $category)->where('status', 1)->get();

            return response()->json([
                'status' => 'success',
                'data' => $services
            ]);
        }
    }

    public function fetchAmount(Request $request)
    {
        $services = BillService::where('status', 1)->find($request->serviceId);
        return response()->json([
            'status' => 'success',
            'data' => $services->extra_response
        ]);
    }


    public function payBillList(Request $request)
    {
        $search = $request->all();
        $created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

        $data['bills'] = BillPay::where('user_id', auth()->id())
            ->when(isset($search['category']), function ($query) use ($search) {
                return $query->where('category_name', 'LIKE', "%{$search['category']}%");
            })
            ->when(isset($search['type']), function ($query) use ($search) {
                return $query->where('type', 'LIKE', "%{$search['type']}%");
            })
            ->when(isset($search['status']), function ($query) use ($search) {
                if ($search['status'] == 'generate') {
                    return $query->where('status', 0);
                } elseif ($search['status'] == 'pending') {
                    return $query->where('status', 1);
                } elseif ($search['status'] == 'payment_completed') {
                    return $query->where('status', 2);
                } elseif ($search['status'] == 'bill_completed') {
                    return $query->where('status', 3);
                } elseif ($search['status'] == 'bill_return') {
                    return $query->where('status', 4);
                }
            })
            ->when($created_date == 1, function ($query) use ($search) {
                return $query->whereDate("created_at", $search['created_at']);
            })
            ->latest()->paginate(20);
        return view('user.bill_pay.index', $data);
    }
}
