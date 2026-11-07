<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function index()
    {
        return view('user.transaction.index');
    }

    public function search(Request $request)
    {

        $search = $request->search['value'] ?? null;
        $filterTransactionId = $request->filterTransactionID;
        $filterDate = explode('-', $request->filterDate);


        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transaction = Transaction::query()
            ->with([
                'user:id,username,firstname,lastname,image,image_driver',
                'currency:id,name,code,symbol,exchange_rate,logo,driver'
            ])
            ->where('user_id', auth()->id())
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhere('remarks', 'LIKE', "%{$search}%");
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
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->trx_type == '+' ? 'text-success' : 'text-danger';
                return "<h6 class='mb-0 $statusClass '>" . $item->trx_type . ' ' . currencyPosition(getAmount($item->amount), $item->currency_id) . "</h6>";
            })
            ->addColumn('charge', function ($item) {
                return "<span class='text-danger'>" . currencyPosition(getAmount($item->charge), $item->currency_id) . "</span>";
            })
            ->addColumn('remarks', function ($item) {
                return $item->remarks;
            })
            ->addColumn('date-time', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['amount', 'charge'])
            ->make(true);
    }
}
