<?php

namespace App\Http\Controllers;

use App\Models\ContentDetails;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\VirtualCardOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $currencies = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();

        $funds = Deposit::query()
            ->with(['sender', 'receiver', 'currency', 'gateway', 'receive_wallet'])
            ->where('user_id', $userId)
            ->search($request->all())
            ->latest()
            ->paginate(20);
        return view('user.fund.index', compact('funds', 'currencies'));
    }

    public function fundWalletSelection()
    {
        $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type', 'is_active', 'driver', 'logo')
            ->where('is_active', 1)->get();
        return view('user.fund.wallet-selection', $data);
    }

    public function initialize(Request $request, $from = null, $id = null)
    {
        $order = null;
        if ($from == 'card') {
            if (!$request->cardFundAmount) {
                return back()->with('error', 'Amount is required!');
            }
            $cardCurrencyCheck = cardCurrencyCheck($id);
            if ($cardCurrencyCheck['status'] == 'success') {
                if ($request->cardFundAmount < $cardCurrencyCheck['MinimumAmount']) {
                    return back()->with('error', 'Minimum amount is ' . $cardCurrencyCheck['MinimumAmount'] . ' ' . $cardCurrencyCheck['currencyCode'] . ' for add fund');
                } elseif ($request->cardFundAmount > $cardCurrencyCheck['MaximumAmount']) {
                    return back()->with('error', 'Maximum amount is ' . $cardCurrencyCheck['MaximumAmount'] . ' ' . $cardCurrencyCheck['currencyCode'] . ' for add fund');
                }
            }
            $order = VirtualCardOrder::with(['cardMethod'])->find($id);
        }
        if ($request->isMethod('get')) {
            $data['methods'] = Gateway::orderBy('sort_by', 'ASC')->where('status', 1)->get();
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type', 'is_active')->where('is_active', 1)->get();
            $data['template'] = ContentDetails::whereHas('content', function ($query) {
                $query->whereIn('name', ['add_fund']);
            })->first();
            $data['receive_currency'] = $request->receive_currency ?? null;
            return view('user.fund.create', $data, compact('order', 'from', 'id'));
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();

            $validationRules = [
                'amount' => 'required|numeric|min:1|not_in:0',
                'currency' => 'required',
                'methodId' => 'required|integer|min:1|not_in:0',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }

            $purifiedData = (object)$purifiedData;

            $amount = $purifiedData->amount;
            $currency = $purifiedData->currency;
            $methodId = $purifiedData->methodId;
            $receiveCurrency = $purifiedData->receive_currency ?? null;
            $isBase = false;

            $cardOrderId = null;
            if ($request->has('orderCardId') && $request->orderCardId != null) {
                $cardOrder = $this->validateCardOrder($request->orderCardId, $purifiedData);
                if (!$cardOrder['status']) {
                    return back()->with('error', $cardOrder['message']);
                }
                $cardOrderId = $cardOrder['id'] ?? null;
                $isBase = 'true';
            }

            $checkAmountValidate = $this->depositValidate($amount, $currency, config('transactionType.deposit'), $methodId, $isBase);//7 = deposit
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $checkAmountValidate['user_id'] = Auth::id();
            $checkAmountValidate['currency_id'] = $currency;
            $deposit = $this->createDeposit($checkAmountValidate, null, null, $cardOrderId, $receiveCurrency);

            return redirect(route('deposit.confirm', $deposit->trx_id));
        }
    }

    public function validatePayment($amount, $currency_id, $transaction_type_id, $methodId, $extraCharge = 0): array
    {
        if (!$currency_id) {
            return ['status' => false, 'message' => "Please select a currency."];
        }

        $gateway = Gateway::where('id', $methodId)->where('status', 1)->first();
        if (!$gateway) {
            return ['status' => false, 'message' => "Payment method not available for this transaction"];
        }

        $chargesLimit = \App\Models\ChargesLimit::query()
            ->with('currency')
            ->where('is_active', 1)
            ->where([
                'currency_id' => $currency_id,
                'transaction_type_id' => $transaction_type_id,
                'payment_method_id' => $methodId
            ])->first();

        $currencyModel = $chargesLimit?->currency ?: Currency::find($currency_id);
        $currency_code = $currencyModel?->code;
        if (!$currency_code) {
            return ['status' => false, 'message' => "Selected currency is invalid for this transaction"];
        }

        $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => Auth::id(), 'currency_id' => $currency_id]);
        if ($wallet->is_active != 1) {
            return ['status' => false, 'message' => "Inactive wallet currency, not available for this transaction"];
        }

        $receivableCurrencies = $gateway->receivable_currencies;
        if (!is_array($receivableCurrencies)) {
            return ['status' => false, 'message' => "Invalid currency configuration"];
        }

        $currencyInfo = collect($receivableCurrencies)->firstWhere('name', $currency_code)
            ?? collect($receivableCurrencies)->firstWhere('currency', $currency_code)
            ?? collect($receivableCurrencies)->firstWhere('currency_symbol', $currency_code);
        if (!$currencyInfo) {
            return [
                'status' => false,
                'message' => "{$currency_code} currency is not supported by the selected method. Please choose a different currency or payment method."
            ];
        }

        $limit = optional($currencyModel)->currency_type == 0 ? 8 : 2;
        $amount = getAmount($amount, $limit);
        $balance = Auth::check() ? getAmount($wallet->balance, $limit) : 0;

        $percentageSource = $chargesLimit->percentage_charge ?? $currencyInfo->percentage_charge ?? 0;
        $fixedSource = $chargesLimit->fixed_charge ?? $currencyInfo->fixed_charge ?? 0;
        $minSource = $chargesLimit->min_limit ?? $currencyInfo->min_limit ?? 0;
        $maxSource = $chargesLimit->max_limit ?? $currencyInfo->max_limit ?? 0;

        $percentage = getAmount($percentageSource, $limit);
        $percentage_charge = getAmount(($amount * $percentage) / 100, $limit);
        $fixed_charge = getAmount($fixedSource, $limit);
        $min_limit = getAmount($minSource, $limit);
        $max_limit = getAmount($maxSource, $limit);
        $charge = getAmount($percentage_charge + $fixed_charge, $limit);
        $payable_amount = getAmount($amount + $charge + $extraCharge, $limit);
        $new_balance = getAmount($balance + $amount, $limit);

        if ($amount < $min_limit || $amount > $max_limit) {
            return ['status' => false, 'message' => "Minimum payment $min_limit and maximum payment limit $max_limit"];
        }

        $conversionRate = $currencyInfo->conversion_rate ?? $chargesLimit->convention_rate ?? 1;
        $conversionRate = $conversionRate ?: 1;
        $payable_amount_in_base_currency = getAmount($payable_amount / $conversionRate, $limit);
        $base_currency_charge = getAmount($charge / $conversionRate, $limit);

        return [
            'status' => true,
            'message' => "Updated balance: $new_balance",
            'payment_method_id' => $gateway->id,
            'charges_limit_id' => $chargesLimit->id ?? null,
            'fixed_charge' => $fixed_charge,
            'percentage' => $percentage,
            'percentage_charge' => $percentage_charge,
            'min_limit' => $min_limit,
            'max_limit' => $max_limit,
            'balance' => $balance,
            'payable_amount' => $payable_amount,
            'new_balance' => $new_balance,
            'charge' => $charge,
            'amount' => $amount,
            'currency_id' => $currency_id,
            'currency_limit' => $limit,
            'base_currency_charge' => $base_currency_charge,
            'payable_amount_in_base_currency' => $payable_amount_in_base_currency,
            'payment_method_currency' => $currency_code,
            'base_currency' => basicControl()->base_currency
        ];
    }

    public function depositValidate($amount, $currency, $transaction_type_id, $methodId, $isBase = 'false'): array
    {
        try {
            $selectGateway = Gateway::where('id', $methodId)->where('status', 1)->first();
            if (!$selectGateway) {
                return ['status' => false, 'message' => "Payment method not available for this transaction"];
            }

            $selectedCurrency = array_search($currency, $selectGateway->supported_currency);
            if ($selectedCurrency !== false) {
                $selectedPayCurrency = $selectGateway->supported_currency[$selectedCurrency];
            } else {
                return ['status' => false, 'message' => "Please choose the currency you'd like to use for payment"];
            }

            if ($selectGateway) {
                $receivableCurrencies = $selectGateway->receivable_currencies;
                if (is_array($receivableCurrencies)) {
                    if ($selectGateway->id < 999) {
                        $currencyInfo = collect($receivableCurrencies)->where('name', $selectedPayCurrency)->first();
                    } else {
                        $currencyInfo = collect($receivableCurrencies)->where('currency', $selectedPayCurrency)->first();
                    }
                } else {
                    return [];
                }
            }
            $currencyType = $selectGateway->currency_type;
            $limit = $currencyType == 0 ? 8 : 2;
            $amount = ($isBase == 'true' ? $amount * $currencyInfo->conversion_rate : $amount);
            $amount = getAmount($amount, $limit);

            $payable_amount = $amount;
            $status = false;

            if ($currencyInfo) {
                $percentage = getAmount($currencyInfo->percentage_charge, $limit);
                $percentage_charge = getAmount(($payable_amount * $percentage) / 100, $limit);
                $fixed_charge = getAmount($currencyInfo->fixed_charge, $limit);

                $min_limit = getAmount($currencyInfo->min_limit, $limit);
                $max_limit = getAmount($currencyInfo->max_limit, $limit);
                $charge = getAmount($percentage_charge + $fixed_charge, $limit);
            }

            $basicControl = basicControl();
            $payable_amount_baseCurrency = getAmount($amount / $currencyInfo->conversion_rate, $limit);
            $charge_baseCurrency = getAmount($charge / $currencyInfo->conversion_rate, $limit);
            $payable_amount = getAmount($payable_amount + $charge, $limit);

            if ($payable_amount < $min_limit || $payable_amount > $max_limit) {
                if ($selectGateway->currency_type) {
                    $message = "Minimum payment $min_limit {$currencyInfo->currency_symbol} and maximum payment limit $max_limit {$currencyInfo->currency_symbol}";
                    if ($isBase == 'true') {
                        $min_limit = $min_limit * $currencyInfo->conversion_rate;
                        $max_limit = $max_limit * $currencyInfo->conversion_rate;
                        $message = "Minimum payment $min_limit {$basicControl->base_currency} and maximum payment limit $max_limit {$basicControl->base_currency}";
                    }
                } else {
                    $message = "Minimum payment $min_limit USD and maximum payment limit $max_limit USD";
                }
            } elseif ($payable_amount < 0) {
                $message = "Unprocessable Amount";
            } else {
                $status = true;
                $message = "Amount : $payable_amount_baseCurrency" . " " . $basicControl->base_currency;
            }

            $data['status'] = $status;
            $data['message'] = $message;
            $data['payment_method_id'] = $selectGateway->id;
            $data['payment_method_currency'] = $selectGateway->currency_type ? ($currencyInfo->name ?? $currencyInfo->currency) : 'USD';
            $data['gateway_name'] = $selectGateway->name;
            $data['fixed_charge'] = $fixed_charge;
            $data['percentage'] = $percentage;
            $data['percentage_charge'] = $percentage_charge;
            $data['min_limit'] = $min_limit;
            $data['max_limit'] = $max_limit;
            $data['payable_amount'] = $payable_amount;
            $data['charge'] = $charge;
            $data['amount'] = $amount;
            $data['conversion_rate'] = $currencyInfo->conversion_rate ?? 1;
            $data['payable_amount_in_base_currency'] = $payable_amount_baseCurrency;
            $data['base_currency_charge'] = $charge_baseCurrency;
            $data['currency_code'] = $selectGateway->currency_type ? ($currencyInfo->name ?? $currencyInfo->currency) : 'USD';
            $data['base_currency'] = $basicControl->base_currency;
            $data['currency_limit'] = $limit;
            $data['payment_crypto_currency'] = $selectGateway->currency_type ? null : ($currencyInfo->name ?? $currencyInfo->currency);

            return $data;

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function createDeposit($data, $morphType = null, $morphId = null, $cardOrderId = null, $receiveCurrency = null)
    {
        $user = Auth::user();
        $depositData = [
            'user_id' => $data['user_id'] ?? $user?->id,
            'depositable_type' => $morphType,
            'depositable_id' => $morphId,
            'payment_method_id' => $data['payment_method_id'],
            'payment_method_currency' => $data['payment_method_currency'],
            'amount' => $data['amount'],
            'percentage' => $data['percentage'],
            'percentage_charge' => $data['percentage_charge'],
            'fixed_charge' => $data['fixed_charge'],
            'charge' => $data['charge'],
            'payable_amount' => $data['payable_amount'],
            'base_currency_charge' => $data['base_currency_charge'],
            'payable_amount_in_base_currency' => $data['payable_amount_in_base_currency'],
            'charges_limit_id' => $data['charges_limit_id'] ?? null,
            'currency_id' => $data['currency_id'] ?? null,
            'card_order_id' => $cardOrderId ?? null,
            'email' => $user?->email ?? request('email') ?? null,
            'receive_currency_code' => $receiveCurrency,
            'status' => 0,
        ];

        return Deposit::create($depositData);
    }

    public function validateCardOrder($orderCardId, $purifiedData): array
    {
        $amount = $purifiedData->amount;

        $cardOrder = VirtualCardOrder::select(['id', 'user_id'])->where('id', $orderCardId)->first();
        if (!$cardOrder) {
            return ['status' => false, 'message' => 'Invalid virtual card'];
        }
        if ($cardOrder->user_id != auth()->id()) {
            return ['status' => false, 'message' => 'You are not eligible for this transaction'];
        }

        $info = cardCurrencyCheck($cardOrder->id);
        if ($info['status'] !== 'success') {
            return ['status' => false, 'message' => 'Currency check failed'];
        }

        if ($info['MinimumAmount'] >= $amount) {
            return ['status' => false, 'message' => 'Amount must be greater than ' . $info['MinimumAmount']];
        }

        if ($info['MaximumAmount'] <= $amount) {
            return ['status' => false, 'message' => 'Amount must be smaller than ' . $info['MaximumAmount']];
        }

        $percentValue = ($info['PercentCharge'] * $amount) / 100;
        $charge = $info['FixedCharge'] + $percentValue;
        if ($amount < $charge) {
            return ['status' => false, 'message' => 'Charge cannot be greater than input amount'];
        }

        return ['status' => true, 'id' => $cardOrder->id];
    }


    public function checkAmountValidate($amount, $currency_id, $transaction_type_id, $methodId)
    {

        return $this->validatePayment($amount, $currency_id, $transaction_type_id, $methodId);
    }


}
