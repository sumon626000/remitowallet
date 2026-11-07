<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\DepositController;
use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use App\Models\BasicControl;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\User;
use App\Traits\PaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stevebauman\Purify\Facades\Purify;

class ApiController extends Controller
{
    use PaymentTrait;

	public function store(Request $request)
	{
		$request->request->add(['ApiKey' => $request->header('ApiKey'), 'SecretKey' => $request->header('SecretKey')]);
		$ApiKey = $request->header('ApiKey');

		$validator = Validator::make($request->all(), [
			'currency' => ['required', Rule::exists('currencies', 'code')->where(function ($query) {
				return $query->where('is_active', 1);
			})],
			'amount' => ['required', 'numeric', 'not_in:0'],
			'ipn_url' => ['required', 'url'],
			'callback_url' => ['required', 'url'],
			'order_id' => ['required', 'string', 'min:10', 'max:20', 'unique:api_orders,order_id'],
			'ApiKey' => ['required', Rule::exists('users', 'public_key')->where(function ($query) {
				return $query->where('status', 1);
			})],
			'SecretKey' => ['required', Rule::exists('users', 'secret_key')->where(function ($query) use ($ApiKey) {
				return $query->where('public_key', $ApiKey);
			})],
			'meta.customer_name' => ['nullable', 'string', 'max:20'],
			'meta.customer_email' => ['nullable', 'email'],
			'meta.description' => ['nullable', 'string', 'max:500'],
		]);

		if ($validator->fails()) {
			return response()->json(['status' => 'error', 'error' => collect($validator->errors()->messages())->collapse()], 422);
		}

		$owner = User::where('public_key', $request->ApiKey)->where('secret_key', $request->SecretKey)->first();
		if (!$owner) {
			return response()->json(['status' => 'error', 'error' => ['Invalid public or secret key'], 422]);
		}

		$currency = Currency::where('code', $request->currency)->first();
		if (!$currency) {
			return response()->json(['status' => 'error', 'error' => ['Currency code invalid'], 422]);
		}

		if ($owner->mode == 1) {
			$order = ApiOrder::create([
				"user_id" => $owner->id,
				"currency_id" => $currency->id,
				"order_id" => $request->order_id,
				"utr" => Str::uuid(),
				"amount" => $request->amount,
				"ipn_url" => $request->ipn_url,
				"redirect_url" => $request->callback_url,
				"meta" => [
					'customer_name' => $request->meta['customer_name'] ?? null,
					'customer_email' => $request->meta['email'] ?? null,
					'description' => $request->meta['description'] ?? null,
				],
			]);
		} else {
			$order = ApiOrderTest::create([
				"user_id" => $owner->id,
				"currency_id" => $currency->id,
				"order_id" => $request->order_id,
				"utr" => Str::uuid(),
				"amount" => $request->amount,
				"ipn_url" => $request->ipn_url,
				"redirect_url" => $request->callback_url,
				"meta" => [
					'customer_name' => $request->meta['customer_name'] ?? null,
					'customer_email' => $request->meta['email'] ?? null,
					'description' => $request->meta['description'] ?? null,
				],
			]);
		}

		if ($owner->mode == 1) {
			$route = route('make.payment', ['live', $order->utr]);
		} else {
			$route = route('make.payment', ['test', $order->utr]);
		}

		return response()->json([
			'status' => 'success',
			'data' => [
				'id' => $order->utr,
				'currency' => optional($order->currency)->currency ?? $request->currency,
				'amount' => $order->amount,
				'order_id' => $order->order_id,
				'ipn_url' => $order->ipn_url,
				'callback_url' => $order->redirect_url,
				'meta' => [
					'customer_name' => optional($order->meta)->customer_name ?? null,
					'customer_email' => optional($order->meta)->customer_email ?? null,
					'description' => optional($order->meta)->description ?? null,
				],
				'redirect_url' => $route
			],
		]);
	}

	public function makePayment($mode, $utr)
	{
		try {
			if ($mode == 'live') {
				$apiOrder = ApiOrder::where('utr', $utr)->where('status', 0)->first();
			} else {
				$apiOrder = ApiOrderTest::where('utr', $utr)->where('status', 0)->first();
			}
            if (!$apiOrder) {
                return view('expired')->with('error', 'Invalid payment request');
            }
			if ($mode == 'test') {
				$basicControl = BasicControl::select('sandbox_gateways')->first();
				$methods = Gateway::automatic()->where('status',1)
                    ->whereIn('id', $basicControl->sandbox_gateways)->orderBy('sort_by', 'ASC')->get();
			} else {
				$methods = Gateway::where('status',1)->orderBy('sort_by', 'ASC')->get();
			}
			return view('api.payment', compact('methods', 'apiOrder', 'mode'));

		} catch (\Exception $e) {
			$this->apiFailResponseSend($apiOrder, 'Something bad happened. Please try again');
			return back()->with('alert', $e);
		}
	}

