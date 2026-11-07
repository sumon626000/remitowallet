<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPay;
use App\Traits\Notify;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BillPayController extends Controller
{
    use Notify;

    public function billPayList(Request $request, $type = 'all')
    {
        if (!in_array($type, ['all', 'pending', 'completed', 'return'])) {
            $type = 'all';
        }

        $data['bills'] = collect(BillPay::selectRaw('COUNT(id) AS totalBill')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS pendingBill')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id) * 100) AS pendingBillPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 5 THEN id END) AS processingBill')
            ->selectRaw('(COUNT(CASE WHEN status = 5 THEN id END) / COUNT(id) * 100) AS processingBillPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 3 THEN id END) AS completeBill')
            ->selectRaw('(COUNT(CASE WHEN status = 3 THEN id END) / COUNT(id) * 100) AS completeBillPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 4 THEN id END) AS returnBill')
            ->selectRaw('(COUNT(CASE WHEN status = 4 THEN id END) / COUNT(id) * 100) AS returnBillPercentage')
            ->getProfit(30)->get()
            ->toArray())
            ->collapse();

        $data['type'] = $type;
        return view('admin.bill_payment.index', $data);
    }

    public function billPaySearch(Request $request, $type = 'all')
    {
        switch ($type) {
            case 'pending':
                $arr = [2, 5];
                break;
            case 'completed':
                $arr = [3];
                break;
            case 'return':
                $arr = [4];
                break;
            default:
                $arr = [2, 3, 4, 5];
                break;
        }

        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $pays = BillPay::with(['user', 'method'])->whereIn('status', $arr)->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('category_name', 'LIKE', "%$filterName%")
                    ->orWhere('type', 'LIKE', "%$filterName%");
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
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
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('category_name', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhere('type', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('method', function ($q) use ($search) {
                            $q->where('methodName', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($pays)
            ->addColumn('method', function ($item) {
                return optional($item->method)->methodName;
            })
            ->addColumn('category', function ($item) {
                return __(str_replace('_', ' ', ucfirst($item->category_name)));
            })
            ->addColumn('type', function ($item) {
                return $item->type;
            })
            ->addColumn('amount', function ($item) {
                return '<span class="text-success">' . (getAmount($item->amount, 2)) . ' ' . __($item->currency) . '</span>
                    <span class="badge bg-soft-dark text-dark ms-1">' . optional($item->walletCurrency)->symbol . getAmount($item->pay_amount_in_base, 2) . '</span>';
            })
            ->addColumn('charge', function ($item) {
                return '<span class="text-danger">' . (getAmount($item->charge, 2)) . ' ' . __($item->currency) . '</span>';
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->user)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                                </div>
                              </a>';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 2) {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                } elseif ($item->status == 3) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Completed') . '
                  </span>';
                } elseif ($item->status == 4) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Return') . '
                  </span>';
                } elseif ($item->status == 5) {
                    return '<span class="badge bg-soft-info text-info">
                    <span class="legend-indicator bg-info"></span>' . trans('Processing') . '
                  </span>';
                }
            })
            ->addColumn('created_time', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.bill.pay.view', $item->utr);

                return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";

            })
            ->rawColumns(['method', 'category', 'type', 'amount', 'charge', 'user', 'status', 'created_time', 'action'])
            ->make(true);
    }

    public function billPayByUser($userId)
    {
        $data['bills'] = BillPay::with(['user', 'method'])->whereIn('status', ['2', '3', '4'])
            ->where('user_id', $userId)
            ->latest()->paginate(20);
        return view('admin.bill_payment.index', $data);
    }

    public function billPayView($utr)
    {
        $data['billDetails'] = BillPay::with(['user', 'method', 'service'])->where('utr', $utr)->firstOrFail();
        return view('admin.bill_payment.show', $data);
    }

    public function billPayReturn($utr)
    {
        $bill = BillPay::where('status', 2)->where('utr', $utr)->firstOrFail();
        try {
            updateWallet($bill->user_id, $bill->base_currency_id, $bill->pay_amount_in_base, 1);
            $bill->status = 4;
            $bill->save();

            $remark = 'Bill Pay Amount Return';
            BasicService::makeTransaction($bill->user, $bill->base_currency_id, $bill->pay_amount_in_base, 0, '+', strRandom(), $remark, $bill->id, BillPay::class);

            $params = [
                'amount' => getAmount($bill->amount, 2),
                'currency' => $bill->currency,
                'return_currency_amount' => getAmount($bill->pay_amount_in_base, 2),
                'return_currency' => optional($bill->baseCurrency)->code ?? basicControl()->base_currency,
                'transaction' => $bill->utr,
            ];
            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $this->sendMailSms($bill->user, 'BILL_PAYMENT_RETURN', $params);
            $this->userPushNotification($bill->user, 'BILL_PAYMENT_RETURN', $params, $action);
            $this->userFirebasePushNotification($bill->user, 'BILL_PAYMENT_RETURN', $params);
            return back()->with('success', 'Bill has been return successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function billPayConfirm($utr)
    {
        $bill = BillPay::with(['method'])->where('status', 2)->where('utr', $utr)->firstOrFail();

        try {
            $billPayMethod = $bill->method;
            $methodObj = 'App\\Services\\Bill\\' . $billPayMethod->code . '\\Card';
            if ($billPayMethod->code == 'reloadly' && $bill->category_name == 'AIRTIME') {
                $response = $methodObj::payAirtimeBill($bill, $billPayMethod);
            } else {
                $response = $methodObj::payBill($bill, $billPayMethod);
            }

            if ($response['status'] == 'success') {
                $bill->status = 3;
                $bill->save();

                $params = [
                    'amount' => $bill->amount,
                    'currency' => $bill->currency,
                    'transaction' => $bill->utr,
                ];
                $action = [
                    "link" => "#",
                    "icon" => "fa fa-money-bill-alt text-white"
                ];

                $this->sendMailSms($bill->user, 'BILL_PAY', $params);
                $this->userPushNotification($bill->user, 'BILL_PAY', $params, $action);
                $this->userFirebasePushNotification($bill->user, 'BILL_PAY', $params);

                return back()->with('success', 'Bill has been paid');
            } elseif ($response['status'] == 'processing') {
                $bill->status = 5;
                $bill->reference_id = $response['data'];
                $bill->save();
                return back()->with('success', 'Bill has been processing');
            } else {
                $bill->last_api_error = $response['data'];
                $bill->save();
                return back()->with('error', $response['data']);
            }

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
