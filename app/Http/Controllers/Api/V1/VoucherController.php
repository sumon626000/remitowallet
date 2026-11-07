<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
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

class VoucherController extends Controller
{
    use ApiValidation, ChargeLimitTrait, Notify;


    public function voucherList()
    {
        try {
            $userId = Auth::id();
            $data['vouchers'] = Voucher::with(['sender', 'receiver', 'currency'])
                ->visibleToUser($userId)
                ->search(request()->all())
                ->latest()
                ->paginate(20)
                ->through(fn($voucher) => $voucher->transformData());

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function voucher()
    {
        try {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function voucherSubmit(Request $request)
    {
        $allowUser = basicControl()->allowUser;
        $purifiedData = Purify::clean($request->all());
        $validationRules = [
            'recipient' => 'required|min:4',
            'amount' => 'required|numeric|min:1|not_in:0',
            'currency' => 'required|integer|min:1|not_in:0',
        ];
        if (!$allowUser) {
            $validationRules['recipient'] = 'required|email';
        }

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

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.voucher'), $charge_from);//6 = voucher

            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return response()->json($this->withErrors($checkRecipientValidate['message']));
            }

            $receiver = $checkRecipientValidate['receiver'];
            $voucher = new Voucher();
            $voucher->sender_id = Auth::id();
            $voucher->receiver_id = optional($receiver)->id ?? null;
            $voucher->currency_id = $checkAmountValidate['currency_id'];
            $voucher->percentage = $checkAmountValidate['percentage'];
            $voucher->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
            $voucher->charge_fixed = $checkAmountValidate['fixed_charge'];
            $voucher->charge = $checkAmountValidate['charge'];
            $voucher->amount = $checkAmountValidate['amount'];
            $voucher->transfer_amount = $checkAmountValidate['transfer_amount'];
            $voucher->received_amount = $checkAmountValidate['received_amount'];
            $voucher->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $voucher->note = $request->note;
            $voucher->email = optional($receiver)->email ?? $recipient;
            $voucher->status = 0;// 0=Pending, 1=generate, 2=payment done, 5=cancel
            $voucher->utr = 'V';
            $voucher->save();

            $data['utr'] = $voucher->utr;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function voucherPreview($utr)
    {
        try {
            $user = Auth::user();
            $voucher = Voucher::where('utr', $utr)
                ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
                ->first();

            if (!$voucher) {
                return response()->json($this->withErrors('Invalid voucher'));
            }

            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $data['enable_for'] = in_array('voucher', is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true));
            $data['requestedAmount'] = getAmount($voucher->transfer_amount);
            $data['currencyCode'] = optional($voucher->currency)->code;
            $data['percentage'] = getAmount($voucher->percentage);
            $data['percentageCharge'] = getAmount($voucher->charge_percentage);
            $data['fixedCharge'] = getAmount($voucher->charge_fixed);
            $data['totalCharge'] = getAmount($voucher->charge);
            $data['receivableAmount'] = getAmount($voucher->received_amount);
            $data['note'] = $voucher->note;
            $data['showCharges'] = ($voucher->sender_id == auth()->id()) ? 'yes' : 'no';
            if ($voucher->status == 1) {
                $data['status'] = 'Generated';
            } elseif ($voucher->status == 2) {
                $data['status'] = 'Deposited';
            } elseif ($voucher->status == 5) {
                $data['status'] = 'Canceled';
            } elseif ($voucher->status == 0) {
                $data['status'] = 'Pending';
            } else {
                $data['status'] = 'N/A';
            }
            $data['acceptBtn'] = ($voucher->status == 1 && $voucher->receiver_id == Auth::id()) ? 'yes' : 'no';
            $data['cancelBtn'] = ($voucher->status == 1) ? 'yes' : 'no';
            $data['utr'] = $voucher->utr;

            $data['statuAre'] = [
                'Generated' => 1,
                'Deposited' => 2,
                'Canceled' => 5,
                'Pending' => 0,
            ];

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function voucherPreviewSubmit(Request $request)
    {
        try {
            $voucher = Voucher::where('utr', $request->utr)->first();
            $user = Auth::user();

            if (!$voucher || $voucher->status != 0) {
                return response()->json($this->withErrors('Transaction already complete or invalid code'));
            }

            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

            if (in_array('voucher', $enable_for)) {
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
            $checkAmountValidate = $this->checkAmountValidate($voucher->amount, $voucher->currency_id, config('transactionType.voucher'), $voucher->charge_from);//6=voucher

            if (!$checkAmountValidate['status']) {
                return response()->json($this->withErrors($checkAmountValidate['message']));
            }

            $voucher->status = 1;
            $voucher->save();

            if (is_null($voucher->receiver_id)) {
                $url = route('voucher.paymentPublicView', $request->utr);
                $receivedUser = new User();
                $receivedUser->name = 'Concern';
                $receivedUser->email = $voucher->email;
                $receiver = $voucher->email;
            } else {
                $receivedUser = $voucher->receiver;
                $receiver = optional($voucher->receiver)->name;
                $url = route('user.voucher.paymentView', $request->utr);
            }

            $params = [
                'sender' => $user->name,
                'amount' => getAmount($voucher->amount),
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
                'link' => $url,
            ];

            $action = [
                "link" => $url,
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = $url;
            if (is_null($voucher->receiver_id)) {
                $this->mail($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
            } else {
                $this->sendMailSms($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
                $this->userPushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $action);
                $this->userFirebasePushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $firebaseAction);
            }

            $params = [
                'receiver' => $receiver,
                'amount' => getAmount($voucher->amount),
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
            ];
            $action = [
                "link" => route('user.voucher.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.voucher.index');
            $this->sendMailSms($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params);
            $this->userPushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $action);
            $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $firebaseAction);

            return response()->json($this->withSuccess('Your Voucher has been initiated successfully'));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function voucherPaymentSubmit(Request $request)
    {

        try {
            $user = Auth::user();
            $voucher = Voucher::where('utr', $request->utr)
                ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
                ->first();
            if (!$voucher || $voucher->status != 0) {
                return response()->json($this->withErrors('Transaction already complete or invalid voucher'));
            }

            $reqStatus = $request->status;
            if ($voucher->status == 1 && ($reqStatus == 2 || $reqStatus == 5)) {
                if ($reqStatus == 5) {
                    $voucher->status = $reqStatus;
                    $voucher->save();

                    // send mail sms notification who receive payment
                    $params = [
                        'receiver' => optional($voucher->receiver)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $action = [
                        "link" => "#",
                        "icon" => "fa fa-money-bill-alt text-white"
                    ];
                    $firebaseAction = "#";
                    $receiver = $voucher->receiver;
                    $this->sendMailSms($receiver, 'VOUCHER_PAYMENT_CANCEL_TO', $params);
                    $this->userPushNotification($receiver, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $action);
                    $this->userFirebasePushNotification($receiver, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $firebaseAction);

                    // send mail sms notification who make payment
                    $params = [
                        'sender' => optional($voucher->sender)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $this->sendMailSms($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params);
                    $this->userPushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $action);
                    $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $firebaseAction);

                    return response()->json($this->withSuccess('Transaction canceled'));
                } elseif ($reqStatus == 2) {

                    DB::beginTransaction();

                    $fromWallet = Wallet::where('is_active', 1)->where('user_id', auth()->id())->where('currency_id', $voucher->currency_id)->first();
                    if ($voucher->received_amount > $fromWallet->balance) {
                        return response()->json($this->withErrors('Please add fund ' . $voucher->currency->name . ' wallet to payment voucher'));
                    }
                    /* Add money to Sender Wallet */
                    $sender_wallet = updateWallet($voucher->sender_id, $voucher->currency_id, $voucher->received_amount, 1);

                    $remark = 'Balance credited from voucher';
                    BasicService::makeTransaction($user, $voucher->currency_id, $voucher->received_amount,
                        $voucher->charge_from == 1 ? $voucher->charge : 0,
                        '+', $voucher->utr, $remark, $voucher->id, Voucher::class);

                    $action = [
                        "link" => route('user.voucher.index'),
                        "icon" => "fa fa-money-bill-alt text-white"
                    ];
                    $firebaseAction = route('user.voucher.index');

                    if ($voucher->receiver){
                        /* Deduct money from receiver wallet */
                        $receiver_wallet = updateWallet($voucher->receiver_id, $voucher->currency_id, $voucher->transfer_amount, 0);
                        $remark = 'Balance debited from voucher';
                        BasicService::makeTransaction($voucher->receiver, $voucher->currency_id, $voucher->received_amount,
                            $voucher->charge_from == 1 ? $voucher->charge : 0,
                            '-', $voucher->utr, $remark, $voucher->id, Voucher::class);

                        // send mail sms notification who receive payment
                        $params = [
                            'receiver' => optional($voucher->receiver)->name,
                            'amount' => getAmount($voucher->amount),
                            'currency' => optional($voucher->currency)->code,
                            'transaction' => $voucher->utr,
                        ];

                        $receiver = $voucher->receiver;
                        $this->sendMailSms($receiver, 'VOUCHER_PAYMENT_TO', $params);
                        $this->userPushNotification($receiver, 'VOUCHER_PAYMENT_TO', $params, $action);
                        $this->userFirebasePushNotification($receiver, 'VOUCHER_PAYMENT_TO', $params, $firebaseAction);
                    }

                    $voucher->status = 2;
                    $voucher->save();

                    // send mail sms notification who make payment
                    $params = [
                        'sender' => optional($voucher->sender)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $this->sendMailSms($user, 'VOUCHER_PAYMENT_FROM', $params);
                    $this->userPushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $action);
                    $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $firebaseAction);

                    DB::commit();
                    return response()->json($this->withSuccess('Payment Confirmed'));
                }
            }
            return response()->json($this->withErrors('Something went wrong'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->withErrors($e->getMessage()));
        }
    }
}
