<?php

namespace App\Services\VirtualCard\flutterwave;

use App\Models\Currency;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use Facades\App\Services\BasicCurl;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;

class Card
{
	public static function cardRequest($cardOrder, $operation)
	{
		$cardMethod = VirtualCardMethod::where('code', 'flutterwave')->firstOrFail();
		$apiUrl = 'https://api.flutterwave.com/v3';

		$card = new Card();

		if ($operation == 'create') {
			$res = $card->createCard($apiUrl, $cardMethod, $cardOrder);
			if ($res['status'] == 'error') {
				return [
					'status' => 'error',
					'data' => $res['data']
				];
			} elseif ($res['status'] == 'success') {
				return [
					'status' => 'success',
					'name_on_card' => $res['name_on_card'],
					'card_id' => $res['card_id'],
					'cvv' => $res['cvv'],
					'card_number' => $res['card_number'],
					'brand' => $res['brand'],
					'expiry_date' => $res['expiry_date'],
					'balance' => $res['balance'],
					'data' => $res['data']
				];
			}
		}

		if ($operation == 'block') {
			$res = $card->blockCard($apiUrl, $cardMethod, $cardOrder);
			if ($res['status'] == 'error') {
				return [
					'status' => 'error',
					'data' => $res['data']
				];
			} elseif ($res['status'] == 'success') {
				return [
					'status' => 'success',
					'data' => $res['data']
				];
			}
		}

		if ($operation == 'unblock') {
			$res = $card->unBlockCard($apiUrl, $cardMethod, $cardOrder);
			if ($res['status'] == 'error') {
				return [
					'status' => 'error',
					'data' => $res['data']
				];
			} elseif ($res['status'] == 'success') {
				return [
					'status' => 'success',
					'data' => $res['data']
				];
			}
		}

		if ($operation == 'fundApprove') {
			$res = $card->fundAddCard($apiUrl, $cardMethod, $cardOrder);
			if ($res['status'] == 'error') {
				return [
					'status' => 'error',
					'data' => $res['data']
				];
			} elseif ($res['status'] == 'success') {
				return [
					'status' => 'success',
					'balance' => $res['balance'],
					'data' => $res['data']
				];
			}
		}
	}

	public static function createCard($apiUrl, $cardMethod, $cardOrder)
	{
		$amount = 5;
		$orderCurrency = $cardOrder->currency;
		foreach ($cardMethod->add_fund_parameter as $key => $param) {
			if ($key == $orderCurrency) {
				foreach ($param as $key => $item) {
					if ($key == 'OpeningAmount') {
						$amount = $item->field_value;
					}
				}
			}
		}

		$url = "$apiUrl/virtual-cards";
		$SEC_KEY = optional($cardMethod->parameters)->secret_key;
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer $SEC_KEY"
		];

		$postParam = [
			"currency" => $cardOrder->currency,
			"amount" => $amount,
			"debit_currency" => $cardMethod->debit_currency,
			"billing_name" => optional($cardOrder->form_input->BillingName)->field_value,
			"billing_address" => optional($cardOrder->form_input->BillingAddress)->field_value,
			"billing_city" => optional($cardOrder->form_input->BillingCity)->field_value,
			"billing_state" => optional($cardOrder->form_input->BillingState)->field_value,
			"billing_postal_code" => optional($cardOrder->form_input->BillingPostalCode)->field_value,
			"billing_country" => optional($cardOrder->form_input->BillingCountry)->field_value,
			"first_name" => optional($cardOrder->form_input->FirstName)->field_value,
			"last_name" => optional($cardOrder->form_input->LastName)->field_value,
			"date_of_birth" => optional($cardOrder->form_input->DateOfBirth)->field_value,
			"email" => optional($cardOrder->form_input->Email)->field_value,
			"phone" => optional($cardOrder->form_input->Phone)->field_value,
			"title" => optional($cardOrder->form_input->Title)->field_value,
			"gender" => optional($cardOrder->form_input->Gender)->field_value,
			"callback_url" => route('flutterwave.Callback'),
		];

		$response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

