<?php

namespace App\Console;

use App\Console\Commands\PayoutCryptoCurrencyUpdateCron;
use App\Console\Commands\PayoutCurrencyUpdateCron;
use App\Models\ApiOrder;
use App\Models\ApiOrderTest;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        PayoutCurrencyUpdateCron::class,
        PayoutCryptoCurrencyUpdateCron::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $basicControl = basicControl();
        if ($basicControl){
            if ($basicControl->currency_layer_auto_update == 1) {
                $schedule->command('payout-currency:update')->{$basicControl->currency_layer_auto_update_at}();
            }
            if ($basicControl->coin_market_cap_auto_update == 1) {
                $schedule->command('payout-crypto-currency:update')->{$basicControl->coin_market_cap_auto_update_at}();
            }
        }

        $schedule->command('stocks:settle')->everyMinute()->withoutOverlapping();

        $schedule->command('model:prune')->dailyAt('01:00');

        $schedule->command('model:prune', [
            '--model' => [ApiOrder::class, ApiOrderTest::class],
        ])->everyFiveMinutes();

    }


    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
