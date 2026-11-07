<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    use Notify;

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
        $data['transfers'] = collect(Transfer::selectRaw('COUNT(id) AS totalTransfer')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completeTransfer')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completeTransferPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingTransfer')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingTransferPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayTransfer')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayTransferPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthTransfer')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthTransferPercentage')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', '=', $userId);
                $query->orWhere('receiver_id', '=', $userId);
            })
            ->get()
            ->toArray())->collapse();

        return view('user.transfer.index', $data);
    }

    public function search(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterTrxId = $request->filter_trx_id;
        $filterCurrency = $request->filter_currency;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = Transfer::query()
            ->with(['sender', 'receiver', 'currency'])
            ->visibleToUser(auth()->id())
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
            ->addColumn('type', function ($item) {
                return renderAmountTypeIcon($item->sender_id);
            })
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount, $item->currency_id);
                return '
            <span class="amount-highlight">' . $amount . '</span>';
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
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Success') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
                }
            })
            ->addColumn('transfer_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                if (!$item->status) {
                    $viewRoute = route('user.transfer.confirm', $item->utr);
                    return "<a href='" . $viewRoute . "' class='btn btn-soft-primary btn-xs'>
                                <i class='bi-check-circle me-1'></i> " . trans('Confirm') . "
                            </a>";
                } else {
                    return '-';
                }
            })
            ->rawColumns(['type', 'transaction_id', 'amount', 'charge', 'charge_pay', 'participant', 'status', 'transfer_at', 'action'])
            ->make(true);
    }


    public function initialize(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();

            return view('user.transfer.create', $data);
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();

            $validationRules = [
                'recipient' => 'required|min:4',
                'amount' => 'required|numeric|min:0|not_in:0',
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
            $recipient = $purifiedData->recipient;
            $charge_from = isset($purifiedData->charge_from);

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.transfer'), $charge_from);//1 = transfer

            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return back()->withInput()->with('error', $checkRecipientValidate['message']);
            }
            $receiver = $checkRecipientValidate['receiver'];
            $transfer = new Transfer();
            $transfer->sender_id = Auth::id();
            $transfer->receiver_id = $receiver->id;
            $transfer->currency_id = $checkAmountValidate['currency_id'];
            $transfer->percentage = $checkAmountValidate['percentage'];
            $transfer->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
            $transfer->charge_fixed = $checkAmountValidate['fixed_charge'];
            $transfer->charge = $checkAmountValidate['charge'];
            $transfer->amount = $checkAmountValidate['amount'];
            $transfer->transfer_amount = $checkAmountValidate['transfer_amount'];
            $transfer->received_amount = $checkAmountValidate['received_amount'];
            $transfer->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $transfer->note = $purifiedData->note;
            $transfer->email = $receiver->email;
            $transfer->status = 0;// 1 = success, 0 = pending
            $transfer->utr = 'T';
            $transfer->save();

            return redirect(route('user.transfer.confirm', $transfer->utr))->with('success', 'Transfer initiated successfully');
        }
    }

    public function confirmTransfer(Request $request, $utr)
    {
        $user = Auth::user();

        $transfer = Transfer::with(['sender', 'receiver', 'currency'])
            ->where('utr', $utr)
            ->where('sender_id', $user->id)
            ->first();
        if (!$transfer || $transfer->status) {
            $message = !$transfer ? 'Unauthorized or invalid transaction.' : 'Transaction already complete.';
            return redirect()->route('user.transfer.index')->with('error', $message);
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.transfer.confirm', compact(['utr', 'transfer', 'enable_for']));
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

            $checkAmountValidate = $this->checkAmountValidate($transfer->amount, $transfer->currency_id, config('transactionType.transfer'), $transfer->charge_from);//1 = transfer
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }
            $checkRecipientValidate = $this->checkRecipientValidate($transfer->email);
            if (!$checkRecipientValidate['status']) {
                return back()->withInput()->with('error', $checkRecipientValidate['message']);
            }
            DB::beginTransaction();
            try {
                /*Deduct money from Sender Wallet */
                $sender_wallet = updateWallet($transfer->sender_id, $transfer->currency_id, $transfer->transfer_amount, 0);
                $remark = 'Balance debited from transfer';
                BasicService::makeTransaction($transfer->sender, $transfer->currency_id, $transfer->transfer_amount,
                    $transfer->charge_from == 1 ? 0 : $transfer->charge,
                    '-', $transfer->utr, $remark, $transfer->id, Transfer::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($transfer->receiver_id, $transfer->currency_id, $transfer->received_amount, 1);
                $remark = 'Balance credited from transfer';
                BasicService::makeTransaction($transfer->receiver, $transfer->currency_id, $transfer->received_amount,
                    $transfer->charge_from == 1 ? $transfer->charge : 0,
                    '+', $transfer->utr, $remark, $transfer->id, Transfer::class);

                $transfer->status = 1;
                $transfer->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }

            $transfer->notifyUsersOnTransfer($user);

            return to_route('user.confirm.success')->with([
                'message' => __("Your transfer has been submitted. Your remaining amount of money: :amount", ['amount' => $sender_wallet]),
                'next_route' => route('user.transfer.index'),
                'next_text' => __('View Transfer List')
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
        $receiver = User::where('username', $recipient)
            ->byType('user')
            ->orWhere('email', $recipient)
            ->first();

        if ($receiver && $receiver->id == Auth::id()) {
            $data['status'] = false;
            $data['message'] = 'Transfer not allowed to self email';
        } elseif ($receiver && $receiver->id != Auth::id()) {
            $data['status'] = true;
            $data['message'] = "User found. Are you looking for $receiver->fullname ?";
            $data['receiver'] = $receiver;
        } else {
            $data['status'] = false;
            $data['message'] = 'No user found';
        }
        return $data;
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

    public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from)
    {
        $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $currency_id, 'transaction_type_id' => $transaction_type_id, 'is_active' => 1])->first();
        $wallet = Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);

        $limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

        $balance = getAmount($wallet->balance, $limit);
        $amount = getAmount($amount, $limit);
        $status = false;
        $charge = 0;
        $min_limit = 0;
        $max_limit = 0;
        $fixed_charge = 0;
        $percentage = 0;
        $percentage_charge = 0;

        if ($chargesLimit) {
            $percentage = getAmount($chargesLimit->percentage_charge, $limit);
            $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
            $fixed_charge = getAmount($chargesLimit->fixed_charge, $limit);
            $min_limit = getAmount($chargesLimit->min_limit, $limit);
            $max_limit = getAmount($chargesLimit->max_limit, $limit);
            $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        }

        if ($charge_from) {
            $transfer_amount = $amount;
            $received_amount = getAmount($amount - $charge, $limit);
        } else {
            $transfer_amount = getAmount($amount + $charge, $limit);
            $received_amount = $amount;
        }

        $remaining_balance = getAmount($balance - $transfer_amount, $limit);

        if ($wallet->is_active != 1) {
            $message = 'Currency not available for this transfer';
        } elseif ($amount < $min_limit || $amount > $max_limit) {
            $message = "minimum payment $min_limit and maximum payment limit $max_limit";
        } elseif ($transfer_amount > $balance) {
            $message = 'Does not have enough money to transfer';
        } else {
            $status = true;
            $message = "Remaining balance : $remaining_balance";
        }

        $data['status'] = $status;
        $data['message'] = $message;
        $data['fixed_charge'] = $fixed_charge;
        $data['percentage'] = $percentage;
        $data['percentage_charge'] = $percentage_charge;
        $data['min_limit'] = $min_limit;
        $data['max_limit'] = $max_limit;
        $data['balance'] = $balance;
        $data['transfer_amount'] = $transfer_amount;
        $data['received_amount'] = $received_amount;
        $data['remaining_balance'] = $remaining_balance;
        $data['charge'] = $charge;
        $data['charge_from'] = $charge_from;
        $data['amount'] = $amount;
        $data['currency_id'] = $currency_id;
        $data['currency_limit'] = $limit;
        return $data;
    }
}
