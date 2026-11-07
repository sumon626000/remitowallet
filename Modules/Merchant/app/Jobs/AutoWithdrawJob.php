<?php

namespace Modules\Merchant\Jobs;

use App\Models\Currency;
use App\Models\PayoutMethod;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Modules\Merchant\Models\MerchantGlobalSetting;
use Modules\Merchant\Models\MerchantSetting;
use Modules\Merchant\Services\AutoWithdrawService;

class AutoWithdrawJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $setting;
    protected $payoutMethod;

    public function __construct(MerchantSetting $setting, PayoutMethod $payoutMethod)
    {
        $this->setting = $setting;
        $this->payoutMethod = $payoutMethod;
    }

    public function handle()
    {
        $setting = $this->setting;
        $pm = $this->payoutMethod;

        if (!$this->isFrequencyDue($setting->withdraw_frequency, $setting->last_auto_withdraw_at)) {
            return;
        }
        $merchantId = $setting->merchant_id;
        $currencyCode = $setting->withdraw_currency;

        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) {
            return;
        }

        $wallet = Wallet::where('user_id', $merchantId)->where('currency_id', $currency->id)->first();
        if (!$wallet || $wallet->balance < $setting->withdraw_amount) {
            return;
        }

        $currencyInfo = collect($pm->payout_currencies)->firstWhere('name', $currency->code)
            ?? collect($pm->payout_currencies)->firstWhere('currency_symbol', $currency->code);
        if (!$currencyInfo) {
            return;
        }

        DB::beginTransaction();
        try {
            $global = MerchantGlobalSetting::query()->where('currency_id', $currency->id)->first();
            if (!$global || !$global->auto_withdraw) {
                return;
            }

            app(AutoWithdrawService::class)->process($setting, $wallet, $pm, $currencyInfo);

            $setting->update(['last_auto_withdraw_at' => now()]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
        }

    }

    protected function isFrequencyDue(string $freq, $last): bool
    {
        $last = $last ? Carbon::parse($last) : now()->subYears(5);
        return match ($freq) {
            'daily' => $last->diffInDays(now()) >= 1,
            'weekly' => $last->diffInDays(now()) >= 7,
            'monthly' => $last->diffInMonths(now()) >= 1,
            default => false,
        };
    }

}
