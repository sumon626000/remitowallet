<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BillMethod;
use App\Models\BillPay;
use App\Models\BillService;
use App\Models\Currency;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class BillController extends Controller
{
	use ApiValidation, Notify;

	public function payBill()
	{
        $categories = [
            ['name' => 'AirTime', 'value' => 'airtime', 'img' => 'airplane.png'],
            ['name' => 'Electricity', 'value' => 'power', 'img' => 'electricity.png'],
            ['name' => 'Internet', 'value' => 'internet', 'img' => 'internet.png'],
            ['name' => 'Toll', 'value' => 'toll', 'img' => 'toll.png'],
            ['name' => 'Cable Tv', 'value' => 'cables', 'img' => 'smart-tv.png'],
            ['name' => 'Data Bundle', 'value' => 'data_bundle', 'img' => 'data-bundle.png'],
        ];
        $data['categories'] = array_map(function ($category) {
            return [
                'name' => $category['name'],
                'value' => $category['value'],
                'img' => asset( "assets/user/img/category/{$category['img']}")
            ];
        }, $categories);


        try {
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();

            $data['billMethod'] = BillMethod::query()
                ->with(['billServices'])
                ->select('id','inputForm')
                ->where('is_active', 1)->firstOrFail();
			if (!$data['billMethod']) {
				return response()->json($this->withErrors('Record not found'));
			}

            $countryLists = [];
            $countries = BillService::select(['id', 'bill_method_id', 'status', 'country'])
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

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function payBillSubmit(Request $request)
	{
        $service = BillService::query()->with('method')->where('id', $request->service)->first();

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
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, $validationRules);
		if ($validator->fails()) {
			return response()->json($this->withErrors(collect($validator->errors())->collapse()[0]));
		}

        if ($service->label_name != null) {
            foreach ($service->label_name as $key => $cus) {
                $customerInput[$cus] = [
                    $cus => $request->$cus
                ];
            }
        }

		try {
			if ($service->amount > 0) {
				$amount = $service->amount;
			} else {
				$amount = $purifiedData['amount'];
			}

			if ($service->min_amount > $amount) {
				return response()->json($this->withErrors('Amount must be greater than minimum amount'));
			}
			if ($service->max_amount > 0 && $service->max_amount < $amount) {
				return response()->json($this->withErrors('Amount must be smaller than maximum amount'));
			}

			$value = $this->convertRate($service->currency, $service->method, $purifiedData['from_wallet']);
			if (!$value) {
				return response()->json($this->withErrors('Something went wrong'));
			}

			$charge = $this->calculateCharge($amount, $service->fixed_charge, $service->percent_charge);
			$billPay = new BillPay();
			$billPay->method_id = $service->bill_method_id;
			$billPay->user_id = auth()->id();
			$billPay->service_id = $service->id;
			$billPay->from_wallet = $purifiedData['from_wallet'];
			$billPay->customer = $customerInput ?? null;
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

			$billPay->save();

			$data['utr'] = $billPay->utr;
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
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

	public function payBillPreview($utr)
	{
		try {
			$user = Auth::user();
			$billPay = BillPay::with(['service', 'walletCurrency'])->where('utr', $utr)->where('user_id', auth()->id())->first();
			if (!$billPay) {
				return response()->json($this->withErrors('Record not found'));
			}
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$data['enable_for'] = in_array('bill_payment', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
			$data['category'] = str_replace('_', ' ', ucfirst($billPay->category_name));
			$data['service'] = optional($billPay->service)->type;
			$data['countryCode'] = $billPay->country_name;
			$data['fromWalletCode'] = optional($billPay->walletCurrency)->code;
			$data['fromWalletName'] = optional($billPay->walletCurrency)->name;
			$data['currencyCode'] = $billPay->currency;
			$data['exchangeRate'] = getAmount($billPay->exchange_rate);
			$data['amount'] = getAmount($billPay->amount);
			$data['charge'] = getAmount($billPay->charge);
			$data['payableAmount'] = getAmount(($billPay->payable_amount / $billPay->exchange_rate) + ($billPay->charge / $billPay->exchange_rate), 2);
			$data['utr'] = $utr;

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function payBillPreviewSubmit(Request $request)
	{
		try {
			$user = Auth::user();
			$data['billPay'] = BillPay::with(['service', 'walletCurrency'])
                ->where('utr', $request->utr)
                ->where('user_id', auth()->id())
                ->first();
			if (!$data['billPay']) {
				return response()->json($this->withErrors('Record not found'));
			}
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

			$purifiedData = Purify::clean($request->all());
            $validationRules = [];
			if (in_array('bill_payment', $enable_for)) {
				$validationRules['security_pin'] = 'required|integer|digits:5';
			}

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
			}

			if (in_array('bill_payment', $enable_for) && !Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return response()->json($this->withErrors('You have entered an incorrect PIN'));
			}

			$value = $this->convertRate($data['billPay']->currency, $data['billPay']->method, $data['billPay']->from_wallet);
			if (!$value) {
				return response()->json($this->withErrors('Something went wrong'));
			}

			$payableAmount = ($data['billPay']->payable_amount / $value['rate']) + ($data['billPay']->charge / $value['rate']);
			$fromWallet = Wallet::where('is_active', 1)->where('user_id', $data['billPay']->user_id)->where('currency_id', $data['billPay']->from_wallet)->firstOrFail();
			if ($payableAmount > $fromWallet->balance) {
				return response()->json('Please add fund ' . $data['billPay']->walletCurrency->name . ' wallet to payment bill');
			}

			updateWallet($data['billPay']->user_id, $data['billPay']->from_wallet, $payableAmount, 0);

			$data['billPay']->pay_amount_in_base = $payableAmount;
			$data['billPay']->base_currency_id = $fromWallet->currency_id;
			$data['billPay']->status = 2;
			$data['billPay']->save();

            BasicService::makeTransaction($user, $data['billPay']->base_currency_id, $data['billPay']->amount, $data['billPay']->charge,
                '-', $data['billPay']->utr, 'Debited balance for Bill Pay', $data['billPay']->id, BillPay::class);

			$res = $this->billPayApi($data['billPay']);
			if ($res['status'] == 'error') {
				$data['billPay']->last_api_error = $res['message'];
				$data['billPay']->save();

				return response()->json($this->withErrors('Please wait some time. administration will contact you'));
			}
			if ($res['status'] == 'success') {
				$data['billPay']->status = 3;
				$data['billPay']->save();
				$params = [
					'amount' => $data['billPay']->amount,
					'currency' => $data['billPay']->currency,
					'transaction' => $data['billPay']->utr,
				];
				$action = [
					"link" => "",
					"icon" => "fa fa-money-bill-alt text-white"
				];

				$this->sendMailSms($data['billPay']->user, 'BILL_PAY', $params);
				$this->userPushNotification($data['billPay']->user, 'BILL_PAY', $params, $action);
				$this->userFirebasePushNotification($data['billPay']->user, 'BILL_PAY', $params);

				return response()->json($this->withSuccess($res['message']));
			}
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function billPayApi($billPay)
	{
		$method = $billPay->method;
		$methodObj = 'App\\Services\\Bill\\' . $method->code . '\\Card';
		$response = $methodObj::payBill($billPay,$method);

		if ($response['status'] == 'error') {
			$data = [
				'status' => 'error',
				'message' => $response['data'],
			];
			return $data;
		}
		if ($response['status'] == 'success') {
			$data = [
				'status' => 'success',
				'message' => 'pay bill completed',
			];
			return $data;
		}
	}

	public function payBillList(Request $request)
	{
		try {
			$search = $request->all();
			$created_date = isset($search['created_at']) ? preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']) : 0;

			$array = [];
			$data['status'] = [
				'generate' => '0',
				'pending' => '1',
				'payment_completed' => '2',
				'bill_complete' => '3',
				'bill_return' => '4',
			];
			$data['bills'] = tap(BillPay::where('user_id', auth()->id())
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
				->latest()->paginate(20), function ($paginatedInstance) use ($array) {
				return $paginatedInstance->getCollection()->transform(function ($query) use ($array) {
					$array['category'] = str_replace('_', ' ', ucfirst($query->category_name));
					$array['type'] = $query->type;
					$array['currency'] = $query->currency;
					$array['amount'] = getAmount($query->amount);
					$array['charge'] = getAmount($query->charge);
					$array['status'] = $query->status;

					$array['createdTime'] = $query->created_at;
					$array['utr'] = $query->utr;
					return $array;
				});
			});

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
