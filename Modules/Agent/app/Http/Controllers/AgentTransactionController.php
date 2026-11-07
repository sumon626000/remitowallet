<?php
namespace Modules\Agent\Http\Controllers;

use App\Traits\ApiValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Agent\Models\AgentTransaction;
use Modules\Agent\Services\AgentService;
use Yajra\DataTables\Facades\DataTables;

class AgentTransactionController extends Controller
{
    use ApiValidation;

    protected $agentService;
    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    public function statement(){

        return view('agent::statements');
    }

    public function statementSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterTransactionId = $request->filterTransactionID;
        $filterDate = explode('-', $request->filterDate);

        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transaction = AgentTransaction::query()
            ->with(['agent:id,username,firstname,lastname,email,phone_code,phone,image,image_driver'])
            ->with(['user:id,username,firstname,lastname,email,phone_code,phone,image,image_driver'])
            ->whereHas('agent')
            ->where('agent_id', auth()->id())
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhere('note', 'LIKE', "%{$search}%");
                });
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            });

        return DataTables::of($transaction)
            ->addColumn('type', function ($item) {
                return $item->directionIcon();
            })
            ->addColumn('trx_id', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount, $item->currency_id);
                return '<span class="amount-highlight">'.$amount.'</span>';
            })
            ->addColumn('commission', function ($item) {
                $amount = currencyPosition($item->commission, $item->currency_id);
                return '<span class="text-success">'.$amount.'</span>';
            })
            ->addColumn('participant', function ($item) {
                $user = $item->user;
                return
                    '<div class="flex-grow-1">
                        <h5 class="text-hover-primary mb-0">'.e($user?->mobile).' </h5>
                        <span class="fs-6 text-body">'.e($user?->email).'</span>
                    </div>';
            })
            ->addColumn('at', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['amount', 'charge', 'participant','commission', 'type'])
            ->make(true);
    }



}
