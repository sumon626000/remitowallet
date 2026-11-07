<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Escrow;
use App\Models\TwoFactorSetting;
use App\Traits\ApiValidation;
use App\Traits\ChargeLimitTrait;
use App\Traits\EscrowTrait;
use App\Traits\Notify;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class EscrowController extends Controller
{
	use ApiValidation, ChargeLimitTrait, Notify, Upload, EscrowTrait;

	public function escrow()
	{
		try {
			$data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function escrowSubmit(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'recipient' => 'required|min:4',
			'amount' => 'required|numeric|min:1|not_in:0',
			'currency' => 'required|integer|min:1|not_in:0',
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
			$charge_from = 1;
			$checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.escrow'), $charge_from);//5 = Escrow
			if (!$checkAmountValidate['status']) {
				return response()->json($this->withErrors($checkAmountValidate['message']));
			}
			$checkRecipientValidate = $this->checkRecipientValidate($recipient);
			if (!$checkRecipientValidate['status']) {
				return response()->json($this->withErrors($checkRecipientValidate['message']));
			}

			$receiver = $checkRecipientValidate['receiver'];
			$escrow = new Escrow();
			$escrow->sender_id = Auth::id();
			$escrow->receiver_id = optional($receiver)->id ?? null;
			$escrow->currency_id = $checkAmountValidate['currency_id'];
			$escrow->percentage = $checkAmountValidate['percentage'];
			$escrow->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
			$escrow->charge_fixed = $checkAmountValidate['fixed_charge'];
			$escrow->charge = $checkAmountValidate['charge'];
			$escrow->amount = $checkAmountValidate['amount'];
			$escrow->transfer_amount = $checkAmountValidate['transfer_amount'];
			$escrow->received_amount = $checkAmountValidate['received_amount'];
			$escrow->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
			$escrow->note = $purifiedData->note ?? null;
			$escrow->email = optional($receiver)->email ?? $recipient;
			$escrow->status = 0;// 0=Pending,1=accept/hold,2=payment done,3=sender request to payment disburse,4=payment disbursed,5=cancel
            $escrow->utr = 'E';

            if ($request->hasFile('attachment')) {
                $image = $this->fileUpload($request->attachment, config('filelocation.escrow.path'), null, null, 'webp');
                $escrow->file = $image['path'];
                $escrow->driver = $image['driver'];
            }

			$escrow->save();

			$data['utr'] = $escrow->utr;
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function escrowPreview(Request $request, $utr)
	{
		try {
			$user = Auth::user();
			$escrow = Escrow::query()
                ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
                ->where('utr', $utr)->first();

			if (!$request->view) {
				if (!$escrow || $escrow->status != 0) { //Check is transaction found and unpaid
					return response()->json($this->withErrors('Transaction already complete or invalid code'));
				}
			}

			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
			$data['enable_for'] = in_array('escrow', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
			$data['requestAmount'] = getAmount($escrow->transfer_amount);
			$data['currency'] = optional($escrow->currency)->code;
			$data['percentage'] = getAmount($escrow->percentage);
			$data['percentageCharge'] = getAmount($escrow->charge_percentage);
			$data['fixedCharge'] = getAmount($escrow->charge_fixed);
			$data['totalCharge'] = getAmount($escrow->charge);
			$data['receiveAmount'] = getAmount($escrow->received_amount);
			$data['note'] = $escrow->note ?? 'N/A';
            $data['status'] = $escrow->getStatus();
			$data['userId'] = $user->id;
			$data['receiverId'] = $escrow->receiver_id;
			$data['acceptCancelBtn'] = ($escrow->receiver_id == auth()->id() && $escrow->status == 1) ? 'yes' : 'no';
			$data['requestPaymentBtn'] = ($escrow->sender_id == auth()->id() && $escrow->status == 2) ? 'yes' : 'no';
			$data['paymentDisbursedBtn'] = ($escrow->receiver_id == auth()->id() && $escrow->status == 3) ? 'yes' : 'no';
			$data['utr'] = $escrow->utr;

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function escrowPreviewConfirm(Request $request)
	{
        $user = Auth::user();
        $escrow = Escrow::query()
            ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
            ->where('utr', $request->utr)->first();

		if (!$escrow || $escrow->status != 0) { //Check is transaction found and unpaid
			return response()->json($this->withErrors('Transaction already complete or invalid code'));
		}

		$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
		$enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

		// Security PIN check and validation
		if (in_array('escrow', $enable_for)) {
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
		$checkAmountValidate = $this->checkAmountValidate($escrow->amount, $escrow->currency_id, config('transactionType.escrow'), $escrow->charge_from);//5=escrow
		if (!$checkAmountValidate['status']) {
			return response()->json($this->withErrors($checkAmountValidate['message']));
		}

		$escrow->status = 1; //0=Pending, 1=generated, 2=payment done, 3=sender request to payment disburse, 4=payment disbursed,5=cancel, 6=dispute
		$escrow->save();

		// Email send to request sender
		$senderUser = $escrow->sender;
		$params = [
			'receiver' => optional($escrow->receiver)->name,
			'amount' => getAmount($escrow->amount),
			'currency' => optional($escrow->currency)->code,
			'transaction' => $escrow->utr,
		];

		$action = [
			"link" => route('user.escrow.index'),
			"icon" => "fa fa-money-bill-alt text-white"
		];
		$firebaseAction = route('user.escrow.index');
		$this->sendMailSms($senderUser, 'ESCROW_REQUEST_SENDER', $params);
		$this->userPushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $action);
		$this->userFirebasePushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $firebaseAction);

		// Email send to request receiver
		$receiverUser = $escrow->receiver;
		$params = [
			'sender' => Auth::user()->name,
			'amount' => getAmount($escrow->amount),
			'currency' => optional($escrow->currency)->code,
			'transaction' => $escrow->utr,
		];

		$url = route('user.escrow.paymentView', $request->utr);
		$action = [
			"link" => $url,
			"icon" => "fa fa-money-bill-alt text-white"
		];

		$this->userPushNotification($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params, $action);
		$params['links'] = $url;
		$this->sendMailSms($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params);

		return response()->json($this->withSuccess('Your escrow has been initiated successfully'));
	}

	public function escrowPaymentSubmit(Request $request)
	{
        DB::beginTransaction();
		try {
            $user = Auth::user();
            $escrow = Escrow::query()->where('utr', $request->utr)->byUser()->first();
			if (!$escrow) {
				return response()->json($this->withErrors('Record not found'));
			}

			$reqStatus = $request->status;
			$message = 'No action has been taken';

            if ($escrow->receiver_id == $user->id && $escrow->status == 1) {
                if ($reqStatus == 2) {
                    $this->processPaymentCompletion($escrow);
                    $message = "Transaction payment complete, we securely hold your payments until both the buyer and seller are in agreeance.";
                } elseif ($reqStatus == 5) {
                    $this->processEscrowCancellation($escrow);
                    $message = "Request has been canceled.";
                }
            } elseif ($escrow->sender_id == $user->id && $escrow->status == 2 && $reqStatus == 3) {
                $escrow->update(['status' => 3]);
                $this->sendEscrowNotifications($escrow, 'ESCROW_PAYMENT_DISBURSED_REQUEST');
                $message = "Request has been submitted.";
            } elseif ($escrow->receiver_id == $user->id && $escrow->status == 3 && $reqStatus == 4) {
                $this->processPaymentDisbursement($escrow);
                $message = "Payment has been disbursed.";
            } elseif ($reqStatus == 6) {
                $data['redirectRoute'] = route('api.dispute', $escrow->utr);
                return response()->json($this->withSuccess($data));
            }

            DB::commit();
			return response()->json($this->withSuccess($message));
		} catch (\Exception $e) {
            DB::rollback();
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

    public function escrowList(Request $request)
    {
        try {
            $userId = Auth::id();
            $filters = $request->all();

            $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
            $data['escrows'] = Escrow::query()
                ->with(['sender', 'receiver', 'currency', 'disputable'])
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                })
                ->search($filters, $userId)->latest()->paginate(20)
                ->through(function ($query) use ($userId) {
                    return [
                        'receiver' => optional($query->receiver)->name ?? 'N/A',
                        'receiverEmail' => $query->email,
                        'transactionId' => $query->utr,
                        'amount' => getAmount($query->amount),
                        'currency' => optional($query->currency)->code,
                        'type' => $query->sender_id == $userId ? 'Sent' : 'Received',
                        'status' => $query->getStatus(),
                        'createdTime' => $query->created_at,
                    ];
                });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

}
