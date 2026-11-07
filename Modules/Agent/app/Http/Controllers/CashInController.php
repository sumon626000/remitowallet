<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Traits\ApiValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Agent\Services\AgentService;

class CashInController extends Controller
{
    use ApiValidation;

    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function cashInCreate()
    {
        $currencies = Currency::query()->where('is_active', 1)->get();
        return view('agent::cash_in.create', compact('currencies'));
    }

    public function cashIn(Request $request)
    {
        $type = 'cash_in';
        DB::beginTransaction();

        try {
            $request->validate([
                'recipient' => 'required',
                'currency_id' => 'required|exists:currencies,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            $check = $this->checkRecipientValidate($request->recipient);
            if (!$check['status']) throw new \Exception($check['message']);
            $receiver = $check['receiver'];

            $checkAmount = $this->agentService->checkAmountValidate($request->amount, $request->currency_id, $type);
            if (!$checkAmount['status']) throw new \Exception($checkAmount['message']);

            $agent = User::where('type', 'agent')->findOrFail(auth()->id());

            $transaction = $this->agentService->process(
                agent: $agent,
                amount: $request->amount,
                type: 'cash_in',
                currencyId: $request->currency_id,
                customer: $receiver
            );
            DB::commit();

            if ($request->is('api/*')) {
                $data = [
                    'message' => 'Cash-in initiated',
                    'trx_id' => $transaction->trx_id,
                ];
                return response()->json($this->withSuccess($data));
            }
            return to_route('agent.cash_in.preview', $transaction->trx_id)->with('success', 'Cash-in initiated');

        } catch (ValidationException $e) {
            return $this->handleValidationError($e);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function preview(Request $request, $trx_id)
    {
        try {
            $user = Auth::user();
            $type = 'cash_in';
            $transaction = $this->agentService->getValidTransaction($user, $trx_id, $type);
            if (isset($transaction['error'])) {
                throw new \Exception($transaction['error']);
            }
            $enabled = $this->agentService->securityPinStatus($user, $type);

            if ($request->is('api/*')) {
                $data['enable'] = $enabled;
                $data['transaction'] = $transaction;
                return response()->json($this->withSuccess($data));
            } else {
                return view('agent::cash_in.preview', compact(['trx_id', 'transaction', 'enabled']));
            }
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function cashInConfirm(Request $request, $trx_id)
    {
        $user = Auth::user();
        $type = 'cash_in';

        DB::beginTransaction();
        try {
            $transaction = $this->agentService->getValidTransaction($user, $trx_id, $type);
            if (isset($transaction['error'])) {
                throw new \Exception($transaction['error']);
            }

            $pinValidation = $this->agentService->validateSecurityPin($user, $request->security_pin, $type);
            if (isset($pinValidation['error'])) {
                throw new \Exception($pinValidation['error']);
            }

            $amountValidation = $this->agentService->checkAmountValidate($transaction->amount, $transaction->currency_id, $type);
            if (!$amountValidation['status']) {
                throw new \Exception($amountValidation['message']);
            }
            $agentValidation = $this->checkRecipientValidate($transaction->user?->username);
            if (!$agentValidation['status']) {
                throw new \Exception($agentValidation['message']);
            }

            //Deduct money from Sender Wallet
            $sender_wallet = updateWallet($transaction->agent_id, $transaction->currency_id, $transaction->amount, 0);
            $remark = 'Balance deducted from cash in';
            $this->agentService->createTransaction($transaction->agent_id, $transaction->currency_id,
                $transaction->amount, 0, '-', $remark, $trx_id, $transaction
            );
            $remark = 'Commission earned from cash in';
            $this->agentService->createTransaction($transaction->agent_id, $transaction->currency_id,
                $transaction->commission, 0, '+', $remark, $trx_id, $transaction
            );

            //Add money to receiver wallet
            $remark = 'Cash In';
            $receiver_wallet = updateWallet($transaction->user_id, $transaction->currency_id, $transaction->amount, 1);
            $this->agentService->createTransaction($transaction->user_id, $transaction->currency_id,
                $transaction->amount, $transaction->charge, '+', $remark, $trx_id, $transaction
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

        $transaction->notifyUsersOnCashIn($user);

        $data = [
            'message' => __("Cash-in has succeeded. Your remaining amount of money: :amount",
                ['amount' => currencyPosition($sender_wallet, $transaction->currency_id)]),
            'next_route' => route('agent.transaction'),
            'next_text' => __('View Transaction')
        ];
        if ($request->is('api/*')) {
            return response()->json($this->withSuccess('Cash-in has succeeded'));
        } else {
            return to_route('agent.confirm.success')->with($data);
        }
    }

    public function checkRecipient(Request $request)
    {
        $data = $this->checkRecipientValidate($request->recipient);
        return response()->json($data);
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

    public function checkRecipientValidate($recipient): array
    {
        $sender = Auth::user();

        $receiver = User::where(function ($query) use ($recipient) {
            $query->where('username', $recipient)
                ->orWhere('email', $recipient);
        })->first();

        if (!$receiver) {
            return [
                'status' => false,
                'message' => 'No user found.',
            ];
        }
        if ($receiver->id == $sender->id) {
            return [
                'status' => false,
                'message' => 'Cash in not allowed to self account.',
            ];
        }
        if ($sender->type === 'agent' && $receiver->type === 'agent') {
            return [
                'status' => false,
                'message' => 'Cash in not allowed to another agent.',
            ];
        }
        return [
            'status' => true,
            'message' => "User found. Are you looking for {$receiver->name}?",
            'receiver' => $receiver,
        ];
    }

    public function checkAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $type = $request->type;
            $data = $this->agentService->checkAmountValidate($amount, $currency_id, $type);
            return response()->json($data);
        }
    }

    public function checkAmountApi(Request $request)
    {
        try {
            $validated = (object)$request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency_id' => 'required|integer|exists:currencies,id',
                'type' => 'required|string|in:cash_in,cash_out',
            ]);
            $data = $this->agentService->checkAmountValidate(
                $validated->amount,
                $validated->currency_id,
                $validated->type
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
