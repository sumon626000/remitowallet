<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminVoucherController extends Controller
{
    public function index()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['vouchers'] = collect(Voucher::selectRaw('COUNT(id) AS totalVoucher')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS generatedVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS generatedVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS paymentDoneVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS paymentDoneVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 5 THEN id END) AS cancelVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS cancelVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingVoucherPercentage')
            ->getProfit(30)->get()
            ->toArray())->collapse();

        return view('admin.voucher.index', $data);
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

        $transfers = Voucher::query()
            ->whereNot('status',0)
            ->with(['sender','receiver', 'currency'])->latest()
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
            ->addColumn('charge', function ($item) {
                $amount = currencyPosition($item->charge,$item->currency_id);
                return '<span class="text-danger">' . $amount . ' </span>';
            })
            ->addColumn('sender', function ($item) {
                if ($item->sender) {
                    $url = route("admin.user.edit", $item->sender_id);
                    return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0"> ' . optional($item->sender)->profilePicture() . ' </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->sender)->name . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->sender)->username . '</span>
                                </div>
                              </a>';
                } else {
                    return 'N/A';
                }
            })
            ->addColumn('receiver', function ($item) {
                if ($item->receiver) {
                    $url = route("admin.user.edit", $item->receiver_id);
                    return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->receiver)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->receiver)->firstname . ' ' . optional($item->receiver)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->receiver)->username . '</span>
                                </div>
                              </a>';
                } else {
                    return '<span class="badge bg-soft-dark text-body "> '. $item->email .'</span>';
                }
            })
            ->addColumn('status', function ($item) {
                return $item->getStatus();
            })
            ->addColumn('transaction_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->rawColumns(['transaction_id', 'amount',  'charge','sender', 'receiver', 'status', 'transaction_at'])
            ->make(true);
    }
}
