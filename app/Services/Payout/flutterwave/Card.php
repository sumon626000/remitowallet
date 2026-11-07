<?php

namespace App\Services\Payout\flutterwave;

use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Traits\PayoutTrait;
use Facades\App\Services\BasicCurl;

class Card
{
    use PayoutTrait;

    public static function getBank($countryCode)
    {
        $method = PayoutMethod::where('code', 'flutterwave')->first();
        $url = 'https://api.flutterwave.com/v3/banks/' . strtoupper($countryCode);
        $SEC_KEY = optional($method->parameters)->Secret_Key;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $SEC_KEY
        ];

        $response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
        $result = json_decode($response);

        if (!isset($result->status)) {
            return [
                'status' => 'error',
                'data'   => 'Something went wrong try again'
            ];
        }
        if ($result->status == 'error') {
            return [
                'status' => 'error',
                'data'   => $result->message
            ];
        } elseif ($result->status == 'success') {
            return [
                'status' => 'success',
                'data'   => $result->data
            ];
        }
    }

    public static function payouts($payout)
    {
        $method  = PayoutMethod::where('code', 'flutterwave')->first();
        $url     = 'https://api.flutterwave.com/v3/transfers';
        $SEC_KEY = optional($method->parameters)->Secret_Key;

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $SEC_KEY
        ];

        $postParam['currency'] = $payout->payout_currency_code;

        foreach ($payout->information as $key => $info) {
            $postParam[$key] = $info->field_value;
        }

        if ($payout->meta_field) {
            foreach ($payout->meta_field as $key => $info) {
                $postParam['meta'][$key] = $info->field_value;
            }
        }

        $postParam['amount']       = (int) $postParam['amount'];
        $postParam['reference']    = $payout->trx_id;
        $postParam['narration']    = 'Payout via ' . optional($method)->name;
        $postParam['beneficiary_name'] = optional($payout->user)->name ?? 'Beneficiary';
        $postParam['callback_url'] = route('payout', $payout->method?->code);

        $response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
        $result   = json_decode($response);

        # Log the raw response from FW →→→→→→→
        //\Log::info('[FW Payout] Raw response', ['trx_id'   => $payout->trx_id, 'response' => $response,]);

        if (isset($result->status) && $result->status == 'error') {
            return [
                'status' => 'error',
                'data'   => ($result->message ?? 'Unknown error'),
            ];
        } elseif (isset($result->status) && $result->status == 'success') {
            return [
                'status'      => 'success',
                'response_id' => $result->data->id,
            ];
        }

        # Catch cases where FW returns something unexpected →→→→→→→
        return [
            'status' => 'error',
            'data'   => 'No valid response from FlutterWave | raw: ' . $response,
        ];
    }

    public static function webhook($apiResponse)
    {
        if ($apiResponse) {
            if ($apiResponse->event == 'transfer.completed') {
                if ($apiResponse->data) {
                    $payout = Payout::where('response_id', $apiResponse->data->id)->first();
                    if ($payout) {
                        if ($apiResponse->data->status == 'SUCCESSFUL') {

                            $payout->status = 2;
                            $payout->save();

                            $remark = "Withdraw via flutterwave";
                            (new self)->createPayoutTransaction($payout, '-', $remark);
                        }
                        if ($apiResponse->data->status == 'FAILED') {
                            $payout->status = 6;
                            $payout->last_error = $apiResponse->data->complete_message;
                            $payout->save();
                            updateWallet($payout->user_id, $payout->currency_id, $payout->amount, 1);
                        }
                    }
                }
            }
        }
    }
}
