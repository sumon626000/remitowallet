<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    use Upload;

    public function index()
    {
        $currencies = Currency::all();
        return view('admin.currency.index', compact('currencies'));
    }

    public function create()
    {
        return view('admin.currency.create');
    }

    public function store(Request $request)
    {
        $purifiedData = $request->all();
        $purifiedData['logo'] = $request->logo;
        $validationRules = [
            'name' => 'required|min:2|max:100',
            'exchange_rate' => 'required|numeric|not_in:0',
            'symbol' => 'required|unique:currencies',
            'code' => 'required|unique:currencies',
            'currency_type' => 'required|numeric:not_in:0',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $purifiedData = (object)$purifiedData;
        $currency = new Currency();
        $currency->name = $purifiedData->name;
        $currency->symbol = $purifiedData->symbol;
        $currency->is_active = isset($purifiedData->is_active);
        $currency->code = $purifiedData->code;
        $currency->exchange_rate = $purifiedData->exchange_rate;
        $currency->currency_type = $purifiedData->currency_type;
        if ($request->file('logo') && $request->file('logo')->isValid()) {
            $extension = $request->logo->extension();
            $logoName = strtolower($purifiedData->code . '.' . $extension);
            try {
                $image = $this->fileUpload($request->logo, config('filelocation.currencyLogo.path'), $logoName, null, 'webp', 70, null, null);
                if ($image) {
                    $currency->logo = $image['path'];
                    $currency->driver = $image['driver'];
                }
            } catch (\Exception $exp) {
                return back()->with('error', 'Logo could not be uploaded.');
            }
        }
        $currency->save();

        ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 1]);
        ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 2]);
        ChargesLimit::firstOrCreate(['currency_id' => $currency->id, 'transaction_type_id' => 3]);

        return redirect(route('admin.currency.index'))->with('success', 'Currency Successfully Saved');
    }

    public function edit(currency $currency)
    {
        return view('admin.currency.edit', compact('currency'));
    }

    public function update(Request $request, currency $currency)
    {
        $purifiedData = Purify::clean($request->all());
        if ($request->file('logo')) {
            $purifiedData['logo'] = $request->logo;
        }
        $validationRules = [
            'name' => 'required',
            'exchange_rate' => 'required',
            'symbol' => 'required|unique:currencies,symbol,' . $currency->id,
            'code' => 'required|unique:currencies,code,' . $currency->id,
            'currency_type' => 'required|numeric:not_in:0',
            'logo' => 'nullable|mimes:jpg,jpeg,png|max:10240',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $purifiedData = (object)$purifiedData;
        $currency->name = $purifiedData->name;
        $currency->symbol = $purifiedData->symbol;
        $currency->is_active = $purifiedData->is_active;
        $currency->code = $purifiedData->code;
        $currency->exchange_rate = $purifiedData->exchange_rate;
        $currency->currency_type = $purifiedData->currency_type;
        if ($request->file('logo') && $request->file('logo')->isValid()) {
            $extension = $request->logo->extension();
            $logoName = strtolower($purifiedData->code . '.' . $extension);
            try {
                $image = $this->fileUpload($request->logo, config('filelocation.currencyLogo.path'), $logoName, null, 'webp', 70, $currency->logo, $currency->driver);
                if ($image) {
                    $currency->logo = $image['path'];
                    $currency->driver = $image['driver'];
                }
            } catch (\Exception $exp) {
                return back()->with('error', 'Logo could not be uploaded.');
            }
        }
        $currency->save();

        return redirect(route('admin.currency.index'))->with('success', 'Currency Successfully Saved');
    }





    // Transaction summary
    public function walletSummary($code)
    {
        $currency = Currency::where('code', $code)->first();
        if (!$currency) {
            return to_route('admin.dashboard')->with('error', 'Invalid Currency');
        }

        $data['transactions'] = Transaction::query()
            ->with(['user:id,username,firstname,lastname,image,image_driver'])
            ->where('currency_id',$currency->id)
            ->whereHas('user')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();

        $data['overview'] = Transaction::query()
            ->where('currency_id',$currency->id)
            ->selectRaw("
            COUNT(*) as total_transactions,
            SUM(amount) as total_transaction_volume,
            SUM(charge) as total_profit,
            COUNT(DISTINCT user_id) as total_users,
            (SUM(amount) - SUM(charge)) as net_transaction_volume,
            (SUM(amount) - SUM(charge) - (0.10 * SUM(amount))) as new_transaction_volume,
            (SUM(charge) / NULLIF(SUM(amount), 0)) * 100 as charge_percentage,
            MAX(charge) as highest_charge,
            MIN(charge) as lowest_charge
        ")->first();

        $totalRegisteredUsers = Cache::remember('total_users_count', now()->addMinutes(30), function () {
            return User::count();
        });
        $data['overview']->user_percentage = ($totalRegisteredUsers > 0)
            ? ($data['overview']->total_users / $totalRegisteredUsers) * 100
            : 0;


        $statistics['schedule'] = $this->dayList();
        return view('admin.transaction.wallet_summary',$data, compact("statistics",'code','currency'));
    }

    public function monthlyTransaction(Request $request, $code = null)
    {
        $wallet = Currency::query()->where('code', $code)->first();

        $keyDataset = $request->keyDataset;
        $dailyTransaction = $this->dayList();
        $dailyProfit = $this->dayList();

        Transaction::query()
            ->when($keyDataset == '0', function ($query) use ($wallet) {
                $query->whereMonth('created_at', Carbon::now()->month)->where('currency_id', $wallet->id);
            })
            ->when($keyDataset == '1', function ($query) use ($wallet) {
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('created_at', $lastMonth->month)
                    ->where('currency_id', $wallet->id);
            })
            ->when($keyDataset == '1', function ($query) {
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('created_at', $lastMonth->month);
            })
            ->select(
                DB::raw('SUM(amount) as totalTransaction'),
                DB::raw('SUM(charge) as totalCharge'),
                DB::raw('DATE_FORMAT(created_at,"Day %d") as date')
            )
            ->groupBy(DB::raw("DATE(created_at)"))
            ->get()->map(function ($item) use ($dailyTransaction, $dailyProfit) {
                $dailyTransaction->put($item['date'], $item['totalTransaction']);
                $dailyProfit->put($item['date'], $item['totalCharge']);
            });

        return response()->json([
            "totalTransaction" => currencyPosition($dailyTransaction->sum(),$wallet->id),
            "totalProfit" => currencyPosition($dailyProfit->sum(),$wallet->id),
            "dailyTransaction" => $dailyTransaction,
            "dailyProfit" => $dailyProfit,
            "currency_code" => $wallet->code,
            "currency_symbol" => $wallet->symbol,
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


}
