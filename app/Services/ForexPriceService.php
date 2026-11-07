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
            return ['price' => null, 'series' => []];
        }

        return [
            'price' => $quote['price'] ?? null,
            'series' => $quote['series'] ?? [],
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

        return ['price' => null, 'series' => []];
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
                return ['price' => null, 'series' => []];
            }

            $series = Cache::remember('fxtwelve:series:'.$asset->id, 60, function () use ($asset, $apiKey) {
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
                $points = [];
                foreach (array_reverse($values) as $row) {
                    if (isset($row['close']) && is_numeric($row['close']) && (float) $row['close'] > 0) {
                        $points[] = (float) $row['close'];
                    }
                }

                return $points;
            });

            $series[] = (float) $price;

            return ['price' => $price, 'series' => array_slice($series, -48)];
        } catch (\Throwable $e) {
            Log::warning('All Stocks Twelve Data request failed', ['symbol' => $asset->symbol, 'error' => $e->getMessage()]);

            return ['price' => null, 'series' => []];
        }
    }

    private function yahoo(FxAsset $asset): array
    {
        if (!$asset->yahoo_symbol) {
            return ['price' => null, 'series' => []];
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'application/json',
                ])
                ->get('https://query1.finance.yahoo.com/v8/finance/chart/'.rawurlencode($asset->yahoo_symbol), [
                    'interval' => '1m',
                    'range' => '1d',
                ]);

            if (!$response->ok()) {
                return ['price' => null, 'series' => []];
            }

            $result = $response->json('chart.result.0');
            if (!is_array($result)) {
                return ['price' => null, 'series' => []];
            }

            $price = $this->normalize($result['meta']['regularMarketPrice'] ?? null, (int) $asset->decimals);
            $closes = $result['indicators']['quote'][0]['close'] ?? [];
            $series = [];
            if (is_array($closes)) {
                foreach ($closes as $close) {
                    if (is_numeric($close) && (float) $close > 0) {
                        $series[] = (float) $close;
                    }
                }
            }

            return ['price' => $price, 'series' => array_slice($series, -48)];
        } catch (\Throwable $e) {
            Log::warning('All Stocks price request failed', ['symbol' => $asset->symbol, 'error' => $e->getMessage()]);

            return ['price' => null, 'series' => []];
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
}
