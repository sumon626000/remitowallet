<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\FxAsset;
use App\Models\FxTrade;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllStocksService
{
    public function __construct(private ForexPriceService $prices)
    {
    }

    public static function durationLabel(int $seconds): string
    {
        if ($seconds >= 3600 && $seconds % 3600 === 0) {
            $hours = (int) ($seconds / 3600);

            return $hours === 1 ? '1 hour' : $hours.' hours';
        }
        if ($seconds >= 60 && $seconds % 60 === 0) {
            $minutes = (int) ($seconds / 60);

            return $minutes === 1 ? '1 minute' : $minutes.' minutes';
        }

        return $seconds === 1 ? '1 second' : $seconds.' seconds';
    }

    public static function durationCatalog(): array
    {
        $raw = (array) (fxTradeSettings()->stocks_durations ?? []);
        $out = [];
        foreach ($raw as $seconds => $flag) {
            $seconds = (int) $seconds;
            if ($seconds >= 5 && !empty($flag)) {
                $out[$seconds] = self::durationLabel($seconds);
            }
        }
        ksort($out);

        return $out;
    }

    public function enabledDurations(object $settings): array
    {
        $raw = (array) ($settings->stocks_durations ?? []);
        $out = [];
        foreach ($raw as $seconds => $flag) {
            $seconds = (int) $seconds;
            if ($seconds >= 5 && !empty($flag)) {
                $out[$seconds] = ['seconds' => $seconds, 'label' => self::durationLabel($seconds)];
            }
        }
        ksort($out);

        return array_values($out);
    }

    public function wallet(int $userId): Wallet
    {
        $usdId = Currency::where('code', 'USD')->value('id')
            ?? Currency::where('code', basicControl()->base_currency)->value('id')
            ?? 2;

        $wallet = Wallet::with('currency')->where('user_id', $userId)->where('currency_id', $usdId)->first();
        if ($wallet) {
            return $wallet;
        }

        return Wallet::create([
            'user_id' => $userId,
            'currency_id' => $usdId,
            'balance' => 0,
        ])->load('currency');
    }

    public function open(int $userId, string $symbol, string $direction, float $stake, int $seconds): array
    {
        $settings = fxTradeSettings();
        if ((int) $settings->stocks_enabled !== 1) {
            throw new \RuntimeException('Forex Trade is currently off');
        }

        $asset = FxAsset::where('symbol', $symbol)->where('active', 1)->first();
        if (!$asset) {
            throw new \RuntimeException('That market is not available');
        }

        $allowed = collect($this->enabledDurations($settings))->pluck('seconds')->all();
        if (!in_array($seconds, $allowed, true)) {
            throw new \RuntimeException('That duration is not available');
        }

        $min = (float) $settings->stocks_min_bet;
        $max = (float) $settings->stocks_max_bet;
        $stake = round($stake, 2);
        if ($stake < $min || $stake > $max) {
            throw new \RuntimeException('Stake must be '.$min.'-'.$max);
        }

        $quote = $this->prices->quote($asset, true);
        $openPrice = $quote['price'] ?? null;
        if ($openPrice === null) {
            throw new \RuntimeException('Live price is unavailable right now');
        }

        $payoutPct = round((float) $settings->stocks_payout_pct, 2);
        $maxOpen = (int) $settings->stocks_max_open;

        return DB::transaction(function () use ($userId, $asset, $direction, $stake, $seconds, $openPrice, $payoutPct, $maxOpen) {
            $wallet = Wallet::where('user_id', $userId)
                ->where('currency_id', $this->wallet($userId)->currency_id)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = $this->wallet($userId);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            $openCount = FxTrade::where('user_id', $userId)->where('status', 'open')->lockForUpdate()->count();
            if ($openCount >= $maxOpen) {
                throw new \RuntimeException('You already have the maximum number of open trades');
            }

            if ($stake > (float) $wallet->balance) {
                throw new \RuntimeException('Insufficient wallet balance');
            }

            $trade = new FxTrade();
            $trade->user_id = $userId;
            $trade->wallet_id = $wallet->id;
            $trade->currency_id = $wallet->currency_id;
            $trade->asset = $asset->symbol;
            $trade->direction = $direction;
            $trade->stake = $stake;
            $trade->payout_pct = $payoutPct;
            $trade->open_price = $openPrice;
            $trade->open_time = now();
            $trade->expire_time = now()->addSeconds($seconds);
            $trade->status = 'open';
            $trade->payout_amount = 0;
            $trade->save();

            $balance = $this->move($wallet, $stake, 0, 'Forex Trade stake '.$asset->symbol.' '.$direction, $trade);

            Log::info('All Stocks trade opened', [
                'trade_id' => $trade->id,
                'user_id' => $userId,
                'asset' => $asset->symbol,
                'direction' => $direction,
                'stake' => $stake,
            ]);

            return [
                'balance' => $balance,
                'trade' => $this->present($trade->fresh(), (int) $asset->decimals),
            ];
        });
    }

    public function settleDue(?int $userId = null): int
    {
        $query = FxTrade::where('status', 'open')->where('expire_time', '<=', now())->orderBy('id');
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $ids = $query->limit(100)->pluck('id');
        $done = 0;
        foreach ($ids as $id) {
            if ($this->settleOne((int) $id)) {
                $done++;
            }
        }

        return $done;
    }

    public function settleOne(int $id): bool
    {
        return (bool) DB::transaction(function () use ($id) {
            $trade = FxTrade::where('id', $id)->lockForUpdate()->first();
            if (!$trade || $trade->status !== 'open' || $trade->expire_time->isFuture()) {
                return false;
            }

            $asset = FxAsset::where('symbol', $trade->asset)->first();
            $decimals = (int) ($asset->decimals ?? 5);
            $price = null;
            if ($asset) {
                $price = $this->prices->quote($asset, true)['price'] ?? null;
            }

            $wallet = Wallet::where('id', $trade->wallet_id)->lockForUpdate()->first();
            if (!$wallet && $trade->currency_id) {
                $wallet = Wallet::where('user_id', $trade->user_id)
                    ->where('currency_id', $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
            }
            if (!$wallet) {
                $wallet = $this->wallet((int) $trade->user_id);
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();
            }

            if (!$wallet) {
                $trade->status = 'void';
                $trade->result = 'void';
                $trade->payout_amount = 0;
                $trade->settled_at = now();
                $trade->save();
                Log::warning('All Stocks trade voided', ['trade_id' => $trade->id, 'user_id' => $trade->user_id]);

                return true;
            }

            $playerWins = $this->playerBeatsBot();
            $needUp = ($trade->direction === 'high') === $playerWins;
            $trade->close_price = $this->closeForResult((string) $trade->open_price, $price, $needUp, $decimals);

            if ($playerWins) {
                $credit = $this->winCredit((string) $trade->stake, (string) $trade->payout_pct);
                $this->move($wallet, (float) $credit, 1, 'Forex Trade win '.$trade->asset, $trade);
                $trade->result = 'win';
                $trade->payout_amount = $credit;
            } else {
                $trade->result = 'loss';
                $trade->payout_amount = 0;
            }

            $trade->status = 'settled';
            $trade->settled_at = now();
            $trade->save();

            Log::info('All Stocks trade settled', [
                'trade_id' => $trade->id,
                'user_id' => $trade->user_id,
                'result' => $trade->result,
            ]);

            return true;
        });
    }

    public function snapshot(int $userId, ?string $symbol): array
    {
        $this->settleDue($userId);
        $wallet = $this->wallet($userId);
        $wallet->refresh();

        $asset = null;
        if ($symbol) {
            $asset = FxAsset::where('symbol', $symbol)->where('active', 1)->first();
        }
        $quote = $asset ? $this->prices->quote($asset) : ['price' => null, 'series' => [], 'candles' => []];
        $decimals = (int) ($asset->decimals ?? 5);

        $decimalsBySymbol = FxAsset::pluck('decimals', 'symbol');
        $trades = FxTrade::where('user_id', $userId)->orderByDesc('id')->limit(30)->get();
        $wins = FxTrade::where('user_id', $userId)->where('result', 'win')->count();
        $losses = FxTrade::where('user_id', $userId)->where('result', 'loss')->count();

        return [
            'balance' => round((float) $wallet->balance, 2),
            'price' => $quote['price'],
            'series' => $quote['series'],
            'candles' => $quote['candles'] ?? [],
            'decimals' => $decimals,
            'wins' => $wins,
            'losses' => $losses,
            'server_time' => now()->timestamp,
            'trades' => $trades->map(function (FxTrade $trade) use ($decimalsBySymbol) {
                return $this->present($trade, (int) ($decimalsBySymbol[$trade->asset] ?? 5));
            })->values()->all(),
        ];
    }

    public function present(FxTrade $trade, int $decimals): array
    {
        return [
            'id' => $trade->id,
            'asset' => $trade->asset,
            'direction' => $trade->direction,
            'stake' => number_format((float) $trade->stake, 2, '.', ''),
            'payout_pct' => number_format((float) $trade->payout_pct, 2, '.', ''),
            'open_price' => $this->trimPrice((string) $trade->open_price, $decimals),
            'close_price' => $trade->close_price !== null ? $this->trimPrice((string) $trade->close_price, $decimals) : null,
            'open_time' => optional($trade->open_time)->toIso8601String(),
            'expire_time' => optional($trade->expire_time)->timestamp,
            'status' => $trade->status,
            'result' => $trade->result,
            'payout_amount' => number_format((float) $trade->payout_amount, 2, '.', ''),
        ];
    }

    private function playerBeatsBot(): bool
    {
        $botPct = max(0, min(100, (float) (fxTradeSettings()->stocks_bot_win_percent ?? 55)));
        $threshold = (int) round($botPct * 100);

        return random_int(1, 10000) > $threshold;
    }

    private function closeForResult(string $open, ?string $market, bool $needUp, int $decimals): string
    {
        $scale = max(0, min(8, $decimals));
        if ($market !== null) {
            $cmp = $this->compare($open, $market, $scale);
            $marketAgrees = $needUp ? $cmp > 0 : $cmp < 0;
            if ($marketAgrees) {
                return $this->trimPrice($market, $scale);
            }
        }

        $tick = $scale === 0 ? '1' : '0.'.str_repeat('0', $scale - 1).'1';
        $close = $needUp ? bcadd($open, $tick, $scale) : bcsub($open, $tick, $scale);
        if ((float) $close <= 0) {
            $close = bcadd($open, $tick, $scale);
        }

        return $close;
    }

    private function winCredit(string $stake, string $pct): string
    {
        $profit = bcdiv(bcmul($stake, $pct, 4), '100', 4);

        return bcadd($stake, $profit, 2);
    }

    private function compare(string $open, string $close, int $decimals): int
    {
        $scale = max(0, min(8, $decimals));
        if (function_exists('bccomp')) {
            return bccomp(bcadd($close, '0', $scale), bcadd($open, '0', $scale), $scale);
        }
        $opened = round((float) $open, $scale);
        $closed = round((float) $close, $scale);

        return $closed <=> $opened;
    }

    private function trimPrice(string $price, int $decimals): string
    {
        $decimals = max(0, min(8, $decimals));
        if (!is_numeric($price)) {
            return $price;
        }

        return number_format((float) $price, $decimals, '.', '');
    }

    private function move(Wallet $wallet, float $amount, int $action, string $remarks, FxTrade $trade): float
    {
        $balance = updateWallet($wallet->user_id, $wallet->currency_id, $amount, $action);
        $transaction = new Transaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->currency_id = $wallet->currency_id;
        $transaction->amount = $amount;
        $transaction->charge = 0;
        $transaction->trx_type = $action === 1 ? '+' : '-';
        $transaction->remarks = $remarks;
        $transaction->transactional_type = FxTrade::class;
        $transaction->transactional_id = $trade->id;
        $transaction->save();

        return (float) $balance;
    }
}
