<?php

namespace Modules\Agent\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Agent\Models\AgentTransaction;

class AgentController extends Controller
{
    use ApiValidation;

    public function index()
    {
        return view('agent::index');
    }

    public function dashboard()
    {
        $userId = auth()->id();
        $data['basic'] = basicControl();
        $data['firebaseNotify'] = config('firebase');

        $data['wallets'] = Wallet::with(['currency:id,name,code,symbol,logo,driver'])
            ->select('id', 'balance as totalBalance', 'user_id', 'currency_id')
            ->where('user_id', $userId)
            ->customOrder()
            ->get();

        $transactionData = Transaction::with('currency:id,code,exchange_rate')
            ->where('user_id', $userId)
            ->select('transactional_type', 'currency_id', 'amount', 'trx_id', 'remarks', 'trx_type')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($txn) {
                return (object)[
                    'icon' => $txn->icon,
                    'type' => $txn->type,
                    'remarks' => $txn->remarks,
                    'amount' => $txn->amount,
                    'trx_type' => $txn->trx_type,
                    'currency' => $txn->currency,
                ];
            });
        $data['transactions'] = $transactionData;

        $data['transfers'] = AgentTransaction::with([
            'user:id,username,firstname,lastname,email,image,image_driver',
            'agent:id,username,firstname,lastname,email,image,image_driver',
            'currency:id,code,exchange_rate'
        ])->where('status', 1)
            ->where('agent_id', $userId)
            ->latest('id')
            ->limit(4)
            ->get();

        $rows = AgentTransaction::query()
            ->where('agent_id', $userId)
            ->select(
                'currency_id',
                DB::raw("SUM(CASE WHEN type = 'cash_out' THEN amount ELSE 0 END) as received"),
                DB::raw("SUM(CASE WHEN type = 'cash_out' THEN commission ELSE 0 END) as commission"),
                DB::raw("SUM(CASE WHEN type = 'cash_in' THEN amount ELSE 0 END) as sent")
            )
            ->where('status', 1)
            ->with('currency:id,exchange_rate')
            ->groupBy('currency_id')
            ->get();
        $data['agent_transaction'] = [
            'total_sent' => 0,
            'total_received' => 0,
            'total_commission' => 0,
        ];
        foreach ($rows as $row) {
            $rate = $row->currency->exchange_rate ?: 1;
            $data['agent_transaction']['total_sent'] += $row->sent / $rate;
            $data['agent_transaction']['total_received'] += $row->received / $rate;
            $data['agent_transaction']['total_commission'] += $row->commission / $rate;
        }

        return view('agent::dashboard', $data);
    }

    public function loadChartData($type)
    {
        $user = auth()->user();
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate]);
        if ($type !== 'Transaction') {
            $query->where('transactional_type', "App\\Models\\$type");
        }

        $rawData = $query->selectRaw("DATE_FORMAT(created_at, '%b-%Y') as month_year,
            SUM(amount) as total,
            COUNT(id) as count")
            ->groupBy('month_year')
            ->orderByRaw("MIN(created_at)")
            ->get()
            ->keyBy('month_year')
            ->toArray();

        $allMonths = collect(range(0, 8))
            ->map(fn($i) => now()->subMonths(8 - $i)->format('M-Y'))
            ->toArray();

        $labels = array_map(fn($monthYear) => explode('-', $monthYear)[0], $allMonths);
        $amount = array_map(fn($monthYear) => $rawData[$monthYear]['total'] ?? 0, $allMonths);
        $count = array_map(fn($monthYear) => $rawData[$monthYear]['count'] ?? 0, $allMonths);

        $chart = ['labels' => $labels, 'amounts' => $amount, 'count' => $count,];

        return response()->json($chart);
    }

    public function qrCode()
    {
        return view('agent::qr_code');
    }


    public function create()
    {
        return view('agent::create');
    }

    public function store(Request $request)
    {
    }


    public function edit($id)
    {
        return view('agent::edit');
    }


    public function update(Request $request, $id)
    {
    }


    public function destroy($id)
    {
    }

}
