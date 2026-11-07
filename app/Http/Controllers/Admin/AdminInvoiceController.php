<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminInvoiceController extends Controller
{
    public function index()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['invoices'] = collect(Invoice::selectRaw('COUNT(id) AS totalInvoice')
            ->selectRaw('COUNT(CASE WHEN status IS NULL THEN 1 END) AS unPaidInvoice')
            ->selectRaw('(COUNT(CASE WHEN status IS NULL THEN 1 END) / COUNT(id)) * 100 AS unPaidInvoicePercentage')
            ->selectRaw('COUNT(CASE WHEN status = "paid" THEN 1 END) AS paidInvoice')
            ->selectRaw('(COUNT(CASE WHEN status = "paid" THEN 1 END) / COUNT(id)) * 100 AS paidInvoicePercentage')
            ->selectRaw('COUNT(CASE WHEN status = "rejected" THEN 1 END) AS rejectedInvoice')
            ->selectRaw('(COUNT(CASE WHEN status = "rejected" THEN 1 END) / COUNT(id)) * 100 AS rejectedInvoicePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 END) AS todayInvoice')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 END) / COUNT(id)) * 100 AS todayInvoicePercentage')
            ->getProfit(30)->get()
            ->toArray())->collapse();

        return view('admin.invoice.index', $data);
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

        $transfers = Invoice::query()
            ->with(['sender', 'sender', 'currency'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('has_slug', 'LIKE', '%' . $filterName . '%');
            })
            ->when($filterStatus === 'unpaid', function ($query) {
                return $query->whereNull('status');
            })->when($filterStatus && $filterStatus !== 'all' && $filterStatus !== 'unpaid', function ($query) use ($filterStatus) {
                return $query->where('status', $filterStatus);
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
                    $subquery->where('has_slug', 'LIKE', "%{$search}%")
                        ->orWhere('grand_total', 'LIKE', "%{$search}%")
                        ->orWhere('customer_email', 'LIKE', "%{$search}%")
                        ->orWhereHas('sender', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });

        return DataTables::of($transfers)
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('sender', function ($item) {
                $url = route("admin.user.edit", $item->sender_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->sender)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->sender)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->sender)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->grand_total,$item->currency?->id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('charge', function ($item) {
                $amount = currencyPosition($item->charge,$item->currency?->id);
                return '<span class="text-danger">' . $amount . ' </span>';
            })
            ->addColumn('receiver_mail', function ($item) {
                return $item->customer_email;
            })
            ->addColumn('status', function ($item) {
                return $item->getStatus();
            })
            ->addColumn('transaction_id', function ($item) {
                $text = $item->has_slug;
                $shortText = \Str::limit($text, 15);

                return '<span class="short-text">' . $shortText . '</span>
                <span class="full-text d-none">' . $text . '</span>
                <a href="javascript:void(0);" class="copy-text text-body small icon icon-xs icon-soft-secondary"
                    onclick="copyTextIcon(this)"><i class="bi-clipboard"></i></a>';
            })
            ->addColumn('transaction_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['transaction_id', 'amount', 'charge', 'sender', 'receiver_mail', 'status', 'transaction_at'])
            ->make(true);
    }
}
