<?php


namespace App\Traits;

use App\Models\BillMethod;
use App\Models\BillService;
use App\Models\Gateway;
use Illuminate\Database\Eloquent\Builder;

trait BillPay
{
	public function getActiveMethod()
	{
		return BillMethod::where('is_active', 1)->firstOrFail();
	}

	public function getActiveServices()
	{
		$method = BillMethod::where('is_active', 1)->first();
		$billServices = null;
		if ($method) {
			$billServices = BillService::where('bill_method_id', $method->id)
				->where('status', 1)->groupBy('service')->pluck('service');
		}
		return $billServices;
	}

	public function convertRate($service)
	{
		try {
			$method = $service->method;
			$serviceCurrency = $service->currency;
			if ($serviceCurrency == basicControl()->base_currency) {
				return 1.00;
			}
			if ($method->convert_rate) {
				return (float)$method->convert_rate->$serviceCurrency;
			}
			return 1.00;
		} catch (\Exception $e) {
			return 1.00;
		}
	}

	function calculateCharge($amount, $service)
	{
		$fromPercent = $amount * $service->percent_charge / 100;
		$charge = $fromPercent + $service->fixed_charge;
		return $charge;
	}

	public function checkAmountValidate($amount, $methodId)
	{
		$baseCurrency = basicControl()->base_currency;
		$gateway = Gateway::where('status', 1)->find($methodId);
		$status = false;
		if (!$gateway) {
			$data['status'] = $status;
			$data['message'] = "Gateway is currently unreachable";
			return $data;
		}

		$percentage = $gateway->percentage_charge;
		$percentage_charge = ($amount * $percentage) / 100;
		$fixed_charge = $gateway->fixed_charge;
		$min_limit = getAmount($gateway->min_amount, 2);
		$max_limit = getAmount($gateway->max_amount, 2);
		$charge = $percentage_charge + $fixed_charge;

		//Total amount with all fixed and percent charge for deduct

		$payable_amount = $amount + $charge;


		//Currency inactive
		if ($min_limit == 0 && $max_limit == 0) {
			$message = "Payment method not available for this transaction";
		} elseif ($amount < $min_limit || $amount > $max_limit) {
			$message = "minimum payment $min_limit $baseCurrency and maximum payment limit $max_limit $baseCurrency";
		} else {
			$status = true;
			$message = "Updated balance";
		}

		$data['status'] = $status;
		$data['message'] = $message;
		$data['fixed_charge'] = $fixed_charge;
		$data['percentage'] = $percentage;
		$data['percentage_charge'] = $percentage_charge;
		$data['min_limit'] = $min_limit;
		$data['max_limit'] = $max_limit;
		$data['payable_amount'] = $payable_amount;
		$data['charge'] = $charge;
		$data['amount'] = $amount;
		$data['convention_rate'] = $gateway->convention_rate;

		return $data;
	}

	public function getFormatRes($res, $api_service, $billMethod)
	{
		$res = (array)$res;
		switch ($billMethod->code) {
			case 'flutterwave':
				return [
					'code' => $res['biller_code'],
					'type' => $res['biller_name'],
					'amount' => $res['amount'],
					'label_name' => [
						title2snake($res['label_name'])
					],
					'extra' => null,
					'min_amount' => 0,
					'max_amount' => 0,
					'fixed_charge' => 0
				];

			case 'reloadly':
				if ($api_service == 'AIRTIME') {
					return [
						'code' => $res['operatorId'],
						'type' => $res['name'],
						'amount' => ($res['denominationType'] == 'RANGE') ? 0 : -1,
						'label_name' => ['Recipient_Phone'],
						'extra' => getReloadlyFixedAmountAirtime($res ?? []) ?? null,
						'min_amount' => $res['localMinAmount'] ?? 0,
						'max_amount' => $res['localMaxAmount'] ?? 0,
						'fixed_charge' => 0
					];

				} else {
					return [
						'code' => $res['id'],
						'type' => $res['name'],
						'amount' => ($res['denominationType'] == 'RANGE') ? 0 : -1,
						'label_name' => ['Account_Number_Or_Card_number'],
						'extra' => $res['localFixedAmounts'] ?? null,
						'min_amount' => $res['minLocalTransactionAmount'] ?? 0,
						'max_amount' => $res['maxLocalTransactionAmount'] ?? 0,
						'fixed_charge' => $res['localTransactionFee'] ?? 0
					];
				}
		}
	}

	public function getCountry($billMethod, $response, $api_service = null)
	{
		$response = (array)$response;
		switch ($billMethod->code) {
			case 'bloc':
				return 'NG';
			case 'flutterwave':
				return $response['country'];
			case 'tpaga':
				return 'CO';
			case 'reloadly':
				return $response['countryCode'] ?? $response['country']->isoName ?? $response['country']['isoName'];
		}
	}

	public function getIsoCode($countryCode)
	{
		$countryLists = config('country');
		foreach ($countryLists as $value) {
			if ($countryCode == $value['code']) {
				$currency = $value['iso_code'];
				break;
			}
		}
		return $currency;
	}

}