	public function makePaymentConfirm(Request $request, $mode, $utr)
	{
		try {
			if ($mode == 'live') {
				$apiOrder = ApiOrder::where('utr', $utr)->where('status', 0)->first();
			} else {
				$apiOrder = ApiOrderTest::where('utr', $utr)->where('status', 0)->first();
			}
            if (!$apiOrder) {
                return view('expired')->with('error', 'Invalid payment request');
            }

			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'amount' => 'required|numeric|min:1|not_in:0',
				'currency' => 'required|integer|min:1|not_in:0',
				'methodId' => 'required|integer|min:1|not_in:0',
			];

			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return back()->withErrors($validate)->withInput();
			}

			$purifiedData = (object)$purifiedData;
			$amount = $apiOrder->amount;
			$currency_id = $apiOrder->currency_id;
			$methodId = $purifiedData->methodId;

			$checkAmountValidate = $this->validatePayment($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('alert', $checkAmountValidate['message']);
			}

			$apiOrder->percentage = $checkAmountValidate['percentage'];
			$apiOrder->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$apiOrder->charge_fixed = $checkAmountValidate['fixed_charge'];
			$apiOrder->charge = $checkAmountValidate['charge'];
			$apiOrder->save();

			$deposit = new Deposit();
			$deposit->user_id = $apiOrder->user_id;
			$deposit->depositable_type = ($mode == 'test') ? ApiOrderTest::class : ApiOrder::class;;
			$deposit->depositable_id = $apiOrder->id;
			$deposit->payment_method_id = $methodId;
			$deposit->currency_id = $currency_id;
			$deposit->amount = $apiOrder->amount;
			$deposit->payment_method_currency = $checkAmountValidate['payment_method_currency'];
			$deposit->charges_limit_id = $checkAmountValidate['charges_limit_id'];
			$deposit->percentage = $checkAmountValidate['percentage'];
			$deposit->percentage_charge = $checkAmountValidate['percentage_charge'];
			$deposit->fixed_charge = $checkAmountValidate['fixed_charge'];
			$deposit->charge = $checkAmountValidate['charge'];
			$deposit->payable_amount = $checkAmountValidate['payable_amount'];
			$deposit->payable_amount_in_base_currency = $checkAmountValidate['payable_amount_in_base_currency'];
			$deposit->base_currency_charge = $checkAmountValidate['base_currency_charge'];
			$deposit->status = 0;
			$deposit->mode = ($mode == 'test') ? '1' : '0';
			$deposit->save();

			$checkAmountValidate = (new DepositController())->checkAmountValidate($deposit->amount, $deposit->currency_id, config('transactionType.deposit'), $deposit->payment_method_id);
			if (!$checkAmountValidate['status']) {
				return back()->withInput()->with('error', $checkAmountValidate['message']);
			}

			return redirect(route('payment.process', $deposit->trx_id));

		} catch (\Exception $e) {
			$this->apiFailResponseSend($apiOrder, 'Something bad happened. Please try again');
			return back()->with('alert', $e);
		}
	}

	public function apiFailResponseSend($order, $msg)
	{
		$order->status = 2;
		$order->save();

		$url = $order->ipn_url;
		$postParam = [
			'status' => 'error',
			'data' => [
				'message' => $msg
			],
		];
		$methodObj = 'App\\Services\\BasicCurl';
		$response = $methodObj::curlPostRequest($url, $postParam);
		return 0;
	}

	public function verifyPayment(Request $request)
	{
		$request->request->add(['ApiKey' => $request->header('ApiKey'), 'SecretKey' => $request->header('SecretKey')]);
		$ApiKey = $request->header('ApiKey');

		$validator = Validator::make($request->all(), [
			'order_id' => ['required', 'string', 'min:10', 'max:20'],
			'ApiKey' => ['required', Rule::exists('users', 'public_key')->where(function ($query) {
				return $query->where('status', 1);
			})],
			'SecretKey' => ['required', Rule::exists('users', 'secret_key')->where(function ($query) use ($ApiKey) {
				return $query->where('public_key', $ApiKey);
			})],
		]);

		if ($validator->fails()) {
			return response()->json(['status' => 'error', 'error' => collect($validator->errors()->messages())->collapse()], 422);
		}

		$owner = User::where('public_key', $request->ApiKey)->where('secret_key', $request->SecretKey)->first();
		if (!$owner) {
			return response()->json(['status' => 'error', 'error' => ['Invalid public or secret key'], 422]);
		}


		if ($owner->mode == 1) {
			$order = ApiOrder::where('order_id', $request->order_id)->first();
			if (!$order) {
				return response()->json(['status' => 'error', 'error' => ['Something bad happened'], 422]);
			}
		} else {
			$order = ApiOrderTest::where('order_id', $request->order_id)->first();
			if (!$order) {
				return response()->json(['status' => 'error', 'error' => ['Something bad happened'], 422]);
			}
		}
		$status = '';
		if ($order->status == 0) {
			$status = 'pending';
		} elseif ($order->status == 1) {
			$status = 'success';
		} elseif ($order->status == 2) {
			$status = 'failed';
		}

		return response()->json([
			'status' => 'success',
			'data' => [
				'order_id' => $order->order_id,
				'status' => $status,
			],
		]);
	}

}
