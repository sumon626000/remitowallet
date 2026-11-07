<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ChargesLimit;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Store;
use App\Models\StoreProductStock;
use App\Models\StoreShipping;
use App\Traits\PaymentTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class PublicChekoutController extends Controller
{
    use PaymentTrait;

    private function normalizeAttributeIds($attributes): array
    {
        if (is_string($attributes)) {
            $decoded = json_decode($attributes, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $attributes = $decoded;
            } else {
                $attributes = array_filter(array_map('trim', explode(',', $attributes)));
            }
        }

        if (!is_array($attributes)) {
            return [];
        }

        return array_values(array_filter($attributes, fn($value) => $value !== null && $value !== ''));
    }

    private function getGatewayConversionRate(?Gateway $gateway, ?string $currencyCode): ?float
    {
        if (!$gateway || !$currencyCode || !is_array($gateway->receivable_currencies)) {
            return null;
        }

        $currencyInfo = collect($gateway->receivable_currencies)->first(function ($item) use ($currencyCode) {
            $itemCurrency = $item->currency ?? $item->name ?? $item->currency_symbol ?? null;
            return strtoupper((string) $itemCurrency) === strtoupper($currencyCode);
        });

        $conversionRate = (float) ($currencyInfo->conversion_rate ?? 0);

        return $conversionRate > 0 ? $conversionRate : null;
    }

	public function productCheckout($link)
	{
		$store = Store::with('user')->where('link', $link)->firstOrFail();
		$data['link'] = $link;
		$data['shippingAdds'] = StoreShipping::where('status', 1)->where('store_id', $store->id)->orderBy('address', 'asc')->get();
		$data['gateways'] = Gateway::getActiveMethods();
        $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type', 'is_active')->where('is_active', 1)->get();
        return view('user.store.shop.checkout', $data, compact('store'));
	}

	public function productCheckoutStore(Request $request)
	{
		$purifiedData = Purify::clean($request->all());
		$validator = Validator::make($purifiedData, [
			'name' => 'required',
			'email' => 'required',
			'phone' => 'required',
			'shippingId' => 'required',
			'detailAddress' => 'required',
			'methodId' => 'required',
		]);
		if ($validator->fails()) {
			return response()->json([
				'status' => 'error'
			]);
		}

		$cartItem = $request->cartItem;
		if (count($cartItem) < 1) {
			return response()->json([
				'status' => 'emptyCart'
			]);
		}

		foreach ($cartItem as $item) {
            $attributeIds = $this->normalizeAttributeIds($item['attributes'] ?? []);
			$stock = StoreProductStock::with('product')
                ->where('product_id', $item['id'])
                ->when(!empty($attributeIds), function ($query) use ($attributeIds) {
                    foreach ($attributeIds as $attributeId) {
                        $query->where(function ($subQuery) use ($attributeId) {
                            $subQuery->whereJsonContains('product_attr_lists_id', (string) $attributeId)
                                ->orWhereJsonContains('product_attr_lists_id', (int) $attributeId);
                        });
                    }
                })
                ->first();
            $requestedQuantity = $item['count'] ?? $item['quantity'] ?? 0;
			if (!$stock || $requestedQuantity > $stock->quantity) {
				$productName = optional($stock?->product)->name ?? 'Unknown Item';
				return response()->json([
					'status' => 'stockOut',
					'product_name' => $productName
				]);
			}
		}

		$totalAmount = 0;
		$shipping = StoreShipping::with('store', 'user')->where('id', $request->shippingId)->first();

		foreach ($cartItem as $item) {
            $requestedQuantity = $item['quantity'] ?? $item['count'] ?? 0;
			$totalAmount += $item['price'] * $requestedQuantity;
		}

		$methodId = $request->methodId;
		$currencyInput = $request->currency_id;
        $currency_id = is_numeric($currencyInput)
            ? (int) $currencyInput
            : Currency::where('code', $currencyInput)->value('id');

        if (!$currency_id) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Selected currency is invalid for this checkout.'
            ]);
        }
        $store_currency_id = $shipping->user?->store_currency_id;
        $selectedCurrencyCode = Currency::where('id', $currency_id)->value('code');
        $storeCurrencyRate = Currency::where('id', $store_currency_id)->value('exchange_rate');
        $gateway = Gateway::where('id', $methodId)->where('status', 1)->first();
        $chargesLimit = ChargesLimit::where([
            'currency_id' => $currency_id,
            'transaction_type_id' => config('transactionType.deposit'),
            'payment_method_id' => $methodId,
            'is_active' => 1,
        ])->first();

        $scRate = $this->getCurrencyRate($currency_id, $store_currency_id);
        if ((!$scRate || $scRate == 0) && $selectedCurrencyCode && $storeCurrencyRate > 0) {
            $gatewayConversionRate = $this->getGatewayConversionRate($gateway, $selectedCurrencyCode);

            if ($gatewayConversionRate) {
                $scRate = $storeCurrencyRate / $gatewayConversionRate;
            }
        }

        if ((!$scRate || $scRate == 0) && $currency_id && $store_currency_id) {
            $selectedCurrencyRate = Currency::where('id', $currency_id)->value('exchange_rate');

            if ($selectedCurrencyRate && $storeCurrencyRate && $selectedCurrencyRate != 0) {
                $scRate = $storeCurrencyRate / $selectedCurrencyRate;
            } elseif ((int) $currency_id === (int) $store_currency_id) {
                $scRate = 1;
            }
        }

        if ((!$scRate || $scRate == 0) && $chargesLimit?->convention_rate > 0) {
            $scRate = (float) $chargesLimit->convention_rate;
        }

        if (!$scRate || $scRate == 0) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Currency conversion rate is not available for this checkout.'
            ]);
        }
        $scAmount = $totalAmount / $scRate;

        $shippingCharge = $shipping->store?->shipping_charge == 1 ? $shipping->charge : 0;
        $scShippingCharge = $shippingCharge / $scRate;

		$checkAmountValidate = $this->validatePayment($scAmount, $currency_id,
            config('transactionType.deposit'), $methodId, $scShippingCharge);//7 = deposit
		if (!$checkAmountValidate['status']) {
			return response()->json([
				'status' => 'fail',
				'message' => $checkAmountValidate['message']
			]);
		}

		DB::beginTransaction();
		$order = new ProductOrder();
		$order->order_number = mt_rand(10000000, 99999999);
		$order->gateway_id = $request->methodId;
		$order->store_id = $shipping->store_id;
		$order->user_id = $shipping->user_id;
		$order->currency_id = optional($shipping->user)->store_currency_id;
		$order->fullname = $request->name;
		$order->email = $request->email;
		$order->phone = $request->phone;
		$order->alt_phone = $request->altPhone;
		$order->total_amount = $totalAmount;
		$order->shipping_id = $shipping->id;
		$order->shipping_charge = $shippingCharge;
		$order->detailed_address = $request->detailAddress;
		$order->order_note = $request->orderNote;
		$order->save();

		foreach ($cartItem as $item) {
            $requestedQuantity = $item['count'] ?? $item['quantity'] ?? 0;
			$orderDetails = new ProductOrderDetail();
			$orderDetails->order_id = $order->id;
			$orderDetails->product_id = $item['id'];
			$orderDetails->attributes_id = $item['attributes'];
			$orderDetails->quantity = $requestedQuantity;
			$orderDetails->price = $item['price'];
			$orderDetails->total_price = $requestedQuantity * $item['price'];
			$orderDetails->save();
		}

        $deposit  = $this->createDeposit($checkAmountValidate, ProductOrder::class, $order->id);

		$order->utr = $deposit->trx_id;
		$order->save();

		DB::commit();
		return response()->json([
			'status' => 'success',
			'route' => route('payment.process', $deposit->trx_id)
		]);

	}


}
