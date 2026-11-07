<?php

namespace App\Services\Bill\flutterwave;

use App\Models\BillMethod;
use App\Models\PayoutMethod;
use Facades\App\Services\BasicCurl;

class Card
{
	public static function fetchServices($request, $billMethod)
	{
		$api_service = $request['api_service'];
		$sec_key = optional($billMethod->parameters)->Secret_Key;
		$url = 'https://api.flutterwave.com/v3/bill-categories?' . $api_service . '=1';

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $sec_key
		];

		$response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
		$result = json_decode($response);
		if (!$result) {
			return [
				'status' => 'error',
				'data' => 'Something went wrong please contact with provider'
			];
		}
		if ($result->status == 'error') {
			return [
				'status' => 'error',
				'data' => $result->message
			];
		} elseif ($result->status == 'success') {
			return [
				'status' => 'success',
				'data' => $result->data
			];
		}
	}

	public static function payBill($billPay, $billMethod)
	{
		$billService = $billPay->service;
		$sec_key = optional($billMethod->parameters)->Secret_Key;
		$url = 'https://api.flutterwave.com/v3/bills';

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $sec_key
		];

		$postParam = [
			'country' => $billPay->country_name,
			'customer' => $billService->label_name[0],
			'amount' => (int)$billPay->amount,
			'type' => $billPay->type,
			'reference' => strRandom(16),
		];


		$response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

		if (!$result) {
			return [
				'status' => 'error',
				'data' => 'Something went wrong please contact with provider'
			];
		}
		if ($result->status == 'error') {
			return [
				'status' => 'error',
				'data' => $result->message
			];
		} elseif ($result->status == 'success') {
			return [
				'status' => 'success',
				'data' => $result->data
			];
		}
	}

	public static function getBalance($billMethod, $currencyCode)
	{

		$sec_key = optional($billMethod->parameters)->Secret_Key;
		$url = 'https://api.flutterwave.com/v3/balances/' . $currencyCode;

		$headers = [
			'Content - Type: application / json',
			'Authorization: Bearer ' . $sec_key
		];

		$response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
		$result = json_decode($response);

		if (isset($result) && isset($result->status) && isset($result->data)) {
			return [
				'status' => 'success',
				'balance' => $result->data->available_balance,
				'currencyCode' => $result->data->currency,
			];
		}

		return [
			'status' => 'error'
		];
	}
}
