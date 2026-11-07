<?php

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\QRCode;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Wallet;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;


class HomeController extends Controller
{
    use Upload;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function saveToken(Request $request)
    {
        try {
            Auth::user()
                ->fireBaseToken()
                ->create([
                    'token' => $request->token,
                ]);
            return response()->json([
                'msg' => 'token saved successfully.',
            ]);
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function index()
    {
        $user = Auth::user();
        $data['basic'] = basicControl();
        $data['firebaseNotify'] = config('firebase');

        $data['wallets'] = Wallet::with(['currency:id,name,code,symbol,logo,driver'])
            ->select('id', 'balance as totalBalance', 'user_id', 'currency_id')
            ->where('user_id', $user->id)
            ->customOrder()
            ->get();

        $data['transfers'] = Transfer::with([
            'sender:id,username,firstname,lastname,image,image_driver',
            'receiver:id,username,firstname,lastname,image,image_driver',
            'currency:id,code,exchange_rate'
        ])
            ->visibleToUser($user->id)
            ->where('status', 1)
            ->latest('id')
            ->limit(4)
            ->get();

        $transferSummary = Transfer::select(
            DB::raw('currency_id'),
            DB::raw('SUM(amount) as total_transfer'),
            DB::raw('SUM(CASE WHEN sender_id = '.$user->id.' THEN amount ELSE 0 END) as total_sent'),
            DB::raw('SUM(CASE WHEN receiver_id = '.$user->id.' THEN amount ELSE 0 END) as total_received')
        )
            ->where('status', 1)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->with('currency:id,exchange_rate')
            ->groupBy('currency_id')
            ->get();

        $transfer = $transferSummary->map(function ($item) {
            $exchangeRate = $item->currency?->exchange_rate ?: 1;
            return [
                'total_transfer' => $item->total_transfer / $exchangeRate,
                'total_sent' => $item->total_sent / $exchangeRate,
                'total_received' => $item->total_received / $exchangeRate,
            ];
        });

        $data['transfer'] = [
            'total_transfer' => $transfer->sum('total_transfer'),
            'total_sent' => $transfer->sum('total_sent'),
            'total_received' => $transfer->sum('total_received'),
        ];

        $thisYearTotalTransfer = Transfer::with('currency:id,exchange_rate')
            ->where('status', 1)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->whereYear('transfers.created_at', now()->year)
            ->get()
            ->sum(function ($transfer) {
                $exchangeRate = $transfer->currency?->exchange_rate ?: 1;
                return $transfer->amount / $exchangeRate;
            });
        $data['transfer_this_year'] = round($thisYearTotalTransfer, 2);

        $payments = Deposit::where('status', 1)->where('user_id', $user->id)
            ->whereYear('created_at', now()->year)->sum('payable_amount_in_base_currency');
        $data['payments'] = currencyPosition($payments);

        $transactionData = Transaction::with('currency:id,code,exchange_rate')
            ->where('user_id', $user->id)
            ->select('transactional_type', 'currency_id', 'amount', 'trx_id', 'remarks', 'trx_type')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($txn) {
                //$rate = $txn->currency?->exchange_rate ?: 1;
                return (object) [
                    'icon' => $txn->icon,
                    'type' => $txn->type,
                    'remarks' => $txn->remarks,
                    'amount' => $txn->amount,
                    'trx_type' => $txn->trx_type,
                    'currency' => $txn->currency,
                ];
            });
        $data['transactions'] = $transactionData;

        return view('user.dashboard', $data);
    }

    public function profile()
    {
        $data['languages'] = Language::all();
        return view('user.profile.my_profile', $data);
    }


    public function addFund()
    {
        $data['basic'] = basicControl();
        $data['gateways'] = Gateway::getActiveMethods();
        return view('user.fund.add_fund', $data);
    }

    public function fund(Request $request)
    {
        $userId = Auth::id();
        $funds = Deposit::with(['depositable', 'gateway'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->latest()->paginate(20);
        return view('user.fund.index', compact('funds'));
    }

    public function qrCode()
    {
        return view('user.qrCode.qrCode');
    }

    public function qrPaymentList()
    {
        $userId = auth()->id();
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
            ->where('user_id', $userId)
            ->get()
            ->toArray())->collapse();

        return view('user.qrCode.index', $data);
    }

    public function qrPaymentListSearch(Request $request)
    {
        $userId = auth()->id();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_email;
        $filterGateway = $request->filter_gateway;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = QRCode::with(['user', 'gateway', 'currency'])
            ->where('user_id', $userId)->latest()
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
                        ->orWhere('amount', 'LIKE', "%{$search}%");
                });
            });
        return DataTables::of($transfers)
            ->addColumn('sender', function ($item) {
                return $item->email;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('charge', function ($item) {
                $amount = currencyPosition($item->charge,$item->currency_id);
                return '<span class="text-danger">' . $amount . '</span>';
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
            ->rawColumns(['sender', 'amount', 'charge', 'gateway', 'status', 'send_at'])
            ->make(true);
    }


    /*chart*/
    public function getTransferRequestSummary()
    {
        $types = [
            'App\\Models\\Transfer',
            'App\\Models\\RequestMoney'
        ];

        $year = now()->year;

        $data = Transaction::selectRaw(" transactional_type, SUM(amount * currencies.exchange_rate) as total ")
            ->whereYear('transactions.created_at', $year)
            ->whereIn('transactional_type', $types)
            ->leftJoin('currencies', 'transactions.currency_id', '=', 'currencies.id')
            ->groupBy('transactional_type')
            ->get()
            ->pluck('total', 'transactional_type');

        $transferRaw = getAmount($data['App\\Models\\Transfer'] ?? 0, 2);
        $requestRaw = getAmount($data['App\\Models\\RequestMoney'] ?? 0, 2);

        $total = $transferRaw + $requestRaw;

        $percentage = $total > 0 ? round(($transferRaw / $total) * 100) : 0;

        return response()->json([
            'data' => [
                'transfer' => [
                    'raw' => $transferRaw,
                    'formatted' => formatNumber($transferRaw)
                ],
                'request' => [
                    'raw' => $requestRaw,
                    'formatted' => formatNumber($requestRaw)
                ],
                'percentage' => $percentage,
                'percentageLabel' => trans('Transfer'),
            ]
        ]);
    }


    public function getActivityChart()
    {
        $types = ['App\\Models\\Transfer', 'App\\Models\\RequestMoney'];
        $year = now()->year;

        $data = Transaction::selectRaw(" MONTH(created_at) as month, transactional_type, COUNT(*) as total ")
            ->whereYear('created_at', $year)
            ->whereIn('transactional_type', $types)
            ->whereMonth('created_at', '<=', 7)
            ->groupByRaw("MONTH(created_at), transactional_type")
            ->orderByRaw("MONTH(created_at)")
            ->get();

        $chartData = [];
        $categories = [];

        for ($i = 1; $i <= 7; $i++) {
            $monthName = now()->setMonth($i)->format('M');
            $categories[] = $monthName;
            $chartData[$i] = [
                'Transfer' => 0,
                'RequestMoney' => 0,
            ];
        }

        foreach ($data as $row) {
            $month = (int)$row->month;
            $type = class_basename($row->transactional_type);

            if (isset($chartData[$month][$type])) {
                $chartData[$month][$type] = $row->total;
            }
        }

        return response()->json([
            'categories' => $categories,
            'series' => [
                [
                    'name' => trans('Transfer'),
                    'data' => array_column($chartData, 'Transfer'),
                ],
                [
                    'name' => trans('Request Money'),
                    'data' => array_column($chartData, 'RequestMoney'),
                ]
            ]
        ]);
    }

    public function getDailyTransactionCount()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $dailyCounts = Transaction::selectRaw('DAYOFWEEK(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('user_id', auth()->id())
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        $weekDays = [1,2,3,4,5,6,7];
        $finalData = [];
        foreach ($weekDays as $day) {
            $finalData[] = $dailyCounts[$day] ?? 0;
        }

        return response()->json([
            'data' => $finalData
        ]);
    }


    public function getTransferChart()
    {
        $userId = Auth::id();
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $data = Transfer::with('currency')
            ->whereYear('created_at', $currentYear)
            ->where('sender_id', $userId)
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format('m'))
            ->map(function ($monthTransfers) {
                return $monthTransfers->sum(fn ($t) => $t->amount * $t->currency?->exchange_rate);
            });

        $allMonths = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        $months = array_slice($allMonths, 0, $currentMonth); // Only till current month

        $monthlyData = [];
        foreach (range(1, $currentMonth) as $month) {
            $key = str_pad($month, 2, '0', STR_PAD_LEFT);
            $monthlyData[] = round($data[$key] ?? 0, 2);
        }

        return response()->json([
            'labels' => $months,
            'data'   => $monthlyData,
        ]);
    }


    public function loadChartData($type)
    {
        $user = auth()->user();

        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($type !== 'Transaction') {
            $query->where('transactional_type', "App\\Models\\$type");
        }

        $rawData = $query->selectRaw("DATE_FORMAT(created_at, '%b-%Y') as month_year,
            SUM(amount) as total,
            COUNT(id) as count")
            ->groupBy('month_year')
            ->orderByRaw("MIN(created_at)")
            ->get()
            ->keyBy('month_year')
            ->toArray();

        $allMonths = collect(range(0, 8))
            ->map(fn($i) => now()->subMonths(8 - $i)->format('M-Y'))
            ->toArray();

        $labels = array_map(fn($monthYear) => explode('-', $monthYear)[0], $allMonths);
        $amount = array_map(fn($monthYear) => $rawData[$monthYear]['total'] ?? 0, $allMonths);
        $count = array_map(fn($monthYear) => $rawData[$monthYear]['count'] ?? 0, $allMonths);

        $chart = [
            'labels' => $labels,
            'amounts' => $amount,
            'count' => $count,
        ];

        return response()->json($chart);
    }



}
