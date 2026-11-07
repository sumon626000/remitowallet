<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChargesLimitController extends Controller
{
    public function index()
    {
        $chargesLimits = ChargesLimit::with('currency', 'gateway')->orderBy('currency_id', 'ASC')->get();
        return view('admin.chargesLimit.index', compact('chargesLimits'));
    }

    public function edit(ChargesLimit $chargesLimit)
    {
        $trxType = array_search($chargesLimit->transaction_type_id, config('transactionType'));
        $trxType = $trxType !== false ? ucfirst($trxType) : null;
        return view('admin.chargesLimit.edit', compact('chargesLimit', 'trxType'));
    }

    public function getCharge($currency_id, $payment_method_id)
    {
        $chargesLimit = ChargesLimit::with('gateway')->firstOrCreate([
            'currency_id' => $currency_id,
            'transaction_type_id' => config('transactionType.deposit'),
            'payment_method_id' => $payment_method_id,
        ]);

        $data = [
            'currency_id' => ($chargesLimit->currency_id) ?? 0,
            'transaction_type_id' => ($chargesLimit->transaction_type_id) ?? 0,
            'payment_method_id' => ($chargesLimit->payment_method_id) ?? 0,
            'percentage_charge' => (getAmount($chargesLimit->percentage_charge)) ?? 0,
            'fixed_charge' => (getAmount($chargesLimit->fixed_charge)) ?? 0,
            'min_limit' => (getAmount($chargesLimit->min_limit)) ?? 0,
            'max_limit' => (getAmount($chargesLimit->max_limit)) ?? 0,
            'convention_rate' => (getAmount($chargesLimit->convention_rate)) ?? 0,
            'is_active' => ($chargesLimit->is_active) ?? 1,
            'method_currency' => getMethodCurrency($chargesLimit->gateway),
        ];

        return response()->json($data);
    }

    public function setCharge(Request $request)
    {
        $purifiedData = $request->all();
        $validationRules = [
            'currency_id' => 'required|integer|not_in:0',
            'payment_method_id' => 'required|integer|not_in:0',
            'transaction_type_id' => 'required|integer|not_in:0',
            'convention_rate' => 'required|numeric|min:0',
            'fixed_charge' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'max_limit' => 'required|numeric|min:0',
            'min_limit' => 'required|numeric|min:0',
            'percentage_charge' => 'nullable|numeric|min:0',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return response()->json([
                $validate->getMessageBag()
            ], 422);
        }

        $purifiedData = (object)$purifiedData;

        $chargesLimit = ChargesLimit::where([
            'currency_id' => $purifiedData->currency_id,
            'transaction_type_id' => $purifiedData->transaction_type_id,
            'payment_method_id' => $purifiedData->payment_method_id,
        ])->first();

        $chargesLimit->convention_rate = $purifiedData->convention_rate;
        $chargesLimit->percentage_charge = $purifiedData->percentage_charge;
        $chargesLimit->fixed_charge = $purifiedData->fixed_charge;
        $chargesLimit->min_limit = $purifiedData->min_limit;
        $chargesLimit->max_limit = $purifiedData->max_limit;
        $chargesLimit->is_active = $purifiedData->is_active == "" ? 0 : 1;
        $chargesLimit->save();

        return response()->json([
            'status' => true,
            'message' => 'Charges & Limits Successfully Updated',
        ]);

    }
    
    public function chargePaymentMethod($currencyID)
	{
		$methods = Gateway::orderBy('sort_by', 'ASC')->get();
		$currency = Currency::findOrFail($currencyID);

		return view('admin.chargesLimit.paymentMethod', compact('methods', 'currency'));
	}

    public function chargeEdit($transactionTypeID, $currencyID)
    {
        $chargesLimit = ChargesLimit::with('currency')->firstOrCreate([
            'currency_id' => $currencyID,
            'transaction_type_id' => $transactionTypeID
        ]);

        $trxType = array_search($transactionTypeID, config('transactionType'));
        $trxType = $trxType !== false ? ucfirst($trxType) : null;

        return view('admin.chargesLimit.edit', compact('chargesLimit', 'trxType'));
    }

    public function update(Request $request, ChargesLimit $chargesLimit)
    {
        $purifiedData = $request->all();
        $validationRules = [
            'percentage_charge' => 'required',
            'fixed_charge' => 'nullable|numeric|min:0',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $purifiedData = (object)$purifiedData;
        $chargesLimit->percentage_charge = $purifiedData->percentage_charge;
        $chargesLimit->fixed_charge = $purifiedData->fixed_charge;
        $chargesLimit->min_limit = $purifiedData->min_limit;
        $chargesLimit->max_limit = $purifiedData->max_limit;
        $chargesLimit->is_active = $purifiedData->is_active;
        $chargesLimit->save();

        return redirect(route('admin.charge.index'))
            ->with('success', 'Charges & Limits Successfully Updated');
    }
}
