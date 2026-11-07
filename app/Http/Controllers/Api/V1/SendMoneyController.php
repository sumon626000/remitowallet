<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\User;
use Facades\App\Services\BasicService;
use App\Traits\ApiValidation;
use App\Traits\ChargeLimitTrait;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class SendMoneyController extends Controller
{
	use ApiValidation, ChargeLimitTrait, Notify;


    public function sendMoneyList(Request $request)
    {
        try {
            $userId = Auth::id();
            $search = $request->all();

            $data['transfers'] = Transfer::query()
                ->with(['sender', 'receiver', 'currency'])
                ->visibleToUser($userId)
                ->filterTransfers($search, $userId)
                ->latest()
                ->paginate(20)
                ->through(function ($query) {
                    return [
                        'sender' => $query->sender?->name ?? 'N/A',
                        'receiver' => $query->receiver?->name ?? 'N/A',
                        'receiverEmail' => $query->email ?? 'N/A',
                        'amount' => getAmount($query->amount),
                        'currencyCode' => optional($query->currency)->code ?? 'N/A',
                        'type' => $query->sender_id == Auth::id() ? 'Sent' : 'Received',
                        'status' => $query->status ? 'Success' : 'Pending',
                        'transactionId' => $query->utr ?? 'N/A',
                        'createdTime' => $query->created_at,
                    ];
                });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

	public function sendMoney()
	{
		try {
			$data['transactionTypeId'] = config('transactionType.transfer');
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function sendMoneySubmit(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'recipient' => 'required|min:4',
			'amount' => 'required|numeric|min:1|not_in:0',
			'currency' => 'required|integer|min:1|not_in:0',
			'charge_from' => 'nullable|integer|in:0,1',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		try {
			$purifiedData = (object)$purifiedData;

			$amount = $purifiedData->amount;
			$currency_id = $purifiedData->currency;
			$recipient = $purifiedData->recipient;
			$charge_from = isset($purifiedData->charge_from);

			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.transfer'), $charge_from);//1 = transfer

			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}

			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}
			$receiver = $checkRecipientValidate['receiver'];
			$transfer = new Transfer();
			$transfer->sender_id = Auth::id();
			$transfer->receiver_id = $receiver->id;
			$transfer->currency_id = $checkAmountValidate['currency_id'];
			$transfer->percentage = $checkAmountValidate['percentage'];
			$transfer->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$transfer->charge_fixed = $checkAmountValidate['fixed_charge'];
			$transfer->charge = $checkAmountValidate['charge'];
			$transfer->amount = $checkAmountValidate['amount'];
			$transfer->transfer_amount = $checkAmountValidate['transfer_amount'];
			$transfer->received_amount = $checkAmountValidate['received_amount'];
			$transfer->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$transfer->note = $purifiedData->note ?? null;
			$transfer->email = $receiver->email;
            $transfer->status = 0;// 1 = success, 0 = pending
            $transfer->utr = 'T';
			$transfer->save();
			return response()->json($this->withSuccess($transfer->utr));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function sendMoneyPreview($utr)
	{
		try {
			$user = Auth::user();
			$transfer = Transfer::with('sender', 'receiver', 'currency')->where('utr', $utr)->first();

            if (!$transfer || $transfer->status) { //Check is transaction found and unpaid
				return response()->json($this->withErrors('Transaction already complete'));
			}

			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$data['enable_for'] = in_array('transfer', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
			$data['receiverName'] = optional($transfer->receiver)->name;
			$data['currency'] = optional($transfer->currency)->code;
			$data['percentageCharge'] = getAmount($transfer->percentage);
			$data['percentage'] = getAmount($transfer->percentage);
			$data['percentageCharge'] = getAmount($transfer->charge_percentage);
			$data['fixedCharge'] = getAmount($transfer->charge_fixed);
			$data['totalCharge'] = getAmount($transfer->charge);
			$data['payableAmount'] = getAmount($transfer->transfer_amount);
			$data['receiverWillReceive'] = getAmount($transfer->received_amount);
			$data['chargeDeductFrom'] = $transfer->charge_from == 1 ? 'Receiver' : 'Sender';
			$data['note'] = $transfer->note;
			$data['utr'] = $transfer->utr;

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function sendMoneyConfirm(Request $request)
	{
		try {
			$user = Auth::user();
            $transfer = Transfer::with('sender', 'receiver', 'currency')
                ->where('utr', $request->utr)
                ->where('sender_id', $user->id)
                ->first();
            if (!$transfer || $transfer->status) {
                $message = !$transfer ? 'Unauthorized or invalid transaction.' : 'Transaction already complete.';
                return response()->json($this->withErrors($message));
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

			$checkAmountValidate = $this->checkAmountValidate($transfer->amount, $transfer->currency_id, config('transactionType.transfer'), $transfer->charge_from);//1 = transfer
			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}
			$checkRecipientValidate = $this->checkRecipientValidate($transfer->email);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}

            DB::beginTransaction();
            try {
                /*Deduct money from Sender Wallet */
                $sender_wallet = updateWallet($transfer->sender_id, $transfer->currency_id, $transfer->transfer_amount, 0);
                $remark = 'Balance debited from transfer';
                BasicService::makeTransaction($transfer->sender, $transfer->currency_id, $transfer->transfer_amount,
                    $transfer->charge_from == 1 ? 0 : $transfer->charge,
                    '-', $transfer->utr, $remark, $transfer->id, Transfer::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($transfer->receiver_id, $transfer->currency_id, $transfer->received_amount, 1);
                $remark = 'Balance credited from transfer';
                BasicService::makeTransaction($transfer->receiver, $transfer->currency_id, $transfer->received_amount,
                    $transfer->charge_from == 1 ? $transfer->charge : 0,
                    '+', $transfer->utr, $remark, $transfer->id, Transfer::class);

                $transfer->status = 1;
                $transfer->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json($this->withErrors($e->getMessage()));
            }

            $transfer->notifyUsersOnTransfer($user);

			return response()->json($this->withSuccess("Your transfer has been submitted your remaining amount of money $sender_wallet"));
		} catch (\Exception $e) {
			DB::rollBack();
			return response()->json($this->withErrors($e->getMessage()));
		}
	}


    public function checkRecipientApi(Request $request)
    {
        try {
            $data = $this->checkRecipientValidate($request->recipient);
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Failed to check recipient'));
        }
    }

    public function checkRecipientValidate($recipient)
    {
        $receiver = User::where('username', $recipient)
            ->byType('user')
            ->orWhere('email', $recipient)
            ->first();

        if ($receiver && $receiver->id == Auth::id()) {
            $data['status'] = false;
            $data['message'] = 'Transfer not allowed to self email';
        } elseif ($receiver && $receiver->id != Auth::id()) {
            $data['status'] = true;
            $data['message'] = "User found. Are you looking for $receiver->name ?";
            $data['receiver'] = $receiver;
        } else {
            $data['status'] = false;
            $data['message'] = 'No user found';
        }
        return $data;
    }


}
