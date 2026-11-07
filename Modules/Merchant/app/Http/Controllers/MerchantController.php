<?php

namespace Modules\Merchant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\TwoFactorSetting;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use App\Traits\PayoutTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Merchant\Models\MakePayment;
use Modules\Merchant\Models\MerchantSetting;
use Stevebauman\Purify\Facades\Purify;

class MerchantController extends Controller
{
    use ApiValidation,PayoutTrait;

    public function dashboard(Request $request)
    {
        $userId = auth()->id();

        $data['wallets'] = Wallet::with(['currency:id,name,code,symbol,logo,driver'])
            ->select('id', 'balance as totalBalance', 'user_id', 'currency_id')
            ->where('user_id', $userId)
            ->customOrder()
            ->get()
            ->map(function ($wallet) {
                $wallet->currency->imageUrl = $wallet?->currency?->getImage();
                return $wallet;
            });

        $transactionData = Transaction::with('currency:id,code,exchange_rate')
            ->where('user_id', $userId)
            ->select('transactional_type', 'currency_id', 'amount', 'trx_id', 'remarks', 'trx_type')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($txn) {
                return (object)[
                    'icon' => $txn->icon,
                    'type' => $txn->type,
                    'remarks' => $txn->remarks,
                    'amount' => $txn->amount,
                    'trx_type' => $txn->trx_type,
                    'currency' => $txn->currency,
                ];
            });
        $data['transactions'] = $transactionData;

        $data['payments'] = MakePayment::with([
            'user:id,username,firstname,lastname,email,image,image_driver',
            'merchant:id,username,firstname,lastname,email,image,image_driver',
            'currency:id,code,exchange_rate'
        ])
            ->where('status', 1)
            ->where('merchant_id', $userId)
            ->latest('id')
            ->limit(4)
            ->get();

        $rows = MakePayment::query()
            ->where('merchant_id', $userId)
            ->where('status', 1)
            ->select(
                'currency_id',
                DB::raw("SUM(received_amount) as received"),
                DB::raw("SUM(charge) as charge"),
                DB::raw("SUM(CASE WHEN charge_applied_to = 'merchant' THEN amount ELSE 0 END) as charge_by_merchant")
            )
            ->with('currency:id,exchange_rate')
            ->groupBy('currency_id')
            ->get();

        $data['merchant_transaction'] = [
            'total_received' => 0,
            'total_charge'   => 0,
        ];

        foreach ($rows as $row) {
            $rate = $row->currency->exchange_rate ?: 1;
            $received = $row->received ?? 0;
            $charge   = $row->charge ?? 0;
            $data['merchant_transaction']['total_received'] += $received / $rate;
            $data['merchant_transaction']['total_charge']   += $charge / $rate;
        }

        if ($this->isApiRequest()) {
            return response()->json($this->withSuccess($data));
        }

        $data['basic'] = basicControl();
        $data['firebaseNotify'] = config('firebase');

        return view('merchant::dashboard', $data);
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

        $chart = ['labels' => $labels, 'amounts' => $amount, 'count' => $count,];

        return response()->json($chart);
    }

    public function qrCode()
    {
        return view('merchant::qr_code');
    }

    public function qrCodeApi()
    {
        $data['qrValue'] = \auth()->user()->email;
        return response()->json($this->withSuccess($data));
    }

    public function settings()
    {
        $merchantId = Auth::id();
        $data['setting'] = MerchantSetting::firstOrCreate(
            ['merchant_id' => $merchantId],
            ['withdraw_currency' => 'USD']
        );
        $data['payoutMethod'] = PayoutMethod::query()
            ->where('is_automatic', 0)
            ->where('is_active', 1)
            // ->where('name', 'Bank Transfer')
            ->first();

        if ($this->isApiRequest()) {
            return response()->json($this->withSuccess($data));
        }
        return view('merchant::settings.index',$data);
    }

    public function settingsUpdate(Request $request)
    {
        $merchantId = Auth::id();
        DB::beginTransaction();
        try {
            $request->validate([
                'charge_applied_to' => 'required|in:user,merchant',
                'auto_withdraw' => 'required|in:0,1',
                'withdraw_frequency' => 'required|in:monthly,weekly',
                'withdraw_amount' => 'required|numeric|min:0',
                'withdraw_currency' => 'required',
            ]);

            $amount = $request->withdraw_amount;
            $currency = $request->withdraw_currency;

            $payoutMethod = PayoutMethod::query()
                ->where('is_automatic', 0)
                ->where('is_active', 1)
                ->where('name', 'Bank Transfer')
                ->first();

            $validateData = $this->validationCheck($amount, $currency, $payoutMethod->id);
            $ignoredMessages = [
                "Insufficient balance for transaction"
            ];
            if (!$validateData['status'] && !in_array($validateData['message'], $ignoredMessages)) {
                throwIfInvalid(true, $validateData['message']);
            }

            $reqField = $this->getRequestFields($payoutMethod, $request);
            $information = $reqField;

            MerchantSetting::updateOrCreate(
                [
                    'merchant_id' => $merchantId
                ],
                [
                    'charge_applied_to' => $request->charge_applied_to ?? 'user',
                    'auto_withdraw' => $request->auto_withdraw ?? false,
                    'withdraw_frequency' => $request->withdraw_frequency ?? 'weekly',
                    'withdraw_amount' => $request->withdraw_amount ?? 100,
                    'withdraw_currency' => $request->withdraw_currency ?? 'USD',
                    'withdraw_information' => $information ?? null,
                ]
            );
            DB::commit();
            if ($this->isApiRequest()) {
                return response()->json($this->withSuccess('Merchant settings updated successfully'));
            }
            return back()->with('success', 'Merchant settings updated successfully.');
        } catch (ValidationException $e) {
            return $this->handleValidationError($e);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function apiKey(Request $request)
    {
        $merchant = auth()->user();
        if ($request->method() == 'GET') {
            $public_key = $merchant->public_key;
            $secret_key = $merchant->secret_key;
            if (!$public_key || !$secret_key) {
                $merchant->public_key = bin2hex(random_bytes(20));
                $merchant->secret_key = bin2hex(random_bytes(20));
                $merchant->save();
            }
            return view('merchant::api.index', compact('merchant'));
        }
        if ($request->method() == 'POST') {
            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $merchant->id]);
            $purifiedData = Purify::clean($request->all());
            $rules['security_pin'] = 'required|integer|digits:5';
            $validate = Validator::make($request->all(), $rules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
            }

            $merchant->public_key = bin2hex(random_bytes(20));
            $merchant->secret_key = bin2hex(random_bytes(20));
            $merchant->save();
            return back()->with('success', 'Api key generated successfully');
        }
    }

    public function modeChange($mode)
    {
        $merchant = auth()->user();

        $merchant->mode = $mode === 'test' ? 0 : 1;
        $merchant->save();

        return to_route('merchant.api.key')->with('success', 'Mode updated successfully.');
    }

    public function apiDocx()
    {
        return view('merchant::api.docx');
    }



}
