<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Escrow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminEscrowController extends Controller
{
    public function index()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['escrows'] = collect(Escrow::selectRaw('COUNT(id) AS totalEscrow')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS generatedEscrow')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS generatedEscrowPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS paymentDoneEscrow')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS paymentDoneEscrowPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 5 THEN id END) AS cancelEscrow')
            ->selectRaw('(COUNT(CASE WHEN status = 5 THEN id END) / COUNT(id)) * 100 AS cancelEscrowPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingEscrow')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingEscrowPercentage')
            ->get()
            ->toArray())->collapse();

        return view('admin.escrow.index', $data);
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

        $escrows = Escrow::query()
            ->with(['sender','receiver','currency'])->latest()
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
        return DataTables::of($escrows)
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('sender', function ($item) {
                $url = route("admin.user.edit", $item->sender_id??'#');
                return
                    '<a class="d-flex align-items-center me-2" href="' . $url . '">
                        <div class="flex-shrink-0"> ' . optional($item->sender)->profilePicture() . ' </div>
                        <div class="flex-grow-1 ms-3">
                          <h5 class="text-hover-primary mb-0">' . optional($item->sender)->name . '</h5>
                          <span class="fs-6 text-body">@' . optional($item->sender)->username . '</span>
                        </div>
                  </a>';
            })
            ->addColumn('receiver', function ($item) {
                $url = route("admin.user.edit", $item->receiver_id??'#');
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->receiver)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->receiver)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->receiver)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('receiver_mail', function ($item) {
                return $item->email;
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-secondary text-secondary">
                    <span class="legend-indicator bg-secondary"></span>' . trans('Generated') . '
                  </span>';

                } elseif ($item->status == 2) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Payment done') . '
                  </span>';
                } elseif ($item->status == 3) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Sender request to payment disburse') . '
                  </span>';
                } elseif ($item->status == 4) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Payment disbursed') . '
                  </span>';
                } elseif ($item->status == 5) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Canceled') . '
                  </span>';
                } elseif ($item->status == 0) {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                } elseif ($item->status == 6) {
                    $res = '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Dispute') . '
                  </span>';
                    if (optional($item->disputable)->status == 1) {
                        $res .= '<span class="badge bg-soft-info text-info">' . trans('Refunded') . '</span>';
                    } elseif (optional($item->disputable)->status == 2) {
                        $res .= '<span class="badge bg-soft-info text-info">' . trans('Payment Disbursed') . '</span>';
                    }
                    return $res;
                } else {
                    return '<span class="badge bg-soft-info text-warning">
                   ' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->rawColumns(['transaction_id', 'amount', 'sender', 'receiver', 'receiver_mail', 'status', 'created_at'])
            ->make(true);
    }
}
