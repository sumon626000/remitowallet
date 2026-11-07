<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ContentDetails;
use App\Models\Currency;
use App\Models\Escrow;
use App\Models\TwoFactorSetting;
use App\Traits\ChargeLimitTrait;
use App\Traits\EscrowTrait;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EscrowController extends Controller
{
    use Notify, Upload, EscrowTrait , ChargeLimitTrait;

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
        $userId = Auth::id();
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
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId);
                $query->orWhere('receiver_id', $userId);
            })
            ->get()
            ->toArray())->collapse();
        return view('user.escrow.index', $data);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterCurrency = $request->filter_currency;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $escrows = Escrow::with(['sender', 'receiver', 'currency'])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId);
                $query->orWhere('receiver_id', $userId);
            })->latest()
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
            ->addColumn('type', function ($item) {
                return renderAmountTypeIcon($item->sender_id);
            })
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('receiver', function ($item) {
                return '<a class="d-flex align-items-center me-2" href="#">
                                <div class="flex-shrink-0">
                                  ' . optional($item->receiver)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->receiver)->firstname . ' ' . optional($item->receiver)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->receiver)->username . '</span>
                                </div>
                              </a>';
            })
            ->addColumn('status', function ($item) {
                return $item->getStatusBadge();
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                if ($item->status != 0) {
                    $viewRoute = route('user.escrow.paymentView', $item->utr);
                    return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-soft-info btn-xs'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";
                } else {
                    $viewRoute = route('user.escrow.confirmInit', $item->utr);
                    return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-soft-primary btn-xs'>
                        <i class='bi-cpu me-1'></i> " . trans('Generate') . "
                      </a>";
                }
            })
            ->rawColumns(['transaction_id', 'amount', 'receiver', 'type', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function escrowPaymentView(Request $request, $utr)
    {
        $escrow = Escrow::where('utr', $utr)->byUser()->firstOrFail();

        if ($request->isMethod('get')) {
            return view('user.escrow.payment', compact('escrow'));
        }
        elseif ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $reqStatus = $request->status;
                $user = Auth::user();
                $message = 'No action has been taken';

                if ($escrow->receiver_id == $user->id && $escrow->status == 1) {
                    if ($reqStatus == 2) {
                        $this->processPaymentCompletion($escrow);
                        $message = "Transaction payment complete, we securely hold your payments until both the buyer and seller are in agreeance.";
                    } elseif ($reqStatus == 5) {
                        $this->processEscrowCancellation($escrow);
                        $message = "Request has been canceled.";
                    }
                } elseif ($escrow->sender_id == $user->id && $escrow->status == 2 && $reqStatus == 3) {
                    $escrow->update(['status' => 3]);
                    $this->sendEscrowNotifications($escrow, 'ESCROW_PAYMENT_DISBURSED_REQUEST');
                    $message = "Request has been submitted.";
                } elseif ($escrow->receiver_id == $user->id && $escrow->status == 3 && $reqStatus == 4) {
                    $this->processPaymentDisbursement($escrow);
                    $message = "Payment has been disbursed.";
                } elseif ($reqStatus == 6) {
                    return redirect(route('user.dispute.view', $escrow->utr));
                }

                DB::commit();
                return redirect(route('user.escrow.index'))->with('success', $message);
            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', $e->getMessage());
            }
        }
    }


    public function create(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            $data['template'] = ContentDetails::whereHas('content', function ($query) {
                $query->where('name', 'escrow');
            })->first();
            return view('user.escrow.create', $data);
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();
            $validationRules = [
                'recipient' => 'required|min:4',
                'amount' => 'required|numeric|min:0|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0',
            ];
            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $recipient = $purifiedData->recipient;
            $charge_from = 1;
            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.escrow'), $charge_from);//5 = Escrow
            if (!$checkAmountValidate['status']) {
                return back()
                    ->withInput()
                    ->withErrors('amount', $checkAmountValidate['message'])
                    ->with('error', $checkAmountValidate['message']);
            }
            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return back()
                    ->withInput()
                    ->withErrors('recipient', $checkRecipientValidate['message'])
                    ->with('error', $checkRecipientValidate['message']);
            }

            $receiver = $checkRecipientValidate['receiver'];
            $escrow = new Escrow();
            $escrow->sender_id = Auth::id();
            $escrow->receiver_id = optional($receiver)->id ?? null;
            $escrow->currency_id = $checkAmountValidate['currency_id'];
            $escrow->percentage = $checkAmountValidate['percentage'];
            $escrow->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
            $escrow->charge_fixed = $checkAmountValidate['fixed_charge'];
            $escrow->charge = $checkAmountValidate['charge'];
            $escrow->amount = $checkAmountValidate['amount'];
            $escrow->transfer_amount = $checkAmountValidate['transfer_amount'];
            $escrow->received_amount = $checkAmountValidate['received_amount'];
            $escrow->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $escrow->note = $purifiedData->note;
            $escrow->email = optional($receiver)->email ?? $recipient;
            $escrow->status = 0;// 0=Pending,1=accept/hold,2=payment done,3=sender request to payment disburse,4=payment disbursed,5=cancel
            $escrow->utr = 'E';

            if ($request->hasFile('attachment')) {
                $image = $this->fileUpload($request->attachment, config('filelocation.escrow.path'), null, null, 'webp');
                $escrow->file = $image['path'];
                $escrow->driver = $image['driver'];
            }
            $escrow->save();
            return redirect(route('user.escrow.confirmInit', $escrow->utr));
        }
    }

    public function confirmInit(Request $request, $utr)
    {
        $escrow = Escrow::where('utr', $utr)->firstOrFail();

        if (!$escrow || $escrow->status != 0) { //Check is transaction found and unpaid
            return redirect(route('user.escrow.createRequest'))->with('error', 'Transaction already complete or invalid code');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.escrow.confirmInit', compact(['utr', 'escrow', 'enable_for']));
        } elseif ($request->isMethod('post')) {
            // Security PIN check and validation
            if (in_array('escrow', $enable_for)) {
                $purifiedData = $request->all();
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return back()->withErrors($validate)->withInput();
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('error', 'You have entered an incorrect PIN')->withInput();
                }
            }
            $checkAmountValidate = $this->checkAmountValidate($escrow->amount, $escrow->currency_id, config('transactionType.escrow'), $escrow->charge_from);//5=escrow
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $escrow->status = 1; //0=Pending, 1=generated, 2=payment done, 3=sender request to payment disburse, 4=payment disbursed,5=cancel, 6=dispute
            $escrow->save();

            // Email send to request sender
            $senderUser = $escrow->sender;
            $params = [
                'receiver' => optional($escrow->receiver)->name,
                'amount' => getAmount($escrow->amount),
                'currency' => optional($escrow->currency)->code,
                'transaction' => $escrow->utr,
            ];

            $action = [
                "name" => $senderUser->fullname,
                "image" => getFile($senderUser->image_driver, $senderUser->image),
                "link" => route('user.escrow.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.escrow.index');
            $this->sendMailSms($senderUser, 'ESCROW_REQUEST_SENDER', $params);
            $this->userPushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $action);
            $this->userFirebasePushNotification($senderUser, 'ESCROW_REQUEST_SENDER', $params, $firebaseAction);

            // Email send to request receiver
            $receiverUser = $escrow->receiver;
            $params = [
                'sender' => Auth::user()->name,
                'amount' => getAmount($escrow->amount),
                'currency' => optional($escrow->currency)->code,
                'transaction' => $escrow->utr,
            ];

            $url = route('user.escrow.paymentView', $utr);
            $action = [
                "name" => $receiverUser->fullname,
                "image" => getFile($receiverUser->image_driver, $receiverUser->image),
                "link" => $url,
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $this->userPushNotification($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params, $action);
            $this->sendMailSms($receiverUser, 'ESCROW_REQUEST_RECEIVER', $params);

            return to_route('user.confirm.success')->with([
                'message' => __("Your escrow has been initiated successfully"),
                'next_route' => route('user.escrow.index'),
                'next_text' => __('View Escrow List')
            ]);

        }
    }

    public function checkRecipient(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->checkRecipientValidate($request->recipient);
            return response()->json($data);
        }
    }

    public function checkInitiateAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $transaction_type_id = $request->transaction_type_id;
            $charge_from = $request->charge_from;
            $data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from);
            return response()->json($data);
        }
    }

}
