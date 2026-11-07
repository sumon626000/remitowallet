<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Exchange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminExchangeController extends Controller
{
    public function index()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['exchanges'] = collect(Exchange::selectRaw('COUNT(id) AS totalExchange')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completeExchange')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completeExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingTransfer')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayExchange')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthExchange')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthExchangePercentage')
            ->getProfit(30)
            ->get()
            ->toArray())->collapse();

        return view('admin.exchange.index', $data);
    }

    public function search(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterFromCurrency = $request->filter_currency_from;
        $filterToCurrency = $request->filter_currency_to;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $exchanges = Exchange::query()
            ->whereNot('status',0)
            ->with(['fromWallet', 'toWallet', 'fromWallet.currency', 'toWallet.currency', 'user'])
            ->whereHas('fromWallet')
            ->whereHas('toWallet')
            ->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('utr', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterFromCurrency), function ($query) use ($filterFromCurrency) {
                if ($filterFromCurrency != "all") {
                    return $query->WhereHas('fromWallet', function ($q) use ($filterFromCurrency) {
                        $q->where('currency_id', $filterFromCurrency);
                    });
                }
            })
            ->when(isset($filterToCurrency), function ($query) use ($filterToCurrency) {
                if ($filterToCurrency != "all") {
                    return $query->WhereHas('toWallet', function ($q) use ($filterToCurrency) {
                        $q->where('currency_id', $filterToCurrency);
                    });
                }
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('utr', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($exchanges)
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })

            ->addColumn('exchange', function ($item) {
                $fromAmount = currencyPosition($item->amount,$item->fromWallet->currency_id) ?? 'N/A';
                $toAmount = currencyPosition($item->received_amount,$item->toWallet->currency_id) ?? 'N/A';
                return '<span class="amount-highlight">'.$fromAmount.'</span>
                        <i class="bi-arrow-left-right"></i> <span class="amount-highlight">'.$toAmount.'</span>';
            })
            ->addColumn('exchange_rate', function ($item) {
                $rate = currencyPosition($item->amount,$item->fromWallet->currency_id) ?? 'N/A';
                return "<span class='amount-highlight'>$rate</span>";
            })
            ->addColumn('charge', function ($item) {
                $charge = currencyPosition($item->charge,$item->fromWallet->currency_id) ?? 0;
                return '<span class="text-danger">' . $charge . '</span>';
            })
            ->addColumn('exchange_rate', function ($item) {
                $rate = currencyPosition($item->exchange_rate,$item->fromWallet->currency_id) ?? 0;
                return $rate;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->user)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Success') . '
                  </span>';
                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('exchange_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->rawColumns(['transaction_id', 'exchange', 'charge', 'exchange_rate','user', 'status', 'exchange_at'])
            ->make(true);
    }
}
