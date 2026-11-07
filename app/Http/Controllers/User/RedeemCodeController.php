<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\RedeemCode;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
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

class RedeemCodeController extends Controller
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

    public function index()
    {
        $userId = Auth::id();
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
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId);
                $query->orWhere('receiver_id', $userId);
            })
            ->get()
            ->toArray())->collapse();

        return view('user.redeem.index', $data);
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

        $transfers = RedeemCode::with(['sender', 'receiver', 'currency'])
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
        return DataTables::of($transfers)
            ->addColumn('type', function ($item) {
                return renderAmountTypeIcon($item->sender_id);
            })
            ->addColumn('transaction_id', function ($item) {
                $iconId  = 'icon_' . $item->id;
                $element = "referralsKeyCode" . $item->id;
                return '
                <div class="input-group input-group-sm input-group-merge table-input-group">
                    <input id="' . $element . '" type="text" class="form-control" readonly value="' . e($item->utr) . '">
                    <a class="js-clipboard input-group-text" onclick="copyFunction(\'' . $element . '\')"
                       href="javascript:;"
                       data-bs-toggle="tooltip"
                       title="Copy to clipboard">
                        <i id="' . $iconId . '" class="bi-clipboard"></i>
                    </a>
                </div>';
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount, $item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('charge', function ($item) {
                $charge = currencyPosition($item->charge, $item->currency_id);
                return '<span class="text-danger">' . $charge;
            })
            ->addColumn('charge_pay', function ($item) {
                $chargeFrom = $item->charge_from ? 'Receiver' : 'Sender';
                return '</span> <span class="badge bg-soft-dark text-dark ms-1">' . $chargeFrom . '</span>';
            })
            ->addColumn('participant', function ($item) {
                $isSender = $item->sender_id == Auth::id();
                $user = $isSender ? $item->receiver : $item->sender;
                $role = $isSender ? trans('Receiver') : trans('Sender');

                if ($user) {
                    return
                        '<a class="d-flex align-items-center me-2" href="#">
                        <div class="flex-shrink-0">' . $user->profilePicture() . '</div>
                        <div class="flex-grow-1 ms-2">
                            <h5 class="text-hover-primary mb-0">' . e($user->name) . '
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($role) . '"></i>
                            </h5>
                            <span class="fs-6 text-body">@' . e($user->username) . '</span>
                        </div>
                    </a>';
                } else {
                    return '<span class="badge bg-soft-dark text-body "> ' . trans('Not yet processed') . '</span>';
                }
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
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                if (!$item->status) {
                    $viewRoute = route('user.redeem.confirm', $item->utr);
                    return "<a href='" . $viewRoute . "' class='btn btn-soft-primary btn-xs'>
                                <i class='bi-check-circle me-1'></i> " . trans('Confirm') . "
                            </a>";
                } else {
                    $note = $item->note;
                    return
                        "<div class='btn btn-soft-info btn-xs' type='button'
                        data-bs-toggle='tooltip' data-bs-placement='left' title='" . e($note) . "'>
                        <i class='bi-journal-text me-1'></i> " . trans('Note') . "
                    </div>";
                }
            })
            ->rawColumns(['transaction_id', 'participant', 'amount', 'charge', 'charge_pay', 'receiver_mail', 'type', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function insertRedeemCode(Request $request)
    {
        $user = Auth::user();
        if ($request->isMethod('get')) {
            return view('user.redeem.insert');
        } elseif ($request->isMethod('post')) {

            $purifiedData = $request->all();
            $validationRules = [
                'redeemCode' => 'required',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $utr = $purifiedData->redeemCode;
            $redeemCode = RedeemCode::where('utr', $utr)->first();

            if (!$redeemCode) { //Check is transaction found
                return back()
                    ->withInput()
                    ->withErrors(['redeemCode' => 'Your redeem code is invalid'])
                    ->with('error', 'Your redeem code is invalid');
            } elseif ($redeemCode->sender_id == $user->id) { // Check is transaction try to used by own
                return back()
                    ->withInput()
                    ->withErrors(['redeemCode' => 'Not allowed to self generated code'])
                    ->with('error', 'Not allowed to self generated code');
            } elseif ($redeemCode->status != 1) { // Check is transaction unused
                return back()
                    ->withInput()
                    ->withErrors(['redeemCode' => 'Your redeem code is already used'])
                    ->with('error', 'Your redeem code is already used');
            }

            DB::beginTransaction();
            try {
                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($user->id, $redeemCode->currency_id, $redeemCode->received_amount, 1);

                $remark = 'Balance credited from redeem code';
                BasicService::makeTransaction($user, $redeemCode->currency_id, $redeemCode->received_amount,
                    $redeemCode->charge_from == 1 ? $redeemCode->charge : 0,
                    '+', $redeemCode->utr, $remark, $redeemCode->id, RedeemCode::class);

                $redeemCode->receiver_id = $user->id;
                $redeemCode->email = $user->email;
                $redeemCode->status = 2;// 1 = success, 0 = pending, 2 = used
                $redeemCode->save();

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }


            $receivedUserUsedBy = $user;
            $params = [
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];
            $action = [
                "name" => $receivedUserUsedBy->fullname,
                "image" => getFile($receivedUserUsedBy->image_driver, $receivedUserUsedBy->image),
                "link" => route('user.redeem.index'),
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = route('user.redeem.index');
            $this->sendMailSms($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params);
            $this->userPushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $action);
            $this->userFirebasePushNotification($receivedUserUsedBy, 'REDEEM_CODE_USED_BY', $params, $firebaseAction);

            $receivedUserSender = optional($redeemCode->sender)->name;
            $params = [
                'receiver' => optional($redeemCode->receiver)->name,
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];

            $this->sendMailSms($receivedUserSender, 'REDEEM_CODE_SENDER', $params);
            $this->userPushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $action);
            $this->userFirebasePushNotification($receivedUserSender, 'REDEEM_CODE_SENDER', $params, $firebaseAction);

            return redirect(route('user.redeem.index'))->with('success', 'Redeem code submitted successfully');
        }
    }

    public function initialize(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return view('user.redeem.create', $data);
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();
            $validationRules = [
                'amount' => 'required|numeric|min:1|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0',
                'charge_from' => 'nullable|integer|not_in:0',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $charge_from = isset($purifiedData->charge_from);

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.redeem'), $charge_from);//1 = transfer

            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $redeemCode = new RedeemCode();
            $redeemCode->sender_id = Auth::id();
            $redeemCode->receiver_id = null;
            $redeemCode->currency_id = $checkAmountValidate['currency_id'];
            $redeemCode->percentage = $checkAmountValidate['percentage'];
            $redeemCode->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
            $redeemCode->charge_fixed = $checkAmountValidate['fixed_charge'];
            $redeemCode->charge = $checkAmountValidate['charge'];
            $redeemCode->amount = $checkAmountValidate['amount'];
            $redeemCode->transfer_amount = $checkAmountValidate['transfer_amount'];
            $redeemCode->received_amount = $checkAmountValidate['received_amount'];
            $redeemCode->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $redeemCode->note = $purifiedData->note;
            $redeemCode->email = null;
            $redeemCode->status = 0;
            $redeemCode->utr = 'R' . strRandomNum(10);
            $redeemCode->save();
            return redirect(route('user.redeem.confirm', $redeemCode->utr))->with('success', 'Redeem code initiated successfully');
        }
    }

    public function confirmGenerate(Request $request, $utr)
    {
        $user = Auth::user();
        $redeemCode = RedeemCode::where('utr', $utr)->firstOrFail();

        if (!$redeemCode || $redeemCode->status != 0) { //Check is transaction found and unpaid
            return redirect(route('user.redeem.initialize'))->with('success', 'Transaction already complete or invalid code');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.redeem.confirm', compact(['utr', 'redeemCode', 'enable_for']));
        } elseif ($request->isMethod('post')) {
            if (in_array('redeem', $enable_for)) {
                $purifiedData = $request->all();
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return back()->withErrors(['security_pin' => 'this is first message'])->withInput();
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('error', 'You have entered an incorrect PIN')->withInput();
                }
            }

            $checkAmountValidate = $this->checkAmountValidate($redeemCode->amount, $redeemCode->currency_id, config('transactionType.redeem'), $redeemCode->charge_from);//4 = redeem
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $sender_wallet = updateWallet($redeemCode->sender_id, $redeemCode->currency_id, $redeemCode->transfer_amount, 0);
            $remark = 'Balance debited from redeem code';
            BasicService::makeTransaction($redeemCode->sender, $redeemCode->currency_id, $redeemCode->transfer_amount,
                $redeemCode->charge_from == 1 ? 0 : $redeemCode->charge,
                '-', $redeemCode->utr, $remark, $redeemCode->id, RedeemCode::class);

            $redeemCode->status = 1;
            $redeemCode->save();

            $receivedUser = $user;
            $params = [
                'amount' => getAmount($redeemCode->amount),
                'currency' => optional($redeemCode->currency)->code,
                'transaction' => $redeemCode->utr,
            ];

            $action = [
                "name" => optional($redeemCode->sender)->fullname,
                "image" => getFile(optional($redeemCode->sender)->image_driver, optional($redeemCode->sender)->image),
                "link" => route('user.redeem.index'),
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = route('user.redeem.index');
            $this->sendMailSms($receivedUser, 'REDEEM_CODE_GENERATE', $params);
            $this->userPushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'REDEEM_CODE_GENERATE', $params, $firebaseAction);

            return to_route('user.confirm.success')->with([
                'message' => __("Your redeem code has been generated, your remaining amount of money: :amount", ['amount' => $sender_wallet]),
                'next_route' => route('user.redeem.index'),
                'next_text' => __('View Redeem List')
            ]);

        }
    }

    public function checkAmount(Request $request)
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
