<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\RedeemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminRedeemCodeController extends Controller
{
    public function index()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['redeemCodes'] = collect(RedeemCode::selectRaw('COUNT(id) AS totalRedeem')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS unUsedRedeem')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS unUsedRedeemPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS usedRedeem')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS usedRedeemPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingRedeem')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingRedeemPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayRedeem')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayRedeemPercentage')
            ->getProfit(30)->get()
            ->toArray())->collapse();
        return view('admin.redeem.index', $data);
    }

    public function search(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterCurrency = $request->filter_currency;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = RedeemCode::query()
            ->with(['sender', 'receiver', 'currency'])
            ->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('utr', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterCurrency), function ($query) use ($filterCurrency) {
                if ($filterCurrency != "all") {
                    return $query->where('currency_id', $filterCurrency);
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
                        ->orWhereHas('sender', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('receiver', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('sender', function ($item) {
                if (!$item->sender_id) {
                    return 'N/A';
                }
                $url = route("admin.user.edit", $item->sender_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0"> ' . optional($item->sender)->profilePicture() . ' </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->sender)->name . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->sender)->username . '</span>
                                </div>
                              </a>';
            })
            ->addColumn('receiver', function ($item) {
                if (!$item->receiver_id) {
                    return 'N/A';
                }
                $url = route("admin.user.edit", $item->receiver_id);
                return
                    '<a class="d-flex align-items-center me-2" href="' . $url . '">
                        <div class="flex-shrink-0"> ' . optional($item->receiver)->profilePicture() . ' </div>
                        <div class="flex-grow-1 ms-3">
                          <h5 class="text-hover-primary mb-0">' . optional($item->receiver)->name . '</h5>
                          <span class="fs-6 text-body">@' . optional($item->receiver)->username . '</span>
                        </div>
                    </a>';
            })
            ->addColumn('receiver_mail', function ($item) {
                return $item->email ?? 'N/A';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-primary text-primary">
                    <span class="legend-indicator bg-primary"></span>' . trans('Unused') . '
                  </span>';

                }
                if ($item->status == 2) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Used') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('transfer_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->rawColumns(['transaction_id', 'amount', 'sender', 'receiver', 'receiver_mail', 'status', 'transfer_at'])
            ->make(true);
    }
}
