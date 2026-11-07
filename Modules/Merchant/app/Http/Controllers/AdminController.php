<?php

namespace Modules\Merchant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Modules\Merchant\Models\MerchantGlobalSetting;

class AdminController extends Controller
{
    public function settings()
    {
        $settings = MerchantGlobalSetting::query()
            ->with('currency')
            ->whereHas('currency')
            ->get();
        return view('merchant::admin.settings.index', compact('settings'));
    }

    public function settingsCreate(Request $request)
    {
        $currencies = Currency::query()->where('is_active',1)->get();
        return view('merchant::admin.settings.create', compact('currencies'));
    }

    public function settingsEdit($id)
    {
        $settings = MerchantGlobalSetting::findOrFail($id);
        $title = __('Edit Settings for :currency Currency', [
            'currency' => $merchant->currency->code ?? __('Unknown')
        ]);
        return view('merchant::admin.settings.edit', compact('settings', 'title'));
    }

    public function settingsStore(Request $request)
    {
        $data = $request->validate([
            'currency_id'         => 'required|exists:currencies,id|unique:merchant_global_settings,currency_id',
            'fixed_charge'        => 'required|numeric|min:0',
            'percent_charge'      => 'required|numeric|min:0',
            'auto_withdraw'       => 'required|numeric|min:0',
            'min_withdraw_amount' => 'required|numeric|min:0',
            'status'              => 'nullable|boolean',
        ], [
            'currency_id.unique' => __('Selected currency already exist.'),
        ]);

        MerchantGlobalSetting::create($data);

        return to_route('admin.merchant.settings')
            ->with('success', __('Merchant settings created successfully.'));
    }

    public function updateSettings(Request $request,$id)
    {
        $merchantSetting = MerchantGlobalSetting::query()->findOrFail($id);

        $data = $request->validate([
            'currency_id' => 'required|exists:currencies,id|unique:merchant_global_settings,currency_id,' . $id,
            'fixed_charge'        => 'required|numeric|min:0',
            'percent_charge'      => 'required|numeric|min:0',
            'auto_withdraw'       => 'required|numeric|min:0',
            'min_withdraw_amount' => 'required|numeric|min:0',
            'status'              => 'nullable|boolean',
        ]);

        $merchantSetting->update($data);

        return back()->with('success', __('Merchant settings for (:currency) updated successfully.', [
            'currency' => $merchantSetting->currency?->code
        ]));
    }


}
