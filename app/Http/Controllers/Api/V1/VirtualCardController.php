<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Template;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class VirtualCardController extends Controller
{
	use ApiValidation, Upload, Notify;

	public function virtualCardList()
	{
		try {
			$basicControl = basicControl();
			$orderLock = 'false';
			$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();

			if ($checkOrder) {
				$orderLock = 'true';
			}
			if ($basicControl->v_card_multiple == 0) {
				$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
				if ($checkOrder) {
					$orderLock = 'true';
				}
			}

			$data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->where('status', '!=', 1)->latest()->first();
			$data['approveCards'] = VirtualCardOrder::cards()->where('user_id', auth()->id())->latest()->get();
			$data['orderLock'] = $orderLock;

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function virtualCardOrder()
	{
		try {
			$basicControl = basicControl();
			$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
			if ($checkOrder) {
				return response()->json($this->withErrors('You can not eligible for request card'));
			}
			if ($basicControl->v_card_multiple == 0) {
				$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
				if ($checkOrder) {
					return response()->json($this->withErrors('You can not eligible for request card'));
				}
			}

			$data['virtualCardMethod'] = VirtualCardMethod::select(['id', 'status', 'name', 'form_field', 'currency', 'symbol', 'info_box', 'alert_message'])
				->where('status', 1)->first();
			if (!$data['virtualCardMethod']) {
				return response()->json($this->withErrors('Record not found'));
			}
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => auth()->id()]);
			$data['enable_for'] = in_array('virtual_card', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function virtualCardOrderSubmit(Request $request)
	{
		$basicControl = basicControl();
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'currency' => 'required',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		if ($this->checkUserBalance() == false) {
			return response()->json($this->withErrors("Please add fund to your $basicControl->base_currency wallet"));
		}

		$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
		if ($checkOrder) {
			return response()->json($this->withErrors('You can not eligible for request card'));
		}
		if ($basicControl->v_card_multiple == 0) {
			$checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
			if ($checkOrder) {
				return response()->json($this->withErrors('You can not eligible for request card'));
			}
		}

		DB::beginTransaction();
		try {
			$user = auth()->user();
			$virtualCardMethod = VirtualCardMethod::where('status', 1)->first();
			$virtualCardOrder = new VirtualCardOrder();

			$rulesSpecification = [];
			if ($virtualCardMethod->form_field != null) {
				foreach ($virtualCardMethod->form_field as $key => $cus) {
					$rulesSpecification[$key] = [$cus->validation];
					if ($cus->type == 'file') {
						array_push($rulesSpecification[$key], 'image');
						array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
						array_push($rulesSpecification[$key], 'max:2048');
					}
					if ($cus->type == 'text') {
						array_push($rulesSpecification[$key], 'max:191');
					}
					if ($cus->type == 'textarea') {
						array_push($rulesSpecification[$key], 'max:300');
					}
				}
			}

			$validate = Validator::make($request->all(), $rulesSpecification);
			if ($validate->fails()) {
				return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
			}

			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

			if (in_array('virtual_card', $enable_for)) {
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

			$collectionSpecification = collect($request);
			$reqFieldSpecification = [];
			if ($virtualCardMethod->form_field != null) {
				foreach ($collectionSpecification as $k => $v) {
					foreach ($virtualCardMethod->form_field as $inKey => $inVal) {
						if ($k != $inKey) {
							continue;
						} else {
							if ($inVal->type == 'file') {
								if ($request->hasFile($inKey)) {

									try {
										$image = $request->file($inKey);
										$location = config('filelocation.virtualCardOrder.path');
										$filename = $this->uploadImage($image, $location);;
										$reqField[$inKey] = [
											'field_name' => $inKey,
											'field_value' => $filename,
											'field_level' => $inVal->field_level,
											'type' => $inVal->type,
											'validation' => $inVal->validation,
										];

									} catch (\Exception $exp) {
										return response()->json('Image could not be uploaded.');
									}
								}
							} else {
								$reqFieldSpecification[$inKey] = [
									'field_name' => $inKey,
									'field_value' => $v,
									'field_level' => $inVal->field_level,
									'type' => $inVal->type,
									'validation' => $inVal->validation,
								];
							}
						}
					}
				}
				$virtualCardOrder->form_input = $reqFieldSpecification;
			} else {
				$virtualCardOrder->form_input = null;
			}

			$virtualCardOrder->virtual_card_method_id = $virtualCardMethod->id;
			$virtualCardOrder->user_id = auth()->id();
			$virtualCardOrder->currency = $request->currency;
			$virtualCardOrder->status = 4;
			$virtualCardOrder->save();

			$this->chargePay($virtualCardOrder);
			$virtualCardOrder->status = 0;
			$virtualCardOrder->save();
			DB::commit();
			return response()->json($this->withSuccess('Your virtual card request is send'));
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function checkUserBalance()
	{
		$basicControl = basicControl();
		$baseCurrency = $basicControl->base_currency;
		$virtualCardCharge = $basicControl->v_card_charge;

		$availableBalance = Wallet::select('balance')->where('user_id', auth()->id())
            ->whereHas('currency', function ($query) use ($baseCurrency) {
                $query->where('code', $baseCurrency);
            })
            ->first();

        if ($availableBalance && $availableBalance->balance > $virtualCardCharge) {
			return true;
		} else {
			return false;
		}
	}

	public function chargePay($cardOrder)
	{
		$basicControl = basicControl();
		$baseCurrency = $basicControl->base_currency;
		$virtualCardCharge = $basicControl->v_card_charge;

        $currency = Currency::where('is_active', 1)->where('code', $baseCurrency)->firstOrFail();
        $availableBalance = Wallet::where('user_id', auth()->id())->where('currency_id', $currency->id)->first();

		$newBalance = $availableBalance->balance - $virtualCardCharge;
		$availableBalance->balance = $newBalance;
		$availableBalance->save();

		$transaction = new Transaction();
		$transaction->amount = $virtualCardCharge;
		$transaction->charge = 0;
		$transaction->currency_id = $currency->id;
		$cardOrder->transactional()->save($transaction);

		$cardOrder->charge = $virtualCardCharge;
		$cardOrder->charge_currency = $currency->id;
		$cardOrder->save();

		$params = [
			'amount' => $virtualCardCharge,
			'currency' => $baseCurrency,
		];
		$action = [
			"link" => "",
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->sendMailSms(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);
		$this->userPushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params, $action);
		$this->userFirebasePushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);

		$params = [
			'username' => optional(auth()->user())->username ?? null,
			'amount' => $virtualCardCharge,
			'currency' => config('basic.base_currency_code'),
		];

		$action = [
			"link" => route('admin.virtual.cardOrderDetail', $cardOrder->id),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('admin.virtual.cardOrderDetail', $cardOrder->id);
		$this->adminMail('ADMIN_VIRTUAL_CARD_APPLY', $params);
		$this->adminPushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $action);
		$this->adminFirebasePushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $firebaseAction);

		return 0;
	}

	public function virtualCardReSubmit()
	{
		try {
			$data['cardOrder'] = VirtualCardOrder::with('cardMethod:id,currency,symbol')
                ->select(['id', 'user_id', 'virtual_card_method_id', 'currency', 'form_input'])
				->where('user_id', auth()->id())->latest()->first();
			if (!$data['cardOrder']) {
				return response()->json($this->withErrors('Record not found'));
			}
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function virtualCardReSubmitPost(Request $request)
	{
		try {
            $basicControl = basicControl();
			$data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->first();
			if (!$data['virtualCardMethod']) {
				return response()->json($this->withErrors('Record not found'));
			}
			$data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->latest()->first();
			if (!$data['cardOrder']) {
				return response()->json($this->withErrors('Record not found'));
			}

			$purifiedData = Purify::clean($request->except('image', '_token', '_method'));
			$rules = [
				'currency' => 'required',
			];
			$message = [
				'currency.required' => 'Currency field is required',
			];

			$validate = Validator::make($purifiedData, $rules, $message);

			if ($validate->fails()) {
				return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
			}

			if ($this->checkUserBalance() == false) {
				return response()->json($this->withErrors("Please add fund to your $basicControl->base_currency wallet"));
			}

			$rulesSpecification = [];
			if ($data['virtualCardMethod']->form_field != null) {
				foreach ($data['virtualCardMethod']->form_field as $key => $cus) {
					$rulesSpecification[$key] = [$cus->validation];
					if ($cus->type == 'file') {
						array_push($rulesSpecification[$key], 'image');
						array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
						array_push($rulesSpecification[$key], 'max:2048');
					}
					if ($cus->type == 'text') {
						array_push($rulesSpecification[$key], 'max:191');
					}
					if ($cus->type == 'textarea') {
						array_push($rulesSpecification[$key], 'max:300');
					}
				}
			}

			$validate = Validator::make($request->all(), $rulesSpecification);

			if ($validate->fails()) {
				return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
			}

			$collectionSpecification = collect($request);
			$reqFieldSpecification = [];
			if ($data['virtualCardMethod']->form_field != null) {
				foreach ($collectionSpecification as $k => $v) {
					foreach ($data['virtualCardMethod']->form_field as $inKey => $inVal) {
						if ($k != $inKey) {
							continue;
						} else {
							if ($inVal->type == 'file') {
								if ($request->hasFile($inKey)) {

									try {
										$image = $request->file($inKey);
										$location = config('filelocation.virtualCardOrder.path');
										$filename = $this->uploadImage($image, $location);;
										$reqField[$inKey] = [
											'field_name' => $inKey,
											'field_value' => $filename,
											'field_level' => $inVal->field_level,
											'type' => $inVal->type,
											'validation' => $inVal->validation,
										];

									} catch (\Exception $exp) {
										return response()->json('Image could not be uploaded.');
									}

								}
							} else {
								$reqFieldSpecification[$inKey] = [
									'field_name' => $inKey,
									'field_value' => $v,
									'field_level' => $inVal->field_level,
									'type' => $inVal->type,
									'validation' => $inVal->validation,
								];
							}
						}
					}
				}
				$data['cardOrder']->form_input = $reqFieldSpecification;
			} else {
				$data['cardOrder']->form_input = null;
			}

			$data['cardOrder']->currency = $request->currency;
			$data['cardOrder']->status = 3;
			$data['cardOrder']->save();

			$this->chargePay($data['cardOrder']);
			return response()->json($this->withSuccess('Re Submitted Successfully'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function virtualCardBlock(Request $request)
	{
		$purifiedData = Purify::clean($request->except('_token', '_method'));
		$rules = [
			'id' => 'required',
			'reason' => 'required',
		];
		$message = [
			'id.required' => 'Id field is required',
			'reason.required' => 'Reason field is required',
		];

		$validate = Validator::make($purifiedData, $rules, $message);

		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		try {
			$card = VirtualCardOrder::find($request->id);
			if (!$card) {
				return response()->json($this->withErrors('Record not found'));
			}
			if ($card->user_id != auth()->id()) {
				return response()->json($this->withErrors('You have not permission'));
			}
			$card->status = 5;
			$card->reason = $purifiedData['reason'];
			$card->save();
			return response()->json($this->withSuccess('Block Request Send'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function virtualCardTran(Request $request)
	{
		try {
			$array = [];
			$data['cardTransactions'] = tap(VirtualCardTransaction::
			where('user_id', auth()->id())->where('card_id', $request->card_id)
				->latest()->paginate(20), function ($paginatedInstance) use ($array) {
				return $paginatedInstance->getCollection()->transform(function ($query) use ($array) {
					$array['provider'] = optional($query->cardOrder->cardMethod)->name ?? 'N/A';
					$array['amount'] = getAmount($query->amount) ?? 'N/A';
					$array['type'] = 'Completed';
					return $array;
				});
			});

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
