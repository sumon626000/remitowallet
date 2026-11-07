<?php

namespace App\Services\VirtualCard\ufitpay;

use App\Models\Currency;
use App\Models\Transaction;
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
        $cardMethod = VirtualCardMethod::where('code', 'ufitpay')->firstOrFail();
        $apiUrl = 'https://api.ufitpay.com/v1';

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

        if ($operation == 'statusUpdate') {
            $res = $card->statusUpdate($apiUrl, $cardMethod, $cardOrder);
            if ($res['status'] == 'error') {
                return [
                    'status' => 'error',
                    'data' => $res['data']
                ];
            } elseif ($res['status'] == 'success') {
                return [
                    'status' => 'success',
                    'card_status' => $res['card_status'],
                ];
            }
        }
    }

    public static function createCard($apiUrl, $cardMethod, $cardOrder)
    {
        $amount = 10;
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

        $url = "$apiUrl/create_virtual_card";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "card_currency" => $cardOrder->currency,
            "amount" => $amount,
            "funding_currency" => $cardOrder->currency,
            "first_name" => optional($cardOrder->form_input->FirstName)->field_value,
            "last_name" => optional($cardOrder->form_input->LastName)->field_value,
            "email" => optional($cardOrder->form_input->Email)->field_value,
            "phone" => optional($cardOrder->form_input->Phone)->field_value,
            "bvn" => optional($cardOrder->form_input->BVN)->field_value ?? "",
            "callback_url" => route('ufitpay.Callback'),
        ];

        $response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
        $result = json_decode($response);

        if ($result->status == 'error') {
            return [
                'status' => 'error',
                'data' => $result->message
            ];
        } elseif ($result->status == 'success') {

            $card = new Card();
            $res = $card->getBalance($apiUrl, $cardMethod, $result->data->id);
            if ($res['status'] == 'error') {
                $balance = 0.00;
            } else {
                $balance = $res['balance'];
            }

            return [
                'status' => 'success',
                'name_on_card' => $result->data->name_on_card,
                'card_id' => $result->data->id,
                'cvv' => $result->data->cvv,
                'card_number' => $result->data->card_number,
                'brand' => $result->data->brand,
                'expiry_date' => dateFormat($result->data->expiry_month, $result->data->expiry_year),
                'balance' => $balance ?? 0.00,
                'data' => $result->data
            ];
        }
    }

    public static function getBalance($apiUrl, $cardMethod, $id)
    {
        $url = "$apiUrl/get_virtual_card";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "id" => $id,
        ];

        $response = BasicCurl::curlPostRequestWithHeaders($url, $headers, $postParam);
        $result = json_decode($response);

        if ($result->status == 'error') {
            return [
                'status' => 'error',
            ];
        } elseif ($result->status == 'success') {
            return [
                'status' => 'success',
                'balance' => $result->data->balance,
            ];
        }
    }

    public static function blockCard($apiUrl, $cardMethod, $cardOrder)
    {
        $url = "$apiUrl/update_card_status";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "id" => optional($cardOrder->card_info->id)->field_value,
            "status" => 'inactive',
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

    public static function unBlockCard($apiUrl, $cardMethod, $cardOrder)
    {
        $url = "$apiUrl/update_card_status";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "id" => optional($cardOrder->card_info->id)->field_value,
            "status" => 'active',
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

    public static function fundAddCard($apiUrl, $cardMethod, $cardOrder)
    {
        $url = "$apiUrl/fund_virtual_card";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "id" => optional($cardOrder->card_info->id)->field_value,
            "amount" => $cardOrder->fund_amount,
            "funding_currency" => $cardOrder->currency,
            "status" => 'active',
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

    public static function statusUpdate($apiUrl, $cardMethod, $cardOrder)
    {
        $url = "$apiUrl/get_virtual_card";
        $apiKey = optional($cardMethod->parameters)->Api_Key;
        $apiToken = optional($cardMethod->parameters)->Api_Token;
        $headers = [
            "Api-Key:$apiKey",
            "Api-Token:$apiToken",
        ];

        $postParam = [
            "id" => optional($cardOrder->card_info->id)->field_value,
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
                'card_status' => $result->data->status
            ];
        }
    }

    public function ipn(Request $request)
    {
        $apiResponse = json_decode($request->all());
        if ($apiResponse) {
            $card_id = $apiResponse->card_id;
            $cardOrder = VirtualCardOrder::where('card_id', $card_id)->first();
            if ($cardOrder) {
                $virtualCardTran = new VirtualCardTransaction();
                $virtualCardTran->user_id = $cardOrder->user_id;
                $virtualCardTran->card_order_id = $cardOrder->id;
                $virtualCardTran->card_id = $apiResponse->card_id;
                $virtualCardTran->data = $apiResponse;
                $virtualCardTran->amount = $apiResponse->amount;
                $virtualCardTran->currency_code = $apiResponse->currency;
                $virtualCardTran->save();

                $cardOrder->balance = $apiResponse->balance;
                $cardOrder->save();

                $currency = Currency::select('id')->where('code', $apiResponse->currency)->first();
                if ($currency) {

                    $remark = 'Virtual Card Credited';
                    BasicService::makeTransaction($cardOrder->user, $currency->id, $apiResponse->amount, 0, '+', strRandom(), $remark, $cardOrder->id, VirtualCardOrder::class);
                }
            }
            return 0;
        }
    }
}
