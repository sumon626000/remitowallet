<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Traits\Notify;
use App\Traits\PayoutTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PayoutLogController extends Controller
{
    use Notify,PayoutTrait;

    public function index()
    {
        $payoutRecord = \Cache::remember('payoutRecord', now()->addMinutes(10), function () {
            return collect(Payout::selectRaw('COUNT(id) AS totalWithdrawLog')
                ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS pendingWithdraw')
                ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS pendingWithdrawPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS successWithdraw')
                ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS successWithdrawPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 3 THEN id END) AS cancelWithdraw')
                ->selectRaw('(COUNT(CASE WHEN status = 3 THEN id END) / COUNT(id)) * 100 AS cancelWithdrawPercentage')
                ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthWithdraw')
                ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthWithdrawPercentage')
                ->getProfit(30)
                ->get()
                ->toArray())->collapse();
        });
        $data['type'] = validateUserType();

        $data['methods'] = PayoutMethod::query()->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.payout.logs', $data, compact('payoutRecord'));
    }

    public function search(Request $request, $type=null)
    {
        $type = validateUserType($type);
        $basicControl = basicControl();
        $search = $request->search['value']?? null;
        $filterTransactionId = $request->filter_trx_id;
        $filterMethod = $request->filter_method;
        $filterStatus = $request->filter_status;

        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $payout = Payout::query()
            ->with(['user:id,type,username,firstname,lastname,image,image_driver', 'method:id,name,logo,driver'])
            ->whereHas('user', function ($q) use ($type) {
                filterUsersByAllowedType($q, $type);
            })
            ->whereHas('method')
            ->orderBy('id', 'desc')
            ->where('status', '!=', 0)
            ->orderBy('id', 'desc')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('trx_id', 'LIKE', $search)
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('email', 'LIKE', "%$search%")
                            ->orWhere('username', 'LIKE', "%$search%");
                    });
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == "all") {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(isset($filterMethod), function ($query) use ($filterMethod) {
                return $query->whereHas('method', function ($subQuery) use ($filterMethod) {
                    if ($filterMethod == "all") {
                        $subQuery->where('id', '!=', null);
                    } else {
                        $subQuery->where('id', $filterMethod);
                    }
                });
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        return DataTables::of($payout)
            ->addColumn('no', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('name', function ($item) {
                $url = route("admin.user.edit", optional($item->user)->id);
                $type = renderUserTypeBadge($item->user->type);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->user)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username .$type. '</span>
                            </div>
                        </a>';
            })
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('method', function ($item) {
                return '<a class="d-flex align-items-center me-2 cursor-unset" href="javascript:void(0)">
                            <div class="flex-shrink-0"> ' . $item->picture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->method)->name . '</h5>
                            </div>
                        </a>';
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->getStatusClass();
                return "<h6 class='mb-0 $statusClass '>" . fractionNumber(getAmount($item->amount)). ' ' . $item->payout_currency_code . "</h6>";

            })
            ->addColumn('charge', function ($item) {
                return "<span class='text-danger'>".getAmount($item->charge) . ' ' . $item->payout_currency_code."</span>";
            })
            ->addColumn('net amount', function ($item) {
                return "<h6>".currencyPosition(getAmount($item->amount_in_base_currency))."</h6>";
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 0) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 1) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 2) {
                    return '<span class="badge bg-soft-success text-success">' . trans('Successful') . '</span>';
                } else if ($item->status == 3) {
                    return '<span class="badge bg-soft-danger text-danger">' . trans('Canceled') . '</span>';
                }
            })
            ->addColumn('date', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->addColumn('action', function ($item) use ($basicControl) {
                $details = null;
                if ($item->information) {
                    $details = [];
                    foreach ($item->information as $k => $v) {
                        if ($v->type == "file") {
                            $details[kebab2Title($k)] = [
                                'type' => $v->type,
                                'field_name' => @$v->field_name,
                                'field_value' => getFile(config('filesystems.default'), @$v->field_value ?? @$v->field_name),
                            ];
                        } else {
                            $details[kebab2Title($k)] = [
                                'type' => @$v->type,
                                'field_name' => @$v->field_name,
                                'field_value' => @$v->field_value ?? @$v->field_name
                            ];
                        }
                    }
                }

                $icon = $item->status == 1 ? 'pencil-square' : 'eye';

                $statusColor = '';
                $statusText = '';
                if ($item->status == 0) {
                    $statusColor = 'badge bg-soft-warning text-warning';
                    $statusText = 'Pending';
                } else if ($item->status == 1) {
                    $statusColor = 'badge bg-soft-warning text-warning';
                    $statusText = 'Pending';
                } else if ($item->status == 2) {
                    $statusColor = 'badge bg-soft-success text-success';
                    $statusText = 'Success';
                } else if ($item->status == 3) {
                    $statusColor = 'badge bg-soft-danger text-danger';
                    $statusText = 'Cancelled';
                } else if ($item->status == 6) {
                    $statusColor = 'badge bg-soft-danger text-danger';
                    $statusText = 'Failed';
                }

                return "<button type='button' class='btn btn-white btn-sm edit_btn' data-bs-target='#accountInvoiceReceiptModal'
                data-id='$item->id'
                data-info='" . json_encode($details) . "'
                data-userid='" . optional($item->user)->id . "'
                data-sendername='" . optional($item->user)->name . "'
                data-transactionid='$item->trx_id'
                data-feedback='$item->feedback'
                data-lasterror='$item->last_error'
                data-amount=' " . currencyPosition(getAmount($item->amount_in_base_currency)) . "'
                data-method='" . optional($item->method)->name . "'
                data-gatewayimage='" . getFile(optional($item->method)->driver, optional($item->method)->logo) . "'
                data-datepaid='" . dateTime($item->created_at) . "'
                data-status='$item->status'
                data-status_color='$statusColor'
                data-status_text='$statusText'
                data-username='" . optional($item->user)->username . "'
                data-action='" . route('admin.payout.action', $item->id) . "'
                data-bs-toggle='modal'
                data-bs-target='#accountInvoiceReceiptModal'>  <i class='bi-$icon me-1'></i> </button>";
            })
            ->rawColumns(['name', 'amount', 'charge', 'method', 'net amount', 'status', 'action'])
            ->make(true);

    }


    public function action(Request $request, $id)
    {
        $this->validate($request, [
            'id'     => 'required|exists:payouts,id',
            'status' => ['required', Rule::in(['2', '3'])],
        ]);

        $payout = Payout::with('user', 'method')
            ->where('status', 1)
            ->find($request->id);

        if (!$payout) {
            return back()->with('error', 'Invalid payout record.');
        }

        $payout->feedback = $request->feedback;

        if ($request->status == 3) {
            return $this->handleReject($payout);
        }

        return $this->handleApprove($payout);
    }

    #===================== Reject =====================
    private function handleReject($payout)
    {
        try {
            $this->cancelPayout($payout->id, $payout->feedback);
            return back()->with('success', 'Payout rejected and wallet refunded.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    #===================== Approve =====================
    private function handleApprove($payout)
    {
        $remark = 'Withdraw via ' . $payout->method?->name;

        if (optional($payout->method)->is_automatic == 1) {

            # Flutterwave: API already called on user submit.
            if ($payout->method->code === 'flutterwave') {
                return $this->handleFlutterwaveApprove($payout, $remark);
            }

            # Other automatic gateways: call API on admin approve →→→→→→→
            return $this->handleAutoGatewayApprove($payout, $remark);
        }

        # MANUAL GATEWAY — no API, admin confirms directly →→→→→→→
        return $this->handleManualApprove($payout, $remark);
    }

    #===================== Flutterwave Approve =====================
    private function handleFlutterwaveApprove($payout, $remark)
    {
        if ($payout->response_id) {
            $payout->status = 2;
            $payout->save();
            $this->createPayoutTransaction($payout, '-', $remark);
            $this->userApprovedNotify($payout);
            return back()->with('success', 'Payout confirmed successfully.');
        }

        $payout->trx_id = $payout->trx_id . '_r' . now()->timestamp;
        $payout->save();

        return $this->handleAutoGatewayApprove($payout, $remark);
    }

    #===================== Auto Gateway Approve (call API) =====================
    private function handleAutoGatewayApprove($payout, $remark)
    {
        $methodClass = 'App\\Services\\Payout\\' . $payout->method->code . '\\Card';
        $data        = $methodClass::payouts($payout);

        if (!$data || $data['status'] == 'error') {
            $payout->last_error = $data['data'] ?? 'Unknown error';
            $payout->save();
            return back()->with('error', $payout->last_error);
        }

        # Coinbase / PerfectMoney confirm synchronously →→→→→→→
        if (in_array($payout->method->code, ['coinbase', 'perfectmoney'])) {
            $this->createPayoutTransaction($payout, '-', $remark);
            $payout->status = 2;
            $payout->save();
            $this->userApprovedNotify($payout);
            return back()->with('success', 'Payout completed successfully.');
        }

        # All others (webhook confirms final status) →→→→→→→
        $payout->response_id = $data['response_id'] ?? null;
        $payout->last_error  = null;
        $payout->status      = 2;
        $payout->save();

        return back()->with('success', 'Payout submitted. Awaiting gateway confirmation.');
    }

    #===================== Manual Gateway Approve =====================
    private function handleManualApprove($payout, $remark)
    {
        $payout->status = 2;
        $payout->save();
        $this->createPayoutTransaction($payout, '-', $remark);
        $this->userApprovedNotify($payout);

        return back()->with('success', 'Payout approved successfully.');
    }


    public function userApprovedNotify($payout): void
    {
        $msg = [
            'username' => optional($payout->user)->username,
            'amount' => getAmount($payout->amount),
            'currency' => $payout->payout_currency_code,
            'gateway' => optional($payout->method)->name,
            'transaction' => $payout->trx_id,
        ];
        $action = [
            "link" => '#',
            "icon" => "bi-cash"
        ];
        $fireBaseAction = "#";
        $this->userPushNotification($payout->user, 'PAYOUT_APPROVED', $msg, $action);
        $this->userFirebasePushNotification($payout->user,'PAYOUT_APPROVED', $msg, $fireBaseAction);
        $this->sendMailSms($payout->user, 'PAYOUT_APPROVED', [
            'gateway_name' => optional($payout->method)->name,
            'amount' => currencyPosition($payout->amount),
            'charge' => currencyPosition($payout->charge),
            'transaction' => $payout->trx_id,
            'feedback' => $payout->feedback,
        ]);
    }


    public function payoutIpn($code, Request $request)
    {
        $apiResponse = json_decode($request->all());
        $methodClass = 'App\\Services\\Payout\\' . $code . '\\Card';
        $data = $methodClass::webhook($apiResponse);
        return true;
    }

}
