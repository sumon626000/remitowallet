<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\QRCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminQrPaymentController extends Controller
{
    public function index()
    {
        $data['gateways'] = Gateway::select('id', 'name')->orderBy('name', 'ASC')->get();
        $data['qrPayments'] = collect(QRCode::selectRaw('COUNT(id) AS totalPayment')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completePayment')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completePaymentPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingPayment')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingPaymentPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayPayment')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayPaymentPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthPayment')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthPaymentPercentage')
            ->getProfit(30)->get()
            ->toArray())->collapse();

        return view('admin.qr_payment.index', $data);
    }

    public function search(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_email;
        $filterGateway = $request->filter_gateway;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = QRCode::with(['user', 'gateway', 'currency'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('email', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterGateway), function ($query) use ($filterGateway) {
                if ($filterGateway != "all") {
                    return $query->where('gateway_id', $filterGateway);
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
                    $subquery->where('email', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('sender', function ($item) {
                return $item->email;
            })
            ->addColumn('receiver', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->user)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('charge', function ($item) {
                return '<span class="text-danger">'. currencyPosition($item->charge,$item->currency_id) .'</span>';
            })
            ->addColumn('gateway', function ($item) {
                return optional($item->gateway)->name;
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Complete') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('send_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['sender', 'receiver', 'amount', 'charge', 'gateway', 'status', 'send_at'])
            ->make(true);
    }

}
