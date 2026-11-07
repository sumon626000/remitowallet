<?php

namespace Modules\Merchant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Traits\ApiValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Agent\Services\AgentService;
use Modules\Merchant\Services\MerchantService;

class MakePaymentController extends Controller
{
    use ApiValidation;

    protected $service;

    public function __construct(MerchantService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        $currencies = Currency::query()->where('is_active', 1)->get();
        return view('merchant::user.make_payment.create', compact('currencies'));
    }

    public function init(Request $request)
    {
        $user_id = auth()->id();
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'recipient' => 'required|string',
                'currency_id' => 'required|integer|exists:currencies,id',
                'amount' => 'required|numeric|min:0.01',
            ]);
            $vdt = (object) $validated;

            $merchantValidation = $this->checkMerchantValidate($vdt->recipient);
            throwIfInvalid(!$merchantValidation['status'], $merchantValidation['message']);
            $merchant = $merchantValidation['receiver'];

            $checkAmount = $this->service->checkAmountValidate($vdt->amount, $vdt->currency_id, $vdt->recipient);
            throwIfInvalid(!$checkAmount['status'], $checkAmount['message']);
            $dto = (object) $checkAmount;

            $transaction = $this->service->processPayment($dto, $user_id, $merchant->id);
            DB::commit();

            if ($request->is('api/*')) {
                $data = [
                    'message' => 'Make-payment initiated',
                    'trx_id' => $transaction->trx_id,
                ];
                return response()->json($this->withSuccess($data));
            } else {
                return to_route('user.make_payment.preview', $transaction->trx_id)->with('success', 'Make-payment initiated');
            }

        } catch (ValidationException $e) {
            return $this->handleValidationError($e);
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->handleException($e);
        }
    }

    public function preview(Request $request, $trx_id)
    {
        try {
            $user = Auth::user();
            $type = 'make_payment';
            $transaction = $this->service->getValidTransaction($user, $trx_id, 'user');
            if (isset($transaction['error'])) {
                throw new \Exception($transaction['error']);
            }
            $enabled = $this->service->securityPinStatus($user, $type);

            if ($request->is('api/*')) {
                $data['enable'] = $enabled;
                $data['transaction'] = $transaction;
                return response()->json($this->withSuccess($data));
            } else {
                return view('merchant::user.make_payment.preview', compact(['trx_id', 'transaction', 'enabled']));
            }
        } catch (\Throwable $e) {
            $toRoute = route('user.transaction');
            return $this->handleException($e, $toRoute);
        }
    }

    public function confirm(Request $request, $trx_id)
    {
        $user = Auth::user();
        $type = 'user';

        DB::beginTransaction();
        try {
            $transaction = $this->service->getValidTransaction($user, $trx_id, $type);
            if (isset($transaction['error'])) {
                throw new \Exception($transaction['error']);
            }

            $pinValidation = $this->service->validateSecurityPin($user, $request->security_pin, 'make_payment');
            if (isset($pinValidation['error'])) {
                throw new \Exception($pinValidation['error']);
            }

            $merchantValidation = $this->checkMerchantValidate($transaction->merchant?->username);
            throwIfInvalid(!$merchantValidation['status'], $merchantValidation['message']);
            $merchant = $merchantValidation['receiver'];

            $checkAmount = $this->service->checkAmountValidate($transaction->amount, $transaction->currency_id, $transaction->merchant?->username);
            throwIfInvalid(!$checkAmount['status'], $checkAmount['message']);
            $dto = (object) $checkAmount;

            //Deduct money from Sender Wallet
            $sender_wallet = updateWallet($transaction->user_id, $transaction->currency_id, $transaction->payable_amount, 0);
            $charge = $transaction->charge_applied_to === 'user' ? $transaction->charge : 0;
            $remark = 'Make Payment to merchant';
            $this->service->createTransaction($transaction->user_id, $transaction->currency_id,
                $transaction->amount, $charge, '-', $remark, $trx_id, $transaction
            );

            //Add money to receiver wallet
            $receiver_wallet = updateWallet($transaction->merchant_id, $transaction->currency_id, $transaction->received_amount, 1);
            $remark = 'Balance credited from payment';
            $this->service->createTransaction($transaction->merchant_id, $transaction->currency_id,
                $transaction->amount, 0, '+', $remark, $trx_id, $transaction
            );
            $remark = 'Charge deducted from payment';
            $this->service->createTransaction($transaction->merchant_id, $transaction->currency_id,
                $transaction->charge, 0, '-', $remark, $trx_id, $transaction
            );

            if ($request->note) {
                $transaction->note = $request->note;
            }
            $transaction->status = 1;
            $transaction->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            return $this->handleException($e);
        }

        $transaction->notifyUsersOnPayment($user);

        $data = [
            'message' => __("Payment has succeeded. Your remaining amount of money: :amount",
                ['amount' => currencyPosition($sender_wallet, $transaction->currency_id)]),
            'next_route' => route('user.transaction'),
            'next_text' => __('View Transaction')
        ];
        if ($request->is('api/*')) {
            return response()->json($this->withSuccess('Payment has succeeded'));
        } else {
            return to_route('user.confirm.success')->with($data);
        }
    }

    public function checkMerchant(Request $request)
    {
        $data = $this->checkMerchantValidate($request->recipient);
        return response()->json($data);
    }

    public function checkMerchantApi(Request $request)
    {
        try {
            $data = $this->checkMerchantValidate($request->recipient);
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Failed to check recipient'));
        }
    }

    public function checkMerchantValidate($recipient): array
    {
        $sender = Auth::user();
        $merchant = User::query()
            ->where(function ($query) use ($recipient) {
                $query->where('username', $recipient)->orWhere('email', $recipient);
            })
            ->where('type', 'merchant')
            ->where('status', 1)
            ->first();
        if (!$merchant) {
            return [
                'status' => false,
                'message' => 'Merchant not found.',
            ];
        }
        if ($merchant->id == $sender->id) {
            return [
                'status' => false,
                'message' => 'Payment not allowed to self account.',
            ];
        }
        return [
            'status' => true,
            'message' => "Merchant found. Are you looking for {$merchant->name}?",
            'receiver' => $merchant,
        ];
    }

    public function checkAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $recipient = $request->merchant;
            $data = $this->service->checkAmountValidate($amount, $currency_id, $recipient);
            return response()->json($data);
        }
    }

    public function checkAmountApi(Request $request)
    {
        try {
            $validated = (object)$request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency_id' => 'required|integer|exists:currencies,id',
                'merchant' => 'required',
            ]);
            $data = $this->service->checkAmountValidate(
                $validated->amount,
                $validated->currency_id,
                $validated->merchant
            );

            return response()->json($this->withSuccess($data));
        } catch (ValidationException $e) {
            $errors = collect($e->errors())->collapse();
            return response()->json($this->withErrors($errors));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Failed to check amount'));
        }
    }
}
