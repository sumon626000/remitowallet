<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Agent\Models\AgentTransaction;

class AgentApiController extends Controller
{
    use ApiValidation;


    public function dashboardApi(Request $request)
    {
        try {
            $data['wallets'] = Wallet::with('currency:id,name,code,symbol,logo,driver')
                ->select('id', 'balance as totalBalance', 'user_id', 'currency_id')
                ->where('user_id',auth()->id())
                ->customOrder()
                ->get()
                ->map(function ($wallet) {
                    $wallet->currency->image = $wallet?->currency?->getImage();
                    return $wallet;
                });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function statement(Request $request)
    {
        try {
            $data['status'] =['0=pending, 1=success, 3=failed'];

            $userId = Auth::id();

            $statements = AgentTransaction::query()
                ->with([
                    'user:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                    'agent:id,username,firstname,lastname,email,phone_code,phone,image,image_driver',
                    'currency:id,code,symbol,exchange_rate',
                ])
                ->whereHas('agent')
                ->where('agent_id', auth()->id())
                ->where('status', 1)
                ->orderBy('id', 'DESC')
                ->search($request->all())
                ->paginate(20);

            $data['statements'] = $statements->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'trx_id' => $item->trx_id,
                    'amount' => $item->amount,
                    'charge' => $item->charge,
                    'net_amount' => $item->net_amount,
                    'commission' => $item->commission,
                    'status' => $item->status,
                    'note' => $item->note,
                    'currency' => [
                        'code' => $item->currency?->code ?? null,
                        'symbol' => $item->currency?->symbol ?? null,
                    ],
                    'user' => $item->user ? [
                        'name' => $item->user->name,
                        'email' => $item->user->email,
                        'phone' => $item->user->mobile,
                        'image' => $item->user->getImage(),
                    ] : null,
                    'agent' => $item->agent ? [
                        'name' => $item->agent->name,
                        'email' => $item->agent->email,
                        'phone' => $item->agent->mobile,
                        'image' => $item->agent->getImage(),
                    ] : null,
                    'created_at' => $item->created_at,
                ];
            });

            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }
}