		if ($result->status == 'error') {
			return [
				'status' => 'error',
				'data' => $result->message
			];
		} elseif ($result->status == 'success') {
			return [
				'status' => 'success',
				'name_on_card' => $result->data->name_on_card,
				'card_id' => $result->data->account_id,
				'cvv' => $result->data->cvv,
				'card_number' => $result->data->account_id,
				'brand' => $result->data->card_type,
				'expiry_date' => dateflatterwaveFormat($result->data->expiration),
				'balance' => $result->data->amount,
				'data' => $result->data
			];
		}
	}

	public static function blockCard($apiUrl, $cardMethod, $cardOrder)
	{
		$id = $cardOrder->card_Id;
		$url = "$apiUrl/virtual-cards/$id/status/block";
		$SEC_KEY = optional($cardMethod->parameters)->secret_key;
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer $SEC_KEY"
		];

		$postParam = [
		];

		$response = BasicCurl::curlPutRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

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

	public static function unBlockCard($apiUrl, $cardMethod, $cardOrder)
	{
		$id = $cardOrder->card_Id;
		$url = "$apiUrl/virtual-cards/$id/status/unblock";
		$SEC_KEY = optional($cardMethod->parameters)->secret_key;
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer $SEC_KEY"
		];

		$postParam = [
		];

		$response = BasicCurl::curlPutRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

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

	public static function fundAddCard($apiUrl, $cardMethod, $cardOrder)
	{
		$id = $cardOrder->card_Id;
		$url = "$apiUrl/virtual-cards/$id/fund";
		$SEC_KEY = optional($cardMethod->parameters)->secret_key;
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer $SEC_KEY"
		];

		$postParam = [
			"debit_currency" => $cardMethod->debit_currency,
			"amount" => $cardMethod->fund_amount,
		];

		$response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
		$result = json_decode($response);

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

	public static function getBalance($account_id)
	{
		$cardInfo = VirtualCardOrder::select('card_info')->where('card_Id', $account_id)->first();
		$id = null;
		if ($cardInfo) {
			$id = $cardInfo->id->field_value;
		}

		$url = "https://api.flutterwave.com/v3/virtual-cards/$id";
		$SEC_KEY = VirtualCardMethod::select('secret_key')->where('code', 'flutterwave')->first();
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer $SEC_KEY"
		];


		$response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
		$result = json_decode($response);

		if ($result->status == 'error') {
			return [
				'status' => 'error',
			];
		} elseif ($result->status == 'success') {
			return [
				'status' => 'success',
				'balance' => $result->amount
			];
		}
	}

    public function ipn(Request $request)
    {
        $apiResponse = json_decode($request->all());
        if ($apiResponse) {
            if ($apiResponse->event == 'charge.completed') {
                $card_id = $apiResponse->data->account_id;
                $cardOrder = VirtualCardOrder::where('card_id', $card_id)->first();
                if ($cardOrder) {
                    $virtualCardTran = new VirtualCardTransaction();
                    $virtualCardTran->user_id = $cardOrder->user_id;
                    $virtualCardTran->card_order_id = $cardOrder->id;
                    $virtualCardTran->card_id = $apiResponse->data->account_id;
                    $virtualCardTran->data = $apiResponse->data;
                    $virtualCardTran->amount = $apiResponse->data->amount;
                    $virtualCardTran->currency_code = $apiResponse->data->currency;
                    $virtualCardTran->save();

                    $methodObj = 'App\\Services\\VirtualCard\\flutterwave\\Card';
                    $data = $methodObj::getBalance($apiResponse->data->account_id);

                    if ($data) {
                        $cardOrder->balance = $data['balance'];
                        $cardOrder->save();
                    }

                    $currency = Currency::select('id')->where('code', $apiResponse->data->currency)->first();
                    if ($currency) {
                        $remark = 'Virtual Card Credited';
                        BasicService::makeTransaction($cardOrder->user, $currency->id, $apiResponse->data->amount, $apiResponse->data->charged_amount, '+', strRandom(), $remark, $cardOrder->id, VirtualCardOrder::class);
                    }
                }
                return response(200);
            }
        }
    }
}
