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

class CashOutController extends Controller
{
    use ApiValidation;

    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function cashOutCreate()
    {
        $currencies = Currency::query()->where('is_active', 1)->get();
        return view('agent::user.cash_out.create', compact('currencies'));
    }

    public function cashOut(Request $request)
    {
        $type = 'cash_out';
        DB::beginTransaction();
        try {
            $request->validate([
                'recipient' => 'required',
                'currency_id' => 'required|exists:currencies,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            $agentValidation = $this->checkAgentValidate($request->recipient);
            if (!$agentValidation['status']) {
                throw new \Exception($agentValidation['message']);
            }
            $agent = $agentValidation['receiver'];

            $checkAmount = $this->agentService->checkAmountValidate($request->amount, $request->currency_id, $type);
            if (!$checkAmount['status']) throw new \Exception($checkAmount['message']);

            $user = User::where('id', auth()->id())->firstOr(function () {
                throw new \Exception("User not found");
            });
            $transaction = $this->agentService->process(
                agent: $agent,
                amount: $request->amount,
                type: 'cash_out',
                currencyId: $request->currency_id,
                customer: $user
            );
            DB::commit();
        } catch (ValidationException $e) {
            return $this->handleValidationError($e);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->handleException($e);
        }

        if ($request->is('api/*')) {
            $data = [
                'message' => 'Cash-out initiated',
                'trx_id' => $transaction->trx_id,
            ];
            return response()->json($this->withSuccess($data));
        } else {
            return to_route('user.cash_out.preview', $transaction->trx_id)->with('success', 'Cash out initiated');
        }
    }

    public function preview(Request $request, $trx_id)
    {
        try {
            $user = Auth::user();
            $type = 'cash_out';
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
                return view('agent::user.cash_out.preview', compact(['trx_id', 'transaction', 'enabled']));
            }
        } catch (\Throwable $e) {
            $toRoute = route('user.transaction');
            return $this->handleException($e, $toRoute);
        }
    }

    public function cashOutConfirm(Request $request, $trx_id)
    {
        $user = Auth::user();
        $type = 'cash_out';

        DB::beginTransaction();
        try {
            $transaction = $this->agentService->getValidTransaction($user, $trx_id, $type);
            if (isset($transaction['error'])) {
                throw new \Exception($transaction['error']);
            }

            $pinValidation = $this->agentService->validateSecurityPin($user, $request->security_pin, 'cash_out');
            if (isset($pinValidation['error'])) {
                throw new \Exception($pinValidation['error']);
            }

            $amountValidation = $this->agentService->checkAmountValidate($transaction->amount, $transaction->currency_id, 'cash_out');
            if (!$amountValidation['status']) {
                throw new \Exception($amountValidation['message']);
            }
            $agentValidation = $this->checkAgentValidate($transaction->agent?->username);
            if (!$agentValidation['status']) {
                throw new \Exception($agentValidation['message']);
            }

            //Deduct money from Sender Wallet
            $sender_wallet = updateWallet($transaction->user_id, $transaction->currency_id, $transaction->net_amount, 0);
            $remark = 'Cash Out';
            $this->agentService->createTransaction($transaction->user_id, $transaction->currency_id,
                $transaction->amount, $transaction->charge, '-', $remark, $trx_id, $transaction
            );

            //Add money to receiver wallet
            $agentAmountWithCommission = $transaction->amount + $transaction->commission;
            $receiver_wallet = updateWallet($transaction->agent_id, $transaction->currency_id, $agentAmountWithCommission, 1);
            $remark = 'Balance credited from cash out';
            $this->agentService->createTransaction($transaction->agent_id, $transaction->currency_id,
                $transaction->amount, 0, '+', $remark, $trx_id, $transaction
            );
            $remark = 'Commission earned from cash out';
            $this->agentService->createTransaction($transaction->agent_id, $transaction->currency_id,
                $transaction->commission, 0, '+', $remark, $trx_id, $transaction
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

        $transaction->notifyUsersOnCashOut($user);

        $data = [
            'message' => __("Cash-out has succeeded. Your remaining amount of money: :amount",
                ['amount' => currencyPosition($sender_wallet, $transaction->currency_id)]),
            'next_route' => route('user.transaction'),
            'next_text' => __('View Transaction')
        ];
        if ($request->is('api/*')) {
            return response()->json($this->withSuccess('Cash-out has succeeded'));
        } else {
            return to_route('user.confirm.success')->with($data);
        }
    }

    public function checkAgent(Request $request)
    {
        $recipient = $request->recipient;
        return $this->checkAgentValidate($recipient);
    }

    public function checkAgentApi(Request $request)
    {
        try {
            $data = $this->checkAgentValidate($request->recipient);
            if (!$data['status']) {
                return response()->json($this->withErrors($data['message']));
            }
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Failed to check agent'));
        }
    }

    public function checkAmount(Request $request)
    {
        $amount = $request->amount;
        $currency_id = $request->currency_id;
        $type = $request->type;
        $data = $this->agentService->checkAmountValidate($amount, $currency_id, $type);
        return response()->json($data);
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
            if (!$data['status']) {
                return response()->json($this->withErrors($data['message']));
            }
            return response()->json($this->withSuccess($data));
        } catch (ValidationException $e) {
            $errors = collect($e->errors())->collapse();
            return response()->json($this->withErrors($errors));
        } catch (\Exception $e) {
            return response()->json($this->withErrors('Failed to check amount'));
        }
    }

    public function checkAgentValidate($recipient): array
    {
        $sender = Auth::user();
        $agent = User::query()
            ->where(function ($query) use ($recipient) {
                $query->where('username', $recipient)->orWhere('email', $recipient);
            })
            ->where('type', 'agent')
            ->where('status', 1)
            ->first();
        if (!$agent) {
            return [
                'status' => false,
                'message' => 'No agent found.',
            ];
        }
        if ($agent->id == $sender->id) {
            return [
                'status' => false,
                'message' => 'Cash out not allowed to self account.',
            ];
        }
        return [
            'status' => true,
            'message' => "Agent found. Are you looking for {$agent->name}?",
            'receiver' => $agent,
        ];
    }


}
