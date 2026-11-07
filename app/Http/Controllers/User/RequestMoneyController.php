<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ContentDetails;
use App\Models\Currency;
use App\Models\RequestMoney;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Traits\ChargeLimitTrait;
use App\Traits\Notify;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;


class RequestMoneyController extends Controller
{
    use Notify, ChargeLimitTrait;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function initialize(Request $request)
    {
        $user = Auth::user();
        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            $data['template'] = ContentDetails::whereHas('content', function ($query) {
                $query->where('name', 'request_money');
            })->first();
            return view('user.requestMoney.create', $data, compact('enable_for'));
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();
            $validationRules = [
                'recipient' => 'required|min:4',
                'amount' => 'required|numeric|min:0|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0'
            ];
            if (in_array('request', $enable_for)) {
                $validationRules['security_pin'] = 'required|integer|digits:5';
            }
            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }

            if (in_array('request', $enable_for)) {
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('error', 'You have entered an incorrect PIN')->withInput();
                }
            }

            $purifiedData = (object)$purifiedData;

            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $recipient = $purifiedData->recipient;
            $charge_from = 0;

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from, null, 'requester');
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return back()->withInput()->with('error', $checkRecipientValidate['message']);
            }
            $receiver = $checkRecipientValidate['receiver'];
            $requestMoney = new RequestMoney();
            $requestMoney->sender_id = $user->id;
            $requestMoney->receiver_id = $receiver->id;
            $requestMoney->currency_id = $checkAmountValidate['currency_id'];
            $requestMoney->percentage = $checkAmountValidate['percentage'];
            $requestMoney->charge_percentage = $checkAmountValidate['percentage_charge'];
            $requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
            $requestMoney->charge = $checkAmountValidate['charge'];
            $requestMoney->amount = $checkAmountValidate['amount'];
            $requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
            $requestMoney->received_amount = $checkAmountValidate['received_amount'];
            $requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $requestMoney->note = $purifiedData->note;
            $requestMoney->email = $receiver->email;
            $requestMoney->status = 0;// 1 = success, 0 = pending
            $requestMoney->utr = 'R';
            $requestMoney->save();

            $receivedUser = $requestMoney->receiver;
            $params = [
                'sender' => $user->name,
                'amount' => getAmount($requestMoney->amount),
                'currency' => $requestMoney->currency->code,
                'transaction' => $requestMoney->utr,
            ];

            $action = [
                "name" => $user->name,
                "image" => getFile($user->image_driver, $user->image),
                "link" => route('user.requestMoney.index'),
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = route('user.requestMoney.index');
            $this->sendMailSms($receivedUser, 'REQUEST_MONEY_INIT', $params);
            $this->userPushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_INIT', $params, $firebaseAction);

            return to_route('user.confirm.success')->with([
                'message' => __("Request initiated successfully"),
                'next_route' => route('user.requestMoney.index'),
                'next_text' => __('View Request List')
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

    public function checkRecipientValidate($recipient)
    {
        $user = Auth::user();
        $field = filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $receiver = User::where($field, $recipient)->byType('user')->first();

        if ($receiver && $receiver->id == $user->id) {
            $data['status'] = false;
            $data['message'] = 'Transfer not allowed to self email';
        } elseif ($receiver && $receiver->id != $user->id) {
            $data['status'] = true;
            $data['message'] = "User found. Are you looking for $receiver->fullname ?";
            $data['receiver'] = $receiver;
        } else {
            $data['status'] = false;
            $data['message'] = 'No user found';
        }
        return $data;
    }

    public function checkInitiateAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $transaction_type_id = $request->transaction_type_id;
            $charge_from = $request->charge_from;
            $requestFrom = $request->requestFrom ?? null;
            $data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from, null, $requestFrom);
            return response()->json($data);
        }
    }

    public function checkRequestMoney(Request $request, $utr)
    {
        $requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->firstOrFail();
        $user = Auth::user();

        //Check if transaction not found or any action done
        if (!$requestMoney || $requestMoney->status != 0) {
            return back()->with('error', 'Not Allowed');
        }
        if ($requestMoney->receiver_id != $user->id) {
            return back()->with('error', 'Not Allowed');
        }

        if ($request->isMethod('get')) {
            return view('user.requestMoney.confirmCreate', compact(['utr', 'requestMoney']));
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();
            $validationRules = [
                'amount' => 'required|numeric|min:1|not_in:0',
                'charge_from' => 'nullable|integer|not_in:0',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $amount = $purifiedData->amount;
            $currency_id = $requestMoney->currency_id;
            $recipient = $requestMoney->sender->email;
            $charge_from = isset($purifiedData->charge_from);

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.request'), $charge_from);//1 = transfer

            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('alert', $checkAmountValidate['message']);
            }

            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return back()->withInput()->with('alert', $checkRecipientValidate['message']);
            }

            $requestMoney->currency_id = $checkAmountValidate['currency_id'];
            $requestMoney->percentage = $checkAmountValidate['percentage'];
            $requestMoney->charge_percentage = $checkAmountValidate['percentage_charge'];
            $requestMoney->charge_fixed = $checkAmountValidate['fixed_charge'];
            $requestMoney->charge = $checkAmountValidate['charge'];
            $requestMoney->amount = $checkAmountValidate['amount'];
            $requestMoney->transfer_amount = $checkAmountValidate['transfer_amount'];
            $requestMoney->received_amount = $checkAmountValidate['received_amount'];
            $requestMoney->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $requestMoney->save();
            return redirect(route('user.requestMoney.confirm', [$requestMoney->utr]))->with('success', 'Request check successfully');
        }
    }

    public function confirmRequestMoney(Request $request, $utr)
    {
        $requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->firstOrFail();
        $user = Auth::user();

        if ($requestMoney->receiver_id != $user->id) {
            return back()->with('error', 'Not Allowed');
        }
        if (!$requestMoney || $requestMoney->status != 0) {
            return back()->with('error', 'Invalid Transaction');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.requestMoney.confirm', compact(['utr', 'requestMoney', 'enable_for']));
        } elseif ($request->isMethod('post')) {
            // Security PIN check and validation
            if (in_array('transfer', $enable_for)) {
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

            $checkAmountValidate = $this->checkAmountValidate($requestMoney->amount, $requestMoney->currency_id, config('transactionType.request'), $requestMoney->charge_from);//1 = transfer
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }
            $checkRecipientValidate = $this->checkRecipientValidate($requestMoney->sender->email);
            if (!$checkRecipientValidate['status']) {
                return back()->withInput()->with('error', $checkRecipientValidate['message']);
            }

            DB::beginTransaction();
            try {
                /*Deduct money from Sender Wallet */
                $sender_wallet = updateWallet($requestMoney->receiver_id, $requestMoney->currency_id, $requestMoney->transfer_amount, 0);
                $remark = 'Balance debited from request money';
                BasicService::makeTransaction($requestMoney->receiver, $requestMoney->currency_id, $requestMoney->transfer_amount,
                    $requestMoney->charge_from == 1 ? 0 : $requestMoney->charge,
                    '-', $requestMoney->utr, $remark, $requestMoney->id, RequestMoney::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($requestMoney->sender_id, $requestMoney->currency_id, $requestMoney->received_amount, 1);
                $remark = 'Balance credited from request money';
                BasicService::makeTransaction($requestMoney->sender, $requestMoney->currency_id, $requestMoney->received_amount,
                    $requestMoney->charge_from == 1 ? $requestMoney->charge : 0,
                    '+', $requestMoney->utr, $remark, $requestMoney->id, RequestMoney::class);

                $requestMoney->status = 1;
                $requestMoney->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }

            $receivedUser = $requestMoney->sender;
            $params = [
                'sender' => $user->name,
                'amount' => getAmount($requestMoney->amount),
                'currency' => $requestMoney->currency->code,
                'transaction' => $requestMoney->utr,
            ];

            $action = [
                "name" => optional($requestMoney->receiver)->fullname,
                "image" => getFile(optional($requestMoney->receiver)->image_driver, optional($requestMoney)->receiver->image),
                "link" => route('user.requestMoney.index'),
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = route('user.requestMoney.index');
            $this->sendMailSms($receivedUser, 'REQUEST_MONEY_CONFIRM', $params);
            $this->userPushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'REQUEST_MONEY_CONFIRM', $params, $firebaseAction);

            return to_route('user.confirm.success')->with([
                'message' => __("Your transfer has been submitted. Your remaining amount of money: :amount", ['amount' => $sender_wallet]),
                'next_route' => route('user.requestMoney.index'),
                'next_text' => __('View Request List')
            ]);

        }
    }

    public function cancelRequestMoney($utr)
    {
        $requestMoney = RequestMoney::with(['sender', 'receiver', 'currency'])->where('utr', $utr)->firstOrFail();
        $user = Auth::user();

        if (!($requestMoney->receiver_id == $user->id || $requestMoney->sender_id == $user->id)) {
            return back()->with('error', 'Not Allowed');
        }
        if (!$requestMoney || $requestMoney->status != 0) {
            return back()->with('error', 'Not Allowed');
        }

        $requestMoney->status = 2;
        $requestMoney->save();

        $receivedUser = ($user->id == $requestMoney->sender_id) ? $requestMoney->receiver : $requestMoney->sender;
        $fromdUser = ($user->id == $requestMoney->sender_id) ? $requestMoney->sender : $requestMoney->receiver;

        $params = [
            'sender' => $user->name,
            'amount' => getAmount($requestMoney->amount),
            'currency' => $requestMoney->currency->code,
            'transaction' => $requestMoney->utr,
        ];

        $action = [
            "name" => $fromdUser->fullname,
            "image" => getFile($fromdUser->image_driver, $fromdUser->image),
            "link" => route('user.requestMoney.index'),
            "icon" => "fa-light fa-bell-on text-white"
        ];

        $this->sendMailSms($receivedUser, 'REQUEST_MONEY_CANCEL', $params);
        $this->userPushNotification($receivedUser, 'REQUEST_MONEY_CANCEL', $params, $action);

        return redirect(route('user.requestMoney.index'))->with("success", "Your transfer has been canceled");
    }

    public function index()
    {
        $userId = Auth::id();
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['requestMoney'] = collect(RequestMoney::selectRaw('COUNT(id) AS totalRequest')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completeRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completeRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS cancelRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS cancelRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayRequest')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayRequestPercentage')
            ->where(['sender_id' => $userId])->orwhere(['receiver_id' => $userId])
            ->get()
            ->toArray())->collapse();
        return view('user.requestMoney.index', $data);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $search = $request->search['value'] ?? null;
        $filterTrxId = $request->filter_trx_id;
        $filterCurrency = $request->filter_currency;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = RequestMoney::with(['sender', 'receiver', 'currency'])
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', '=', $userId);
                $query->orWhere('receiver_id', '=', $userId);
            })
            ->latest()
            ->when(isset($filterTrxId), function ($query) use ($filterTrxId) {
                return $query->where('utr', 'LIKE', '%' . $filterTrxId . '%');
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
                $amount = currencyPosition($item->amount, $item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('charge', function ($item) {
                $charge = currencyPosition($item->charge, $item->currency_id);
                $chargeFrom = $item->charge_from ? 'Receiver' : 'Sender';
                return '<span class="text-danger">' . $charge;
            })
            ->addColumn('charge_pay', function ($item) {
                $chargeFrom = $item->charge_from ? 'Receiver' : 'Sender';
                return '</span> <span class="badge bg-soft-dark text-dark ms-1">' . $chargeFrom . '</span>';
            })
            ->addColumn('participant', function ($item) {
                $isSender = $item->sender_id == Auth::id();
                $user = $isSender ? $item->receiver : $item->sender;
                $role = $isSender ? trans('Request To') : trans('Request From');
                return
                    '<a class="d-flex align-items-center me-2" href="#">
                    <div class="flex-shrink-0"> ' . $user?->profilePicture() . ' </div>
                    <div class="flex-grow-1 ms-2">
                        <h5 class="text-hover-primary mb-0">' . e($user?->name) . '
                            <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($role) . '"></i>
                        </h5>
                        <span class="fs-6 text-body">@' . e($user?->username) . '</span>
                    </div>
                </a>';
            })
            ->addColumn('receiver_mail', function ($item) {
                return $item->email;
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Success') . '
                  </span>';

                } elseif ($item->status == 2) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Canceled') . '
                  </span>';
                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('request_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                if ($item->receiver_id == Auth::id() && $item->status == 0) {
                    $yesRoute = route('user.requestMoney.check', [$item->utr]);
                    return "
                      <a href='" . $yesRoute . "' class='btn btn-soft-info btn-xs'>
                        <i class='bi-eye me-1'></i> " . trans('Review') . "
                      </a>";
                } elseif ($item->status == 0) {
                    $cancelRoute = route('user.requestMoney.cancel', [$item->utr]);
                    return "
                    <a href='#' class='btn btn-soft-danger btn-xs cancelButton'
                       data-bs-toggle='modal' data-bs-target='#cancelModal' data-route='$cancelRoute' >
                       <i class='bi-x me-1'></i> " . trans('Cancel') . "
                    </a>";
                } else {
                    return '-';
                }
            })
            ->rawColumns(['transaction_id', 'amount', 'charge', 'charge_pay', 'participant', 'receiver_mail', 'status', 'request_at', 'action'])
            ->make(true);
    }
}
