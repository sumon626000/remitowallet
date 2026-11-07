<?php

namespace Modules\Merchant\Console;

use App\Models\Currency;
use App\Models\PayoutMethod;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Merchant\Jobs\AutoWithdrawJob;
use Modules\Merchant\Models\MerchantGlobalSetting;
use Modules\Merchant\Models\MerchantSetting;
use Modules\Merchant\Services\AutoWithdrawService;

class AutoWithdrawCommand extends Command
{
    protected $signature = 'merchant:auto-withdraw';
    protected $description = 'Automatically process merchant auto-withdrawals based on settings';

    public function handle()
    {
        $pm = PayoutMethod::query()
            ->where('is_automatic', 0)
            ->where('is_active', 1)
            ->where('name', 'Bank Transfer')
            ->first();

        if (!$pm || !is_array($pm->payout_currencies)) {
            return Command::FAILURE;
        }

        if ($this->shouldRunInBackground()) {
            $this->runJob($pm);
        } else {
            $this->runRealTime($pm);
        }

        return Command::SUCCESS;
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

    protected function runRealTime(PayoutMethod $pm): void
    {
        MerchantSetting::query()
            ->where('auto_withdraw', true)
            ->where('status', true)
            ->whereNotNull('withdraw_currency')
            ->cursor()
            ->each(function ($setting) use ($pm) {

                if (!$this->isFrequencyDue($setting->withdraw_frequency, $setting->last_auto_withdraw_at)) {
                    return;
                }
                $merchantId = $setting->merchant_id;
                $currencyCode = $setting->withdraw_currency;

                $currency = Currency::where('code', $currencyCode)->first();
                if (!$currency) {
                    return;
                }

                $wallet = Wallet::where('user_id', $merchantId)
                    ->where('currency_id', $currency->id)
                    ->first();
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
            });
    }


    protected function shouldRunInBackground(): bool
    {
        //return config('job.merchant_auto_withdraw', false);
        return false;
    }

    //for millions data
    protected function runJob(PayoutMethod $pm): void
    {
        MerchantSetting::query()
            ->where('auto_withdraw', true)
            ->where('status', true)
            ->whereNotNull('withdraw_currency')
            ->cursor()
            ->each(function ($setting) use ($pm) {
                dispatch(new AutoWithdrawJob($setting, $pm));
            });
    }


}
