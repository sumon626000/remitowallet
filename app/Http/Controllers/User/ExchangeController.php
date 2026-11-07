<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\TwoFactorSetting;
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

class ExchangeController extends Controller
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


    public function initialize(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return view('user.exchange.create', $data);
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();
            $validationRules = [
                'from_wallet' => 'required|integer|min:1|not_in:0',
                'to_wallet' => 'required|integer|min:1|not_in:0',
                'amount' => 'required|numeric|min:0|not_in:0',
            ];
            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;

            $userId = Auth::id();
            $fromCurrencyId = $purifiedData->from_wallet;
            $toCurrencyId = $purifiedData->to_wallet;
            $amount = $purifiedData->amount;

            $checkAmountValidate = $this->checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount);

            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $exchange = new Exchange();
            $exchange->user_id = $userId;
            $exchange->from_wallet = $checkAmountValidate['from_wallet'];
            $exchange->to_wallet = $checkAmountValidate['to_wallet'];
            $exchange->percentage = $checkAmountValidate['percentage'];
            $exchange->charge_percentage = $checkAmountValidate['charge_percentage'];
            $exchange->charge_fixed = $checkAmountValidate['charge_fixed'];
            $exchange->charge = $checkAmountValidate['charge'];
            $exchange->exchange_rate = $checkAmountValidate['exchange_rate'];
            $exchange->amount = $checkAmountValidate['amount'];
            $exchange->transfer_amount = $checkAmountValidate['transfer_amount'];
            $exchange->received_amount = $checkAmountValidate['received_amount'];
            $exchange->utr = 'E';
            $exchange->status = 0; //pending
            $exchange->save();
            return redirect(route('user.exchange.confirm', $exchange->utr))->with('success', 'Exchange initiated successfully');
        }
    }

    public function confirmExchange(Request $request, $utr)
    {
        $user = Auth::user();
        $exchange = Exchange::with(['fromWallet', 'toWallet'])->where('utr', $utr)->firstOrFail();

        if (!$exchange || $exchange->status) { //Check is exchange found and unpaid
            return redirect(route('user.exchange.initialize'))->with('success', 'Exchange already complete');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.exchange.confirm', compact(['utr', 'exchange', 'enable_for']));
        } elseif ($request->isMethod('post')) {
            if (in_array('exchange', $enable_for)) {
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

            $checkAmountValidate = $this->checkAmountValidate($exchange->user_id, optional($exchange->fromWallet)->currency_id, optional($exchange->toWallet)->currency_id, $exchange->amount);
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            DB::beginTransaction();
            try {
                /*Deduct money from Wallet */
                $sender_wallet = updateWallet($exchange->user_id, optional($exchange->fromWallet)->currency_id, $exchange->transfer_amount, 0);
                $remark = 'Balance debited from exchange money';
                BasicService::makeTransaction($user, optional($exchange->fromWallet)->currency_id, $exchange->transfer_amount,
                    $exchange->charge, '-', $exchange->utr, $remark, $exchange->id, Exchange::class);

                /*Add money to receiver wallet */
                $receiver_wallet = updateWallet($exchange->user_id, optional($exchange->toWallet)->currency_id, $exchange->received_amount, 1);
                $remark = 'Balance credited from exchange money';
                BasicService::makeTransaction($user, optional($exchange->toWallet)->currency_id, $exchange->received_amount,
                    0, '+', $exchange->utr, $remark, $exchange->id, Exchange::class);

                $exchange->status = 1;
                $exchange->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }

            $receivedUser = $user;
            $params = [
                'from_amount' => getAmount($exchange->transfer_amount),
                'from_currency' => optional(optional($exchange->fromWallet)->currency)->code,
                'to_amount' => getAmount($exchange->received_amount),
                'to_currency' => optional(optional($exchange->toWallet)->currency)->code,
                'transaction' => $exchange->utr,
            ];

            $action = [
                "name" => $user->name,
                "image" => getFile($user->image_driver, $user->image),
                "link" => route('user.exchange.index'),
                "icon" => "fa-light fa-bell-on text-white"
            ];
            $firebaseAction = route('user.exchange.index');
            $this->sendMailSms($receivedUser, 'MONEY_EXCHANGE', $params);
            $this->userPushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $action);
            $this->userFirebasePushNotification($receivedUser, 'MONEY_EXCHANGE', $params, $firebaseAction);

            return to_route('user.confirm.success')->with([
                'message' => __("Your exchange has been completed successfully!"),
                'next_route' => route('user.exchange.index'),
                'next_text' => __('View Exchange List')
            ]);
        }
    }

    public function currenciesExceptSelected(Request $request)
    {
        $from_wallet = $request->from_wallet;
        $to_wallet = Currency::select('id', 'code', 'name')->where('id', '!=', $from_wallet)->where(['is_active' => 1])->get();

        $data['status'] = true;
        $data['message'] = '';
        $data['to_wallet'] = $to_wallet;

        return $data;
    }

    public function checkAmount(Request $request)
    {
        $userId = Auth::id();
        $fromCurrencyId = $request->from_wallet;
        $toCurrencyId = $request->to_wallet;
        $amount = $request->amount;

        $data = $this->checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount);
        return response()->json($data);
    }

    public function checkAmountValidate($userId, $fromCurrencyId, $toCurrencyId, $amount)
    {
        $defaultCurrency = Currency::where('code', basicControl()->base_currency)->first();
        $defaultCurrencyRate = $defaultCurrency->exchange_rate;

        $fromWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $fromCurrencyId])->first();
        $toWallet = Wallet::with('currency')->where(['user_id' => $userId, 'currency_id' => $toCurrencyId])->first();
        $toLimit = optional($toWallet->currency)->currency_type == 0 ? 8 : 2;
        $chargesLimit = ChargesLimit::with('currency')->where(['currency_id' => $fromCurrencyId, 'transaction_type_id' => config('transactionType.exchange'), 'is_active' => 1])->first();
        $limit = optional($chargesLimit->currency)->currency_type == 0 ? 8 : 2;

        $amount = getAmount($amount, $limit);
        $status = false;
        $percentage = 0;
        $chargeFixed = 0;
        $chargePercentage = 0;
        $charge = 0;
        $minLimit = 0;
        $maxLimit = 0;

        if ($chargesLimit) {
            $percentage = getAmount($chargesLimit->percentage_charge, $limit);
            $chargeFixed = getAmount($chargesLimit->fixed_charge, $limit);
            $chargePercentage = getAmount(($amount * $percentage) / 100, $limit);
            $charge = getAmount($chargePercentage + $chargeFixed, $limit);
            $minLimit = getAmount($chargesLimit->min_limit, $limit);
            $maxLimit = getAmount($chargesLimit->max_limit, $limit);
        }

        $fromExchangeRate = getAmount($fromWallet->currency->exchange_rate, $limit);
        $toExchangeRate = getAmount($toWallet->currency->exchange_rate, $toLimit);
        $exchangeRate = getAmount(($defaultCurrencyRate / $fromExchangeRate) * $toExchangeRate, $toLimit);

        $transferAmount = getAmount($amount + $charge, $limit);
        $receivedAmount = getAmount($amount * $exchangeRate, $toLimit);

        $fromWalletBalance = getAmount($fromWallet->balance, $limit);
        $fromWalletUpdateBalance = getAmount($fromWalletBalance - $transferAmount, $limit);
        $toWalletUpdateBalance = getAmount($toWallet->balance + $receivedAmount, $toLimit);

        if ($amount < $minLimit || $amount > $maxLimit) {
            $message = "minimum transfer $minLimit and maximum transfer limit $maxLimit";
        } elseif ($transferAmount > $fromWalletBalance) {
            $message = 'Does not have enough money to cover transfer';
        } else {
            $status = true;
            $message = "Remaining balance : $fromWalletUpdateBalance " . optional($fromWallet->currency)->code;
        }

        $data = [
            'balance' => $fromWalletBalance,
            'user_id' => $userId,
            'from_wallet' => $fromWallet->id,
            'to_wallet' => $toWallet->id,
            'percentage' => $percentage,
            'charge_percentage' => $chargePercentage,
            'charge_fixed' => $chargeFixed,
            'charge' => $charge,
            'exchange_rate' => $exchangeRate,
            'amount' => $amount,
            'transfer_amount' => $transferAmount,
            'received_amount' => $receivedAmount,
            'status' => $status,
            'message' => $message,
            'fromWalletUpdateBalance' => $fromWalletUpdateBalance,
            'toWalletUpdateBalance' => $toWalletUpdateBalance,
            'min_limit' => $minLimit,
            'max_limit' => $maxLimit,
            'currency_limit' => $limit,
        ];

        return $data;
    }

    public function index()
    {
        $userId = Auth::id();
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['exchanges'] = collect(Exchange::selectRaw('COUNT(id) AS totalExchange')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS completeExchange')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS completeExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingExchange')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayExchange')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayExchangePercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthExchange')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthExchangePercentage')
            ->where('user_id', $userId)
            ->get()
            ->toArray())->collapse();

        return view('user.exchange.index', $data);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterFromCurrency = $request->filter_currency_from;
        $filterToCurrency = $request->filter_currency_to;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $exchanges = Exchange::with(['fromWallet', 'toWallet', 'fromWallet.currency', 'toWallet.currency', 'user'])
            ->where('user_id', $userId)->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('utr', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterFromCurrency), function ($query) use ($filterFromCurrency) {
                if ($filterFromCurrency != "all") {
                    return $query->WhereHas('fromWallet', function ($q) use ($filterFromCurrency) {
                        $q->where('currency_id', $filterFromCurrency);
                    });
                }
            })
            ->when(isset($filterToCurrency), function ($query) use ($filterToCurrency) {
                if ($filterToCurrency != "all") {
                    return $query->WhereHas('toWallet', function ($q) use ($filterToCurrency) {
                        $q->where('currency_id', $filterToCurrency);
                    });
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
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($exchanges)
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('exchange', function ($item) {
                $fromAmount = currencyPosition($item->amount, $item->fromWallet->currency_id) ?? 'N/A';
                $toAmount = currencyPosition($item->received_amount, $item->toWallet->currency_id) ?? 'N/A';
                return '<span class="amount-highlight">' . $fromAmount . '</span>
                        <i class="bi-arrow-left-right"></i> <span class="amount-highlight">' . $toAmount . '</span>';
            })
            ->addColumn('exchange_rate', function ($item) {
                $rate = currencyPosition($item->exchange_rate, $item->toWallet->currency_id) ?? 'N/A';
                return "<span class=''>$rate</span>";
            })
            ->addColumn('charge', function ($item) {
                $charge = currencyPosition($item->charge, $item->fromWallet->currency_id) ?? 'N/A';
                return "<span class='text-danger'>$charge</span>";
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
            ->addColumn('exchange_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                if (!$item->status) {
                    $viewRoute = route('user.exchange.confirm', $item->utr);

                    return "
                      <a href='" . $viewRoute . "' class='btn btn-soft-primary btn-xs'>
                        <i class='bi-check-circle me-1'></i> " . trans('Confirm') . "
                      </a>";
                } else {
                    return '-';
                }
            })
            ->rawColumns(['transaction_id', 'exchange', 'exchange_rate', 'charge', 'status', 'exchange_at', 'action'])
            ->make(true);
    }
}
