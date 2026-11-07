<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\RequestMoney;
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

class RequestMoneyController extends Controller
{
	use ApiValidation, ChargeLimitTrait, Notify;

    public function requestMoneyList()
    {
        try {
            $userId = Auth::id();

            $data['requestMoney'] = RequestMoney::with(['sender', 'receiver', 'currency'])
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->search(request()->all())
                ->latest()
                ->paginate(20)
                ->through(function ($query) {
                    return RequestMoney::transformRequestMoney($query);
                });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function requestMoney()
	{
		try {
			$data['transactionTypeId'] = config('transactionType.request');
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneySubmit(Request $request)
	{
		$user = Auth::user();
		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'recipient' => 'required|min:4',
			'amount' => 'required|numeric|min:1|not_in:0',
			'currency' => 'required|integer|min:1|not_in:0',
			'charge_from' => 'required|integer|in:0,1'
		];
		if (in_array('request', $enable_for)) {
			$validationRules['security_pin'] = 'required|integer|digits:5';
		}
		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		if (in_array('request', $enable_for)) {
			if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
				return response()->json($this->withErrors('You have entered an incorrect PIN'));
			}
		}

		try {
			$purifiedData = (object)$purifiedData;

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$recipient = $purifiedData->recipient;
            $charge_from = $purifiedData->charge_from ??  0;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from);//2 = Request

			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}
			$receiver = $checkRecipientValidate['receiver'];
			$requestMoney = new RequestMoney();
			$requestMoney->sender_id = $user->id;
			$requestMoney->receiver_id = $receiver->id;
			$requestMoney->currency_id = $checkAmountValidate['currency_id'];
			$requestMoney->percentage = $checkAmountValidate['percentage'];
			$requestMoney->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
			$requestMoney->charge = $checkAmountValidate['charge'];
			$requestMoney->amount = $checkAmountValidate['amount'];
			$requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
			$requestMoney->received_amount = $checkAmountValidate['received_amount'];
			$requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$requestMoney->note = $purifiedData->note;
			$requestMoney->email = $receiver->email;
			$requestMoney->status = 0;// 1 = success, 0 = pending
			$requestMoney->utr = 'R';
			$requestMoney->save();

			$receivedUser = $requestMoney->receiver;
			$params = [
				'sender' => $user->name,
				'amount' => getAmount($requestMoney->amount),
				'currency' => $requestMoney->currency->code,
				'transaction' => $requestMoney->utr,
			];

			$action = [
				"link" => route('user.requestMoney.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('user.requestMoney.index');
			$this->sendMailSms($receivedUser, 'REQUEST_MONEY_INIT', $params);
			$this->userPushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $firebaseAction);

			return response()->json($this->withSuccess('Request initiated successfully'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneyCancel(Request $request)
	{
		try {
			$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $request->utr)->first();
			if (!$requestMoney) {
				return response()->json($this->withErrors('Record not found'));
			}
			$user = Auth::user();

			if (!($requestMoney->receiver_id == $user->id || $requestMoney->sender_id == $user->id)) {
				return response()->json($this->withErrors('Not Allowed'));
			}
			if (!$requestMoney || $requestMoney->status != 0) {
				return response()->json($this->withErrors('Not Allowed'));
			}

			$requestMoney->status = 2;
			$requestMoney->save();

			$receivedUser = ($user->id == $requestMoney->sender_id) ? $requestMoney->receiver : $requestMoney->sender;

			$params = [
				'sender' => $user->name,
				'amount' => getAmount($requestMoney->amount),
				'currency' => $requestMoney->currency->code,
				'transaction' => $requestMoney->utr,
			];

			$action = [
				"link" => route('user.requestMoney.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];

			$this->sendMailSms($receivedUser, 'REQUEST_MONEY_CANCEL', $params);
			$this->userPushNotification($receivedUser, 'REQUEST_MONEY_CANCEL', $params, $action);

			return response()->json($this->withSuccess('Your transfer has been canceled'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneyCheck($utr)
	{
		try {
			$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->first();
			if (!$requestMoney) {
				return response()->json($this->withErrors('Record not found'));
			}
			$user = Auth::user();

			//Check if transaction not found or any action done
			if (!$requestMoney || $requestMoney->status != 0) {
				return response()->json($this->withErrors('Not Allowed'));
			}
			if ($requestMoney->receiver_id != $user->id) {
				return response()->json($this->withErrors('Not Allowed'));
			}

			$data['transactionTypeId'] = config('transactionType.request');
			$data['utr'] = $utr;
			$data['email'] = optional($requestMoney->sender)->email ?? 'Unknown';
			$data['currencyId'] = optional($requestMoney->currency)->id;
			$data['amount'] = getAmount($requestMoney->amount);
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneyCheckSubmit(Request $request)
	{
		$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $request->utr)->first();
		if (!$requestMoney) {
			return response()->json($this->withErrors('Record not found'));
		}
		$user = Auth::user();

		//Check if transaction not found or any action done
		if (!$requestMoney || $requestMoney->status != 0) {
			return response()->json($this->withErrors('Not Allowed'));
		}

		// check if other try to attempt confirm payment with out login user
		if ($requestMoney->receiver_id != $user->id) {
			return response()->json($this->withErrors('Not Allowed'));
		}

		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'amount' => 'required|numeric|min:1|not_in:0',
            'charge_from' => 'required|integer|in:0,1'
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		try {
			$purifiedData = (object)$purifiedData;
			$amount = $purifiedData->amount;
			$currency_id = $requestMoney->currency_id;
			$recipient = $requestMoney->sender->email;
            $charge_from = $purifiedData->charge_from ??  false;

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from);

			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}

			$requestMoney->currency_id = $checkAmountValidate['currency_id'];
			$requestMoney->percentage = $checkAmountValidate['percentage'];
			$requestMoney->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
			$requestMoney->charge = $checkAmountValidate['charge'];
			$requestMoney->amount = $checkAmountValidate['amount'];
			$requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
			$requestMoney->received_amount = $checkAmountValidate['received_amount'];
			$requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$requestMoney->save();

			$data['utr'] = $requestMoney->utr;
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneyPreview($utr)
	{
		try {
			$user = Auth::user();
			$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->first();
			if (!$requestMoney) {
				return response()->json($this->withErrors('Record not found'));
			}
			if ($requestMoney->receiver_id != $user->id) {
				return response()->json($this->withErrors('Not Allowed'));
			}
			//Check if transaction not found or any action done
			if (!$requestMoney || $requestMoney->status != 0) {
				return response()->json($this->withErrors('Not Allowed'));
			}

			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$data['enable_for'] = in_array('request', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
			$data['utr'] = $utr;
			$data['name'] = optional($requestMoney->sender)->name;
			$data['currency_id'] = $requestMoney->currency_id;
			$data['currency'] = optional($requestMoney->currency)->code;
			$data['percent'] = getAmount($requestMoney->percentage);
			$data['percentCharge'] = getAmount($requestMoney->charge_percentage);
			$data['fixedCharge'] = getAmount($requestMoney->charge_fixed);
			$data['totalCharge'] = getAmount($requestMoney->charge);
			$data['payableAmount'] = getAmount($requestMoney->transfer_amount);
			$data['receiverWillReceive'] = getAmount($requestMoney->received_amount);
			$data['chargeDeductFrom'] = $requestMoney->charge_from == 1 ? 'Receiver' : 'Sender';
			$data['note'] = $requestMoney->note;

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function requestMoneyConfirm(Request $request)
	{
		try {
			$requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $request->utr)->first();
			if (!$requestMoney) {
				return response()->json($this->withErrors('Record not found'));
			}
			$user = Auth::user();

			if ($requestMoney->receiver_id != $user->id) {
				return response()->json($this->withErrors('Not Allowed'));
			}
			//Check if transaction not found or any action done
			if (!$requestMoney || $requestMoney->status != 0) {
				return response()->json($this->withErrors('Not Allowed'));
			}

			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

			// Security PIN check and validation
			if (in_array('transfer', $enable_for)) {
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

			$checkAmountValidate = $this->checkAmountValidate($requestMoney->amount, $requestMoney->currency_id, config('transactionType.request'), $requestMoney->charge_from);//1 = transfer
			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}
			$checkRecipientValidate = $this->checkRecipientValidate($requestMoney->sender->email);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}

            DB::beginTransaction();
            try {
                /*Deduct money from Sender Wallet */
                $sender_wallet = updateWallet($requestMoney->receiver_id, $requestMoney->currency_id, $requestMoney->transfer_amount, 0);
                $remark = 'Balance debited from request money';
                BasicService::makeTransaction($requestMoney->receiver, $requestMoney->currency_id, $requestMoney->transfer_amount,
                    $requestMoney->charge_from == 1 ? 0 : $requestMoney->charge,
                    '-', $requestMoney->utr, $remark, $requestMoney->id, RequestMoney::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($requestMoney->sender_id, $requestMoney->currency_id, $requestMoney->received_amount, 1);
                $remark = 'Balance credited from request money';
                BasicService::makeTransaction($requestMoney->sender, $requestMoney->currency_id, $requestMoney->received_amount,
                    $requestMoney->charge_from == 1 ? $requestMoney->charge : 0,
                    '+', $requestMoney->utr, $remark, $requestMoney->id, RequestMoney::class);

                $requestMoney->status = 1;
                $requestMoney->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json($this->withErrors($e->getMessage()));
            }

			$receivedUser = $requestMoney->sender;
			$params = [
				'sender' => $user->name,
				'amount' => getAmount($requestMoney->amount),
				'currency' => $requestMoney->currency->code,
				'transaction' => $requestMoney->utr,
			];

			$action = [
				"link" => route('user.requestMoney.index'),
				"icon" => "fa fa-money-bill-alt text-white"
			];
			$firebaseAction = route('user.requestMoney.index');
			$this->sendMailSms($receivedUser, 'REQUEST_MONEY_CONFIRM', $params);
			$this->userPushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $action);
			$this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $firebaseAction);

			return response()->json($this->withSuccess("Your transfer has been submitted your remaining amount of money $sender_wallet"));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
