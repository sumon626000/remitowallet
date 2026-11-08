<?php

namespace App\Services;

use App\Models\FxAsset;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForexPriceService
{
    public function quote(FxAsset $asset, bool $fresh = false): array
    {
        $key = 'fxquote:'.$asset->id;
        if ($fresh) {
            Cache::forget($key);
        }

        $quote = Cache::remember($key, 4, function () use ($asset) {
            return $this->fetch($asset);
        });

        if (!is_array($quote)) {
            return ['price' => null, 'series' => [], 'candles' => []];
        }

        return [
            'price' => $quote['price'] ?? null,
            'series' => $quote['series'] ?? [],
            'candles' => $quote['candles'] ?? [],
        ];
    }

    private function fetch(FxAsset $asset): array
    {
        $apiKey = (string) config('services.twelvedata.key');
        if ($apiKey !== '' && $asset->twelve_symbol) {
            $fromTwelve = $this->twelve($asset, $apiKey);
            if ($fromTwelve['price'] !== null) {
                return $fromTwelve;
            }
        }

        $fromYahoo = $this->yahoo($asset);
        if ($fromYahoo['price'] !== null) {
            return $fromYahoo;
        }

        return ['price' => null, 'series' => [], 'candles' => []];
    }

    private function twelve(FxAsset $asset, string $apiKey): array
    {
        try {
            $priceResponse = Http::timeout(8)->acceptJson()->get('https://api.twelvedata.com/price', [
                'symbol' => $asset->twelve_symbol,
                'apikey' => $apiKey,
            ]);
            $raw = $priceResponse->json('price');
            $price = $this->normalize($raw, (int) $asset->decimals);
            if ($price === null) {
                return ['price' => null, 'series' => [], 'candles' => []];
            }

            $candles = Cache::remember('fxtwelve:candles:'.$asset->id, 60, function () use ($asset, $apiKey) {
                $response = Http::timeout(8)->acceptJson()->get('https://api.twelvedata.com/time_series', [
                    'symbol' => $asset->twelve_symbol,
                    'interval' => '1min',
                    'outputsize' => 48,
                    'apikey' => $apiKey,
                ]);
                $values = $response->json('values');
                if (!is_array($values)) {
                    return [];
                }
                $rows = [];
                foreach (array_reverse($values) as $row) {
                    $rows[] = [
                        'o' => $row['open'] ?? null,
                        'h' => $row['high'] ?? null,
                        'l' => $row['low'] ?? null,
                        'c' => $row['close'] ?? null,
                    ];
                }

                return $this->candlesFromRows($rows);
            });
            $candles = $this->applyLivePrice($candles, $price);

            return [
                'price' => $price,
                'series' => array_column($candles, 'c'),
                'candles' => $candles,
            ];
        } catch (\Throwable $e) {
            Log::warning('All Stocks Twelve Data request failed', ['symbol' => $asset->symbol, 'error' => $e->getMessage()]);

            return ['price' => null, 'series' => [], 'candles' => []];
        }
    }

    private function yahoo(FxAsset $asset): array
    {
        if (!$asset->yahoo_symbol) {
            return ['price' => null, 'series' => [], 'candles' => []];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'application/json',
                ])
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/'.rawurlencode($asset->yahoo_symbol), [
                    'interval' => '15m',
                    'range' => '5d',
                ]);

            if (!$response->ok()) {
                return ['price' => null, 'series' => [], 'candles' => []];
            }

            $result = $response->json('chart.result.0');
            if (!is_array($result)) {
                return ['price' => null, 'series' => [], 'candles' => []];
            }

            $price = $this->normalize($result['meta']['regularMarketPrice'] ?? null, (int) $asset->decimals);
            $quote = $result['indicators']['quote'][0] ?? [];
            $closes = is_array($quote['close'] ?? null) ? $quote['close'] : [];
            $rows = [];
            foreach ($closes as $i => $close) {
                $rows[] = [
                    'o' => $quote['open'][$i] ?? null,
                    'h' => $quote['high'][$i] ?? null,
                    'l' => $quote['low'][$i] ?? null,
                    'c' => $close,
                ];
            }
            $candles = $this->applyLivePrice($this->candlesFromRows($rows), $price);

            return [
                'price' => $price,
                'series' => array_column($candles, 'c'),
                'candles' => $candles,
            ];
        } catch (\Throwable $e) {
            Log::warning('All Stocks price request failed', ['symbol' => $asset->symbol, 'error' => $e->getMessage()]);

            return ['price' => null, 'series' => [], 'candles' => []];
        }
    }

    private function normalize($raw, int $decimals): ?string
    {
        if (!is_numeric($raw) || (float) $raw <= 0) {
            return null;
        }
        $decimals = max(0, min(8, $decimals));

        return number_format((float) $raw, $decimals, '.', '');
    }

    private function candlesFromRows(array $rows): array
    {
        $candles = [];
        foreach ($rows as $row) {
            if (!isset($row['c']) || !is_numeric($row['c']) || (float) $row['c'] <= 0) {
                continue;
            }
            $close = (float) $row['c'];
            $open = isset($row['o']) && is_numeric($row['o']) && (float) $row['o'] > 0 ? (float) $row['o'] : $close;
            $high = isset($row['h']) && is_numeric($row['h']) && (float) $row['h'] > 0 ? (float) $row['h'] : max($open, $close);
            $low = isset($row['l']) && is_numeric($row['l']) && (float) $row['l'] > 0 ? (float) $row['l'] : min($open, $close);
            $candles[] = [
                'o' => $open,
                'h' => max($high, $open, $close),
                'l' => min($low, $open, $close),
                'c' => $close,
            ];
        }

        return array_slice($candles, -40);
    }

    private function applyLivePrice(array $candles, ?string $price): array
    {
        if ($price === null || $candles === []) {
            return $candles;
        }
        $live = (float) $price;
        $last = count($candles) - 1;
        $candles[$last]['c'] = $live;
        $candles[$last]['h'] = max($candles[$last]['h'], $live);
        $candles[$last]['l'] = min($candles[$last]['l'], $live);

        return $candles;
    }
}
