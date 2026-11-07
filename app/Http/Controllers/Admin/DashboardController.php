<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionEntry;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Escrow;
use App\Models\Exchange;
use App\Models\Fund;
use App\Models\Invoice;
use App\Models\Payout;
use App\Models\QRCode;
use App\Models\RedeemCode;
use App\Models\RequestMoney;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserKyc;
use App\Models\UserLogin;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Traits\Notify;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use App\Traits\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    use Upload, Notify;

    public function index()
    {
        $last30 = date('Y-m-d', strtotime('-30 days'));
        $last7 = date('Y-m-d', strtotime('-7 days'));

        $today = today();
        $dayCount = date('t', strtotime($today));

        $data['basic'] = basicControl();
        $data['firebaseNotify'] = config('firebase');
        $data['latestUser'] = User::whereIn('type', getAllowedUserTypes())->latest()->limit(5)->get();
        $statistics['schedule'] = $this->dayList();

        $data['wallets'] = Wallet::with('currency:id,name,code,symbol,logo,driver')
            ->groupBy('currency_id')
            ->selectRaw('SUM(balance) as totalBalance, currency_id')
            ->get();


        $transferCacheKey = 'transfer_overview_' . now()->toDateString();
        $transfer = Cache::remember($transferCacheKey, 600, function () use ($last7){
            return Transfer::where('status', 1)
                ->with('currency:id,code,exchange_rate')
                ->whereHas('currency')
                ->selectRaw("
                    SUM(amount) as total_transfer,
                    SUM(charge) as total_profit,
                    SUM(CASE WHEN created_at >= ? THEN amount END) as transfer_7_days,
                    SUM(CASE WHEN created_at >= ? THEN charge END) as profit_7_days
                ", [$last7, $last7])
                ->groupBy('currency_id')
                ->get()
                ->map(function ($item) {
                    $exchangeRate = $item->currency ? $item->currency->exchange_rate : 1;
                    return [
                        'total_transfer' => $item->total_transfer / $exchangeRate,
                        'total_profit' => $item->total_profit / $exchangeRate,
                        'transfer_7_days' => $item->transfer_7_days / $exchangeRate,
                        'profit_7_days' => $item->profit_7_days / $exchangeRate,
                    ];
                });
        });
        $data['transfer'] = [
            'total_transfer' => $transfer->sum('total_transfer'),
            'total_profit' => $transfer->sum('total_profit'),
            'transfer_7_days' => $transfer->sum('transfer_7_days'),
            'profit_7_days' => $transfer->sum('profit_7_days'),
        ];


        $requestMoneyCacheKey = 'request_money_overview_' . now()->toDateString();
        $requestMoney = Cache::remember($requestMoneyCacheKey, 600, function () use ($last30, $last7) {
            return RequestMoney::where('status', 1)
                ->join('currencies', 'currencies.id', '=', 'request_money.currency_id')
                ->selectRaw("
                    SUM(request_money.amount) as total_request_money,
                    SUM(request_money.charge) as total_profit,
                    SUM(CASE WHEN request_money.created_at >= ? THEN request_money.amount END) as request_money_7_days,
                    SUM(CASE WHEN request_money.created_at >= ? THEN request_money.charge END) as profit_7_days,
                    currencies.exchange_rate
                ", [$last7, $last7])
                ->groupBy('request_money.currency_id', 'currencies.exchange_rate')
                ->get()
                ->map(function ($item) {
                    $exchangeRate = $item->exchange_rate ?: 1;
                    return [
                        'total_request_money' => $item->total_request_money / $exchangeRate,
                        'total_profit' => $item->total_profit / $exchangeRate,
                        'request_money_7_days' => $item->request_money_7_days / $exchangeRate,
                        'profit_7_days' => $item->profit_7_days / $exchangeRate,
                    ];
                });
        });
        $data['requestMoney'] = $requestMoney->mapWithKeys(function ($item) {
            return [
                'total_request_money' => $item['total_request_money'],
                'total_profit' => $item['total_profit'],
                'request_money_7_days' => $item['request_money_7_days'],
                'profit_7_days' => $item['profit_7_days'],
            ];
        });


        $redeemCodeCacheKey = 'redeem_code_overview_' . now()->toDateString();
        $redeemCode = Cache::remember($redeemCodeCacheKey, 600, function () use ($last30) {
            return RedeemCode::whereNot('redeem_codes.status', 0)
                ->join('currencies', 'redeem_codes.currency_id', '=', 'currencies.id')
                ->selectRaw("
                    SUM(redeem_codes.amount) / COALESCE(MAX(currencies.exchange_rate), 1) as total_redeem,
                    SUM(redeem_codes.charge) / COALESCE(MAX(currencies.exchange_rate), 1) as total_redeem_profit
                ")->first();
        });
        $data['redeemCode'] = [
            'total_redeem' => $redeemCode->total_redeem ?? 0,
            'total_redeem_profit' => $redeemCode->total_redeem_profit ?? 0,
        ];

        $voucher = Voucher::query()
            ->whereIn('status',[2])
            ->with('currency:id,name,code,logo,symbol,exchange_rate')
            ->groupBy('currency_id')
            ->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN amount END)) as voucher_30_days, currency_id")
            ->selectRaw("SUM((CASE WHEN created_at >= $last30 THEN charge END)) as voucher_income_30_days, currency_id")
            ->get()
            ->map(function ($item) {
                return [
                    'voucher_30_days' => $item->voucher_30_days / $item->currency->exchange_rate,
                    'voucher_income_30_days' => $item->voucher_income_30_days / $item->currency->exchange_rate,
                ];
            });
        $data['voucher'] = [
            'voucher_30_days' => $voucher->sum('voucher_30_days'),
            'voucher_income_30_days' => $voucher->sum('voucher_income_30_days'),
        ];

        return view('admin.dashboard-alternative', $data, compact("statistics"));
    }

    public function monthlyDepositWithdraw(Request $request)
    {
        $keyDataset = $request->keyDataset;
        $dailyDeposit = $this->dayList();

        Deposit::when($keyDataset == '0', function ($query) {
            $query->whereMonth('created_at', Carbon::now()->month);
        })
            ->when($keyDataset == '1', function ($query) {
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('created_at', $lastMonth->month);
            })
            ->select(
                DB::raw('SUM(payable_amount_in_base_currency) as totalDeposit'),
                DB::raw('DATE_FORMAT(created_at,"Day %d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()->map(function ($item) use ($dailyDeposit) {
                $dailyDeposit->put($item['date'], $item['totalDeposit']);
            });

        return response()->json([
            "totalDeposit" => currencyPosition($dailyDeposit->sum()),
            "dailyDeposit" => $dailyDeposit,
        ]);
    }

    public function saveToken(Request $request)
    {
        $admin = Auth::guard('admin')->user()
            ->fireBaseToken()
            ->create([
                'token' => $request->token,
            ]);
        return response()->json([
            'msg' => 'token saved successfully.',
        ]);
    }

    public function dayList()
    {
        $totalDays = Carbon::now()->endOfMonth()->format('d');
        $daysByMonth = [];
        for ($i = 1; $i <= $totalDays; $i++) {
            array_push($daysByMonth, ['Day ' . sprintf("%02d", $i) => 0]);
        }

        return collect($daysByMonth)->collapse();
    }

    protected function followupGrap($todaysRecords, $lastDayRecords = 0)
    {
        if (0 < $lastDayRecords) {
            $percentageIncrease = (($todaysRecords - $lastDayRecords) / $lastDayRecords) * 100;
        } else {
            $percentageIncrease = 0;
        }
        if ($percentageIncrease > 0) {
            $class = "bg-soft-success text-success";
        } elseif ($percentageIncrease < 0) {
            $class = "bg-soft-danger text-danger";
        } else {
            $class =  "bg-soft-secondary text-body";
        }
        return [
            'class' => $class,
            'percentage' => round($percentageIncrease, 2)
        ];
    }


    public function chartUserRecords()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $userRecord = collect(User::selectRaw('COUNT(id) AS totalUsers')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS currentDateUserCount')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) THEN id END) AS previousDateUserCount')
            ->get()->makeHidden(['last-seen-activity', 'fullname'])
            ->toArray())->collapse();
        $followupGrap = $this->followupGrap($userRecord['currentDateUserCount'], $userRecord['previousDateUserCount']);

        $userRecord->put('followupGrapClass', $followupGrap['class']);
        $userRecord->put('followupGrap', $followupGrap['percentage']);

        $current_month_data = DB::table('users')
            ->select(DB::raw('DATE_FORMAT(created_at,"%e %b") as date'), DB::raw('count(*) as count'))
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $currentMonth)
            ->orderBy('created_at', 'asc')
            ->groupBy('date')
            ->get();

        $current_month_data_dates = $current_month_data->pluck('date');
        $current_month_datas = $current_month_data->pluck('count');
        $userRecord['chartPercentageIncDec'] = fractionNumber($userRecord['totalUsers'] - $userRecord['currentDateUserCount'], false);
        return response()->json(['userRecord' => $userRecord, 'current_month_data_dates' => $current_month_data_dates, 'current_month_datas' => $current_month_datas]);
    }

    public function chartTicketRecords()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $ticketRecord = collect(SupportTicket::selectRaw('COUNT(id) AS totalTickets')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS currentDateTicketsCount')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) THEN id END) AS previousDateTicketsCount')
            ->selectRaw('count(CASE WHEN status = 2  THEN status END) AS replied')
            ->selectRaw('count(CASE WHEN status = 1  THEN status END) AS answered')
            ->selectRaw('count(CASE WHEN status = 0  THEN status END) AS pending')
            ->get()
            ->toArray())->collapse();

        $followupGrap = $this->followupGrap($ticketRecord['currentDateTicketsCount'], $ticketRecord['previousDateTicketsCount']);
        $ticketRecord->put('followupGrapClass', $followupGrap['class']);
        $ticketRecord->put('followupGrap', $followupGrap['percentage']);

        $current_month_data = DB::table('support_tickets')
            ->select(DB::raw('DATE_FORMAT(created_at,"%e %b") as date'), DB::raw('count(*) as count'))
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $currentMonth)
            ->orderBy('created_at', 'asc')
            ->groupBy('date')
            ->get();

        $current_month_data_dates = $current_month_data->pluck('date');
        $current_month_datas = $current_month_data->pluck('count');
        $ticketRecord['chartPercentageIncDec'] = fractionNumber($ticketRecord['totalTickets'] - $ticketRecord['currentDateTicketsCount'], false);
        return response()->json(['ticketRecord' => $ticketRecord, 'current_month_data_dates' => $current_month_data_dates, 'current_month_datas' => $current_month_datas]);
    }

    public function chartKycRecords()
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $kycRecords = collect(UserKyc::selectRaw('COUNT(id) AS totalKYC')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS currentDateKYCCount')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) THEN id END) AS previousDateKYCCount')
            ->selectRaw('count(CASE WHEN status = 0  THEN status END) AS pendingKYC')
            ->get()
            ->toArray())->collapse();
        $followupGrap = $this->followupGrap($kycRecords['currentDateKYCCount'], $kycRecords['previousDateKYCCount']);
        $kycRecords->put('followupGrapClass', $followupGrap['class']);
        $kycRecords->put('followupGrap', $followupGrap['percentage']);


        $current_month_data = DB::table('user_kycs')
            ->select(DB::raw('DATE_FORMAT(created_at,"%e %b") as date'), DB::raw('count(*) as count'))
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $currentMonth)
            ->orderBy('created_at', 'asc')
            ->groupBy('date')
            ->get();

        $current_month_data_dates = $current_month_data->pluck('date');
        $current_month_datas = $current_month_data->pluck('count');
        $kycRecords['chartPercentageIncDec'] = fractionNumber($kycRecords['totalKYC'] - $kycRecords['currentDateKYCCount'], false);
        return response()->json(['kycRecord' => $kycRecords, 'current_month_data_dates' => $current_month_data_dates, 'current_month_datas' => $current_month_datas]);
    }

    public function chartTransactionRecords()
    {
        $currentMonth = Carbon::now()->format('Y-m');

        $transaction = collect(Transaction::selectRaw('COUNT(id) AS totalTransaction')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS currentDateTransactionCount')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) THEN id END) AS previousDateTransactionCount')
            ->whereRaw('YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())')
            ->get()
            ->toArray())
            ->collapse();

        $followupGrap = $this->followupGrap($transaction['currentDateTransactionCount'], $transaction['previousDateTransactionCount']);
        $transaction->put('followupGrapClass', $followupGrap['class']);
        $transaction->put('followupGrap', $followupGrap['percentage']);


        $current_month_data = DB::table('transactions')
            ->select(DB::raw('DATE_FORMAT(created_at,"%e %b") as date'), DB::raw('count(*) as count'))
            ->where(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), $currentMonth)
            ->orderBy('created_at', 'asc')
            ->groupBy('date')
            ->get();

        $current_month_data_dates = $current_month_data->pluck('date');
        $current_month_datas = $current_month_data->pluck('count');
        $transaction['chartPercentageIncDec'] = fractionNumber($transaction['totalTransaction'] - $transaction['currentDateTransactionCount'], false);
        return response()->json(['transactionRecord' => $transaction, 'current_month_data_dates' => $current_month_data_dates, 'current_month_datas' => $current_month_datas]);
    }


    public function chartBrowserHistory(Request $request)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $userLoginsData = DB::table('user_logins')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('browser', 'os', 'get_device')
            ->get();

        $userLoginsBrowserData = $userLoginsData->groupBy('browser')->map->count();
        $data['browserKeys'] = $userLoginsBrowserData->keys();
        $data['browserValue'] = $userLoginsBrowserData->values();

        return response()->json(['browserPerformance' => $data]);
    }

    public function chartOsHistory(Request $request)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $userLoginsData = DB::table('user_logins')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('browser', 'os', 'get_device')
            ->get();

        $userLoginsOSData = $userLoginsData->groupBy('os')->map->count();
        $data['osKeys'] = $userLoginsOSData->keys();
        $data['osValue'] = $userLoginsOSData->values();

        return response()->json(['osPerformance' => $data]);
    }

    public function chartDeviceHistory(Request $request)
    {
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        $userLoginsData = DB::table('user_logins')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('browser', 'os', 'get_device')
            ->get();

        $userLoginsDeviceData = $userLoginsData->groupBy('get_device')->map->count();
        $data['deviceKeys'] = $userLoginsDeviceData->keys();
        $data['deviceValue'] = $userLoginsDeviceData->values();

        return response()->json(['deviceHistory' => $data]);
    }



    public function getDepositChart(Request $request)
    {
        $start = $request->start;
        $end = $request->end ?? $start;

        $dailyDeposit = collect();

        Deposit::query()
            ->where('status',1)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(payable_amount_in_base_currency) as totalDeposit'),
                DB::raw('DATE_FORMAT(created_at,"%Y-%m-%d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->each(function ($item) use ($dailyDeposit) {
                $dailyDeposit->put($item->date, $item->totalDeposit);
            });

        return response()->json([
            "dates" => $dailyDeposit->keys(),
            "totalDeposit" => $dailyDeposit->values()
        ]);
    }

    public function getPayoutChart(Request $request)
    {
        $start = $request->start;
        $end = $request->end ?? $start;

        $dailyPayout = collect();

        Payout::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(amount_in_base_currency) as totalPayout'),
                DB::raw('DATE_FORMAT(created_at,"%Y-%m-%d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->each(function ($item) use ($dailyPayout) {
                $dailyPayout->put($item->date, $item->totalPayout);
            });

        return response()->json([
            "dates" => $dailyPayout->keys(),
            "totalPayout" => $dailyPayout->values()
        ]);
    }

    public function getTrxChart(Request $request)
    {
        $start = $request->start;
        $end = $request->end ?? $start;

        $dailyTransaction = collect();

        Transaction::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(amount) as totalTransaction'),
                DB::raw('DATE_FORMAT(created_at,"%Y-%m-%d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->each(function ($item) use ($dailyTransaction) {
                $dailyTransaction->put($item->date, $item->totalTransaction);
            });

        return response()->json([
            "dates" => $dailyTransaction->keys(),
            "totalTransaction" => $dailyTransaction->values()
        ]);
    }

    public function getCommissionChart(Request $request)
    {
        $start = $request->start;
        $end = $request->end ?? $start;

        $dailyCommission = collect();

        CommissionEntry::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(commission_amount) as totalTransaction'),
                DB::raw('DATE_FORMAT(created_at,"%Y-%m-%d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()
            ->each(function ($item) use ($dailyCommission) {
                $dailyCommission->put($item->date, $item->totalTransaction);
            });

        return response()->json([
            "dates" => $dailyCommission->keys(),
            "totalCommission" => $dailyCommission->values()
        ]);
    }


    public function getUserLocations()
    {
        $countries = config('country') ?? [];

        $locations = UserLogin::selectRaw('country, country_code, latitude, longitude, COUNT(user_id) as total_users')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->groupBy('country', 'country_code', 'latitude', 'longitude')
            ->orderByDesc('total_users')
            ->get()
            ->map(function ($location) use ($countries) {
                $countryData = collect($countries)->firstWhere('code', $location->country_code);
                $flag = $countryData['flag'] ?? null;

                return [
                    "coords" => [floatval($location->latitude), floatval($location->longitude)],
                    "name" => $location->country,
                    "active" => $location->total_users,
                    "new" => rand(1, $location->total_users),
                    "flag" => asset($flag),
                    "code" => $location->country_code
                ];
            });

        return response()->json($locations);
    }


    public function getTransactionChart(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        $transactions = Transaction::select('created_at', 'currency_id')
            ->whereBetween('created_at', [$start, $end])
            ->with(['transactional' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Transfer::class => ['sender', 'receiver', 'currency'],
                    RequestMoney::class => ['sender', 'receiver', 'currency'],
                    RedeemCode::class => ['sender', 'receiver', 'currency'],
                    Escrow::class => ['sender', 'receiver', 'currency'],
                    Voucher::class => ['sender', 'receiver', 'currency'],
                    Invoice::class => ['sender', 'currency'],
                    Deposit::class => ['sender', 'receiver', 'currency'],
                    Exchange::class => ['user', 'currency'],
                    CommissionEntry::class => ['sender', 'receiver', 'currency'],
                    QRCode::class => ['user', 'currency'],
                ]);
            }])
            ->whereHasMorph('transactional',
                [
                    Transfer::class,
                    RequestMoney::class,
                    RedeemCode::class,
                    Escrow::class,
                    Voucher::class,
                    Invoice::class,
                    Deposit::class,
                    Exchange::class,
                    CommissionEntry::class,
                    QRCode::class,
                ])
            ->groupBy([DB::raw("DATE_FORMAT(created_at, '%j')"), 'currency_id'])
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Deposit' THEN amount ELSE 0 END) as Deposit")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Fund' THEN amount ELSE 0 END) as Fund")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Transfer' THEN amount ELSE 0 END) as Transfer")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%RequestMoney' THEN amount ELSE 0 END) as RequestMoney")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Voucher' THEN amount ELSE 0 END) as Voucher")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Invoice' THEN amount ELSE 0 END) as Invoice")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%QRCode' THEN amount ELSE 0 END) as QRPaymentAmount")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Redeem' THEN amount ELSE 0 END) as Redeem")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Escrow' THEN amount ELSE 0 END) as Escrow")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Payout' THEN amount ELSE 0 END) as Payout")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%Exchange' THEN amount ELSE 0 END) as Exchange")
            ->selectRaw("SUM(CASE WHEN transactional_type like '%CommissionEntry' THEN amount ELSE 0 END) as CommissionEntry")
            ->get()
            ->groupBy([function ($query) {
                return $query->created_at->format('j');
            }, 'currency_id']);

        $labels = [];
        $dataDeposit = [];
        $dataFund = [];
        $dataTransfer = [];
        $dataRequestMoney = [];
        $dataVoucher = [];
        $dataInvoice = [];
        $dataQRPaymentAmount = [];
        $dataRedeem = [];
        $dataEscrow = [];
        $dataPayout = [];
        $dataExchange = [];
        $dataDispute = [];
        $dataCommissionEntry = [];
        $start = new DateTime($start);
        $end = new DateTime($end);

        for ($day = $start; $day <= $end; $day->modify('+1 day')) {
            $i = $day->format('j');
            $labels[] = $day->format('jS M');
            $currentDeposit = 0;
            $currentFund = 0;
            $currentTransfer = 0;
            $currentRequestMoney = 0;
            $currentVoucher = 0;
            $currentInvoice = 0;
            $currentQRPaymentAmount = 0;
            $currentRedeem = 0;
            $currentEscrow = 0;
            $currentPayout = 0;
            $currentExchange = 0;
            $currentDispute = 0;
            $currentCommissionEntry = 0;
            if (isset($transactions[$i])) {
                foreach ($transactions[$i] as $key => $transaction) {
                    $currency = Currency::find($key);
                    if ($currency) {
                        $currentDeposit += $transactions[$i][$key][0]->Deposit / $currency->exchange_rate;
                        $currentFund += $transactions[$i][$key][0]->Fund / $currency->exchange_rate;
                        $currentTransfer += $transactions[$i][$key][0]->Transfer / $currency->exchange_rate;
                        $currentRequestMoney += $transactions[$i][$key][0]->RequestMoney / $currency->exchange_rate;
                        $currentVoucher += $transactions[$i][$key][0]->Voucher / $currency->exchange_rate;
                        $currentInvoice += $transactions[$i][$key][0]->Invoice / $currency->exchange_rate;
                        $currentQRPaymentAmount = $transactions[$i][$key][0]->QRPaymentAmount / $currency->exchange_rate;
                        $currentRedeem += $transactions[$i][$key][0]->Redeem / $currency->exchange_rate;
                        $currentEscrow += $transactions[$i][$key][0]->Escrow / $currency->exchange_rate;
                        $currentPayout += $transactions[$i][$key][0]->Payout / $currency->exchange_rate;
                        $currentExchange += $transactions[$i][$key][0]->Exchange / $currency->exchange_rate;
                        $currentDispute += $transactions[$i][$key][0]->Dispute / $currency->exchange_rate;
                        $currentCommissionEntry += $transactions[$i][$key][0]->CommissionEntry / $currency->exchange_rate;
                    }
                }
            }
            $dataDeposit[] = round($currentDeposit, basicControl()->fraction_number);
            $dataFund[] = round($currentFund, basicControl()->fraction_number);
            $dataTransfer[] = round($currentTransfer, basicControl()->fraction_number);
            $dataRequestMoney[] = round($currentRequestMoney, basicControl()->fraction_number);
            $dataVoucher[] = round($currentVoucher, basicControl()->fraction_number);
            $dataInvoice[] = round($currentInvoice, basicControl()->fraction_number);
            $dataQRPaymentAmount[] = round($currentQRPaymentAmount, basicControl()->fraction_number);
            $dataRedeem[] = round($currentRedeem, basicControl()->fraction_number);
            $dataEscrow[] = round($currentEscrow, basicControl()->fraction_number);
            $dataPayout[] = round($currentPayout, basicControl()->fraction_number);
            $dataExchange[] = round($currentExchange, basicControl()->fraction_number);
            $dataDispute[] = round($currentDispute, basicControl()->fraction_number);
            $dataCommissionEntry[] = round($currentCommissionEntry, basicControl()->fraction_number);
        }

        $data['labels'] = $labels;
        $data['dataDeposit'] = $dataDeposit;
        $data['dataFund'] = $dataFund;
        $data['dataTransfer'] = $dataTransfer;
        $data['dataRequestMoney'] = $dataRequestMoney;
        $data['dataVoucher'] = $dataVoucher;
        $data['dataInvoice'] = $dataInvoice;
        $data['dataQRPaymentAmount'] = $dataQRPaymentAmount;
        $data['dataRedeem'] = $dataRedeem;
        $data['dataEscrow'] = $dataEscrow;
        $data['dataPayout'] = $dataPayout;
        $data['dataExchange'] = $dataExchange;
        $data['dataDispute'] = $dataDispute;
        $data['dataCommissionEntry'] = $dataCommissionEntry;

        return response()->json($data);
    }

}
