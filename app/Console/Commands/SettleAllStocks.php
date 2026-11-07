<?php

namespace App\Console\Commands;

use App\Services\AllStocksService;
use Illuminate\Console\Command;

class SettleAllStocks extends Command
{
    protected $signature = 'stocks:settle';

    protected $description = 'Settle expired All Stocks trades from the live price feed';

    public function handle(AllStocksService $stocks): int
    {
        $count = $stocks->settleDue();
        $this->info('Settled '.$count.' All Stocks trade(s).');

        return self::SUCCESS;
    }
}
