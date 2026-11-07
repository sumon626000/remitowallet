<?php

namespace App\Services;


use App\Models\Currency;
use App\Models\PayoutMethod;
use Illuminate\Support\Facades\Http;
use Exception;

class CurrencyLayerService
{
    private function getSupportedCurrencyCodes(): array
    {
        $payoutCurrencies = PayoutMethod::where('is_automatic', 1)
            ->where('code', '!=', 'coinbase')
            ->where('is_auto_update', 1)
            ->pluck('supported_currency')
            ->toArray();

        $currencyLists = [];
        foreach ($payoutCurrencies as $currency) {
            foreach ((array) $currency as $singleCurrency) {
                $currencyLists[] = $singleCurrency;
            }
        }

        return array_values(array_unique($currencyLists));
    }

    private function getFallbackRates(string $source, array $currencyCodes): object
    {
        $sourceRate = Currency::where('code', $source)->value('exchange_rate');
        $sourceRate = ($sourceRate && $sourceRate != 0) ? (float) $sourceRate : 1.0;

        $quotes = [];
        foreach ($currencyCodes as $code) {
            $targetRate = Currency::where('code', $code)->value('exchange_rate');

            if (!$targetRate || $targetRate == 0) {
                if ($code === $source) {
                    $targetRate = $sourceRate;
                } else {
                    continue;
                }
            }

            $quotes[$source . $code] = (float) $targetRate / $sourceRate;
        }

        return (object) [
            'success' => true,
            'source' => $source,
            'quotes' => (object) $quotes,
        ];
    }

    public function getCurrencyRate()
    {
        $endpoint = 'live';
        $source = basicControl()->base_currency;
        $currency_layer_url = "https://api.currencylayer.com";
        $currency_layer_access_key = basicControl()->currency_layer_access_key;
        $currencyLists = $this->getSupportedCurrencyCodes();

        if (empty($currencyLists)) {
            return $this->getFallbackRates($source, []);
        }

        if (!$currency_layer_access_key) {
            return $this->getFallbackRates($source, $currencyLists);
        }

        $currencies = implode(',', $currencyLists);
        $CurrencyAPIUrl = "$currency_layer_url/$endpoint?access_key=$currency_layer_access_key&source=$source&currencies=$currencies";

        try {
            $response = Http::acceptJson()->get($CurrencyAPIUrl);

            if (!$response->successful()) {
                return $this->getFallbackRates($source, $currencyLists);
            }

            $payload = $response->json();

            if (!is_array($payload)) {
                return $this->getFallbackRates($source, $currencyLists);
            }

            if (($payload['success'] ?? true) === false) {
                return $this->getFallbackRates($source, $currencyLists);
            }

            if (empty($payload['quotes']) || empty($payload['source'])) {
                return $this->getFallbackRates($source, $currencyLists);
            }

            return json_decode($response->body());
        } catch (Exception $e) {
            return $this->getFallbackRates($source, $currencyLists);
        }
    }

}