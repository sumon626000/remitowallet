<?php

namespace App\Services\Gateway\paystack;


use Facades\App\Services\BasicCurl;
use Facades\App\Services\BasicService;
use Illuminate\Support\Facades\Auth;
use App\Models\Deposit;

class Payment
{
	public static function prepareData($deposit, $gateway)
	{
		$send['key'] = $gateway->parameters?->public_key ?? '';
		$send['email'] = optional($deposit->user)->email ?? optional($deposit->depositable)->email ?? basicControl()->sender_email;
		$send['amount'] = round($deposit->payable_amount, 2) * 100;
		$send['currency'] = $deposit->payment_method_currency;
		$send['ref'] = $deposit->trx_id;
		$send['view'] = 'user.payment.paystack';
		return json_encode($send);
	}

	public static function ipn($request, $gateway, $deposit = null, $trx = null, $type = null)
	{
	    info("ipn hit...");
		$secret_key = $gateway->parameters->secret_key ?? '';
		$url = 'https://api.paystack.co/transaction/verify/' . $trx;
		$headers = [
			"Authorization: Bearer {$secret_key}"
		];
		$response = BasicCurl::curlGetRequestWithHeaders($url, $headers);
		$response = json_decode($response, true);
	    info('API Response:', $response);
		if ($response) {
			if ($response['data']) {
				if ($response['data']['status'] == 'success') {
				    info("success");
					$deposit = Deposit::with('user')->where('trx_id', $trx)->first();

					$depositAmount = round($deposit->payable_amount, 2) * 100;
					if ($response['data']['currency'] == $deposit->payment_method_currency) {
					    info("success-2");
						BasicService::preparePaymentUpgradation($deposit);
						$data['status'] = 'success';
						$data['msg'] = 'Transaction was successful.';
						$data['redirect'] = route('success');
					} else {
						$data['status'] = 'error';
						$data['msg'] = 'invalid amount.';
						$data['redirect'] = route('failed');
					}
				} else {
					$data['status'] = 'error';
					$data['msg'] = $response['data']['gateway_response'];
					$data['redirect'] = route('failed');
				}
			} else {
				$data['status'] = 'error';
				$data['msg'] = $response['message'];
				$data['redirect'] = route('failed');
			}
		} else {
			$data['status'] = 'error';
			$data['msg'] = 'unexpected error!';
			$data['redirect'] = route('failed');
		}

		return $data;
	}
}
