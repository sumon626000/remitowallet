<?php

namespace App\Services\Payout\paypal;

use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Traits\PayoutTrait;
use Facades\App\Services\BasicCurl;

class Card
{
    use PayoutTrait;
	public static function payouts($payout)
	{
		$method = PayoutMethod::where('code', 'paypal')->first();
		$info = $payout->information;

		if ($method->environment == 'live') {
			$api = 'https://api-m.paypal.com/v1/';
		} else {
			$api = 'https://api-m.sandbox.paypal.com/v1/';
		}

		$CLIENT_ID = optional($method->parameters)->cleint_id;
		$KEY_SECRET = optional($method->parameters)->secret;

		$url = $api . 'payments/payouts';
		$recipient_type = $info->recipient_type->field_value;
		$value = $info->amount->field_value;
		$receiver = $info->receiver->field_value;

		$headers = [
			'Content-Type: application/json',
			'Authorization: Basic ' . base64_encode("{$CLIENT_ID}:{$KEY_SECRET}")
		];

		$postParam = [
			"sender_batch_header" => [
				"sender_batch_id" => substr(md5(mt_rand()), 0, 10),
				"email_subject" => "You have a payout!",
				"email_message" => "You have received a payout! Thanks for using our service!",
			],
			"items" => [
				[
					"recipient_type" => $recipient_type,
					"amount" => [
						"value" => $value,
						"currency" => $payout->payout_currency_code
					],
					"receiver" => $receiver,
				]
			]
		];

		$response = BasicCurl::payoutCurlPostRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);
       
		if (isset($result->batch_header)) {
			return [
				'status' => 'success',
				'response_id' => $result->batch_header->payout_batch_id
			];
		} else {
			return [
				'status' => 'error',
				'data' => $result->error_description
			];
		}
	}

    public static function webhook($apiResponse)
    {
        if ($apiResponse) {
            if ($apiResponse->batch_header) {
                $payout = Payout::where('response_id', $apiResponse->batch_header->payout_batch_id)->first();
                if ($payout) {
                    if ($apiResponse->event_type == 'PAYMENT.PAYOUTSBATCH.SUCCESS' || $apiResponse->event_type == 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED' || $apiResponse->event_type == 'PAYMENT.PAYOUTSBATCH.PROCESSING') {
                        if ($apiResponse->event_type != 'PAYMENT.PAYOUTSBATCH.PROCESSING') {

                            $payout->status = 2;
                            $payout->save();

                            $remark= "Withdraw via paypal";
                            (new self)->createPayoutTransaction($payout, '-', $remark);
                        }
                    } else {
                        $payout->status = 6;
                        $payout->last_error = $apiResponse->summary;
                        $payout->save();
                        updateWallet($payout->user_id, $payout->currency_id, $payout->amount, 1);
                    }
                }
            }
        }
    }
}
