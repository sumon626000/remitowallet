<?php

namespace App\Services\Payout\perfectmoney;

use App\Models\PayoutMethod;
use Facades\App\Services\BasicCurl;
use Illuminate\Support\Facades\Http;

class Card
{
    public static function payouts($payout)
    {
        $method = PayoutMethod::where('code', 'perfectmoney')->first();

        $accountId = optional($method->parameters)->Account_ID;
        $passphrase = optional($method->parameters)->Passphrase;
        $payer = optional($method->parameters)->Payer_Account;
        $payee = $payout?->information?->account_number ?? null;
        $amount = $payout->amount;

        $query = [
            'AccountID'   => $accountId,
            'PassPhrase'  => $passphrase,
            'Payer_Account' => $payer,
            'Payee_Account' => $payee,
            'Amount'      => $amount,//in USD
            'PAY_IN'      => 1,
            'PAYMENT_ID'  => 'masud9900',
        ];

        $url = 'https://perfectmoney.is/acct/confirm.asp';

        $response = Http::get($url, $query);

        $body = $response->body();

        if ($response->successful()) {
            if (str_contains($body, 'ERROR')) {
                return ['status' => 'error', 'data' => $body];
            }
            return ['status' => 'success', 'transaction_id' => $payout->trx_id];
        }
        return ['status' => 'error', 'data' => 'API unreachable or invalid'];
    }

}
