<?php

namespace App\Services\Bill\reloadly;

use Facades\App\Services\BasicCurl;

class Card
{
	protected $baseUrl;
	protected $airtimeBaseUrl;

	public function __construct($mood = 'test')
	{
		if ($mood == 'test') {
			$this->baseUrl = 'https://utilities-sandbox.reloadly.com';
			$this->airtimeBaseUrl = 'https://topups-sandbox.reloadly.com';
		} else {
			$this->baseUrl = 'https://utilities.reloadly.com';
			$this->airtimeBaseUrl = 'https://topups.reloadly.com';
		}
	}

	public function accessToken($billMethod, $audience)
	{
		$curl = curl_init();
		$postParams = [
			'client_id' => $billMethod->parameters->Client_Id,
			'client_secret' => $billMethod->parameters->Client_Secret,
			'grant_type' => 'client_credentials',
			'audience' => $audience,
		];

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://auth.reloadly.com/oauth/token",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($postParams),
			CURLOPT_HTTPHEADER => [
				"Accept: application/json",
				"Content-Type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		$res = json_decode($response);

		if (isset($res->access_token)) {
			return $res->access_token;
		}
		return 'abc';
	}

	public static function fetchServices($request, $billMethod)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->baseUrl);
		$url = $object->baseUrl . '/billers?size=100&type=' . $request['api_service'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $bearerToken
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = json_decode($response);

		if (isset($result->content) && count($result->content) > 0) {
			return [
				'status' => 'success',
				'data' => $result->content
			];
		}

		return [
			'status' => 'error',
			'data' => 'Content not found please contact with provider'
		];
	}

	public static function payBill($billPay, $billMethod)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->baseUrl);
		$url = $object->baseUrl . '/pay';

		$invoiceId = null;
		if ($billPay->service->code == 23) {
			$invoiceId = '5829456988423AH';
		} elseif ($billPay->service->code == 24) {
			$invoiceId = '993GC449433';
		}


		$postParams = [
			"subscriberAccountNumber" => $billPay->customer->Account_Number_Or_Card_number->Account_Number_Or_Card_number ?? 'unknown',
			"amount" => (int)$billPay->amount,
			"amountId" => $billPay->amount_id ?? null,
			"billerId" => (int)optional($billPay->service)->code ?? 0,
			"referenceId" => strRandom(12),
			"useLocalAmount" => true,
			"additionalInfo" => [
				"invoiceId" => $invoiceId
			]
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($postParams),
			CURLOPT_HTTPHEADER => [
				"Accept: application/com.reloadly.utilities-v1+json",
				"Authorization: Bearer " . $bearerToken,
				"Content-Type: application/json"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = json_decode($response);

		if (isset($result) && isset($result->status)) {
			if ($result->status == 'SUCCESSFUL') {
				return [
					'status' => 'success',
					'data' => $result
				];
			} elseif ($result->status == 'FAILED' || $result->status == 'REFUNDED') {
				return [
					'status' => 'error',
					'data' => $result->message ?? 'Unknown error Found',
				];
			} elseif ($result->status == 'PROCESSING') {
				return [
					'status' => 'processing',
					'data' => $result->id,
				];
			}
		}

		return [
			'status' => 'error',
			'data' => $result->message ?? 'Unknown error Found',
		];
	}

	//{#1649 ▼
	//+"id": 952
	//+"status": "PROCESSING"
	//+"referenceId": "QHGZVCX8QR71"
	//+"code": "PAYMENT_PROCESSING_IN_PROGRESS"
	//+"message": "The payment is being processed, status will be updated when biller processes the payment."
	//+"submittedAt": "2023-09-21 06:24:21"
	//+"finalStatusAvailabilityAt": "2023-09-22 06:24:21"
	//}

	public static function getStatus($billPay, $billMethod)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->baseUrl);
		$url = $object->baseUrl . '/transactions/' . $billPay->reference_id;

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Accept: application/com.reloadly.utilities-v1+json",
				"Authorization: Bearer " . $bearerToken
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		$result = json_decode($response);
		if (isset($result) && isset($result->transaction)) {
			return [
				'status' => $result->transaction->status,
				'message' => $result->message,
			];
		}
		return [
			'status' => 'error'
		];
	}

	public static function getBalance($billMethod, $currencyCode = null)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->baseUrl);
		$url = $object->baseUrl . '/accounts/balance';

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Accept: application/com.reloadly.utilities-v1+json",
				"Authorization: Bearer " . $bearerToken
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		$result = json_decode($response);
		if (isset($result) && isset($result->balance)) {
			return [
				'status' => 'success',
				'balance' => $result->balance,
				'currencyCode' => $result->currencyCode,
			];
		}

		return [
			'status' => 'error'
		];
	}

	public static function fetchOperators($request, $billMethod)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->airtimeBaseUrl);
		$url = $object->airtimeBaseUrl . '/operators';

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $bearerToken,
				'Accept: application/com.reloadly.topups-v1+json',
			),
		));
		$response = curl_exec($curl);

		curl_close($curl);

		$result = json_decode($response);

		if (isset($result->content) && count($result->content) > 0) {
			return [
				'status' => 'success',
				'data' => $result->content
			];
		}

		return [
			'status' => 'error',
			'data' => 'Content not found please contact with provider'
		];
	}

	public static function payAirtimeBill($billPay, $billMethod)
	{
		$object = new Card();
		$bearerToken = $object->accessToken($billMethod, $object->airtimeBaseUrl);
		$url = $object->airtimeBaseUrl . '/topups';

		$postParams = [
			"amount" => (string)$billPay->amount_id,
			"operatorId" => (string)$billPay->service->code,
			"useLocalAmount" => !($billPay->service->amount == -1),
			"customIdentifier" => strRandom(12),
			"recipientPhone" => [
				"countryCode" => $billPay->country_name,
				"number" => getPhoneCode($billPay->country_name) . $billPay->customer->Recipient_Phone->Recipient_Phone ?? '01772991688'
			],
			"senderPhone" => [
				"countryCode" => "CA",
				"number" => "1231231231"
			]
		];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => json_encode($postParams),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $bearerToken,
				'Accept: application/com.reloadly.topups-v1+json',
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);

		$response = json_decode($response);

		if (isset($response->status) && $response->status == 'SUCCESSFUL') {
			return [
				'status' => 'success',
				'data' => $response
			];
		}

		return [
			'status' => 'error',
			'data' => $response->message ?? 'Unknown error Found',
		];
	}
}
