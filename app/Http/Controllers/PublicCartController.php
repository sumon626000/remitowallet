<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ProductAttrList;
use App\Models\ProductOrder;
use App\Models\StoreProductAttr;
use App\Models\StoreProductStock;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class PublicCartController extends Controller
{
	public function stockCheck(Request $request)
	{
		$stock = StoreProductStock::where('product_id', $request->productId)
			->where(function ($query) use ($request) {
				foreach ($request->attributeIds as $key => $id) {
					$query->whereJsonContains('product_attr_lists_id', $id);
				}
			})
			->first();

		if ($stock && $stock->quantity > 0) {
			return [
				'status' => true,
				'stock' => $stock->quantity,
				'message' => 'In stock'
			];
		}

		return [
			'status' => false,
			'stock' => 0,
			'message' => 'Out of stock'
		];
	}

	public function stockAttrCheck(Request $request)
	{
		$stock = StoreProductStock::where('product_id', $request->productId)
			->where(function ($query) use ($request) {
				foreach ($request->attributeIds as $key => $id) {
					$query->whereJsonContains('product_attr_lists_id', $id);
				}
			})
			->first();
		if ($stock->quantity > $request->storage_qty + 1) {
			return [
				'status' => true,
				'stock' => $stock->qty,
			];
		}
		return [
			'status' => false,
			'stock' => 0,
			'message' => 'Out of stock'
		];
	}

public function attrList(Request $request)
{
    if (empty($request->attributeIds) || !is_array($request->attributeIds)) {
        return [
            'attributes' => []
        ];
    }

    $newList = collect([]);

    ProductAttrList::with(['attrName'])
        ->whereIn('id', $request->attributeIds)
        ->get()
        ->map(function ($item) use ($newList) {
            $res = [];
            $product_attr_key = (string) optional($item->attrName)->name;
            $res[$product_attr_key] = $item->name;
            $newList->push($res);
            return $res;
        });

    return [
        'attributes' => $newList,
    ];
}

	public function productCart($link)
	{
		$data['link'] = $link;
		return view('user.store.shop.productCart', $data);
	}

	public function ProductStockCheck(Request $request)
	{
		$attr = StoreProductAttr::whereIn('id', $request->attributeIds)->get();

		$stock = StoreProductStock::where('product_id', $request->productId)
			->where(function ($query) use ($request) {
				foreach ($request->attributeIds as $key => $id) {
					$query->whereJsonContains('product_attr_lists_id', $id);
				}
			})
			->first();

		if ($stock->quantity > $request->storage_qty + 1) {
			return [
				'status' => true,
				'stock' => $stock->quantity,
				'storage' => $request->storage_qty,
				'attribute' => $attr,
			];
		}
		return [
			'status' => false,
			'productId' => $request->productId,
			'message' => 'Out of stock'
		];
	}

	public function productTrack($link, Request $request)
	{
		$order = [
			'stage' => '-1'
		];
		$order = (object)$order;
		if ($request->orderNumber) {
			$order = ProductOrder::where('order_number', $request->orderNumber)->where('status', 1)->first();
			if (!$order) {
				return back()->with('alert', 'Order Number Not Found');
			}
		}
		$data['order'] = $order;
		return view('user.store.orderTrack', $data, compact('link'));
	}

	public function productOrderDownload($orderId)
	{
		$subtotal = 0;
		$order = ProductOrder::with(['orderDetails', 'store.user'])->where('status', 1)->findOrFail($orderId);
		$currencySymbol = Currency::select('symbol')->findOrFail(optional($order->store->user)->store_currency_id);
		foreach ($order->orderDetails as $orderDetail) {
			$subtotal += $orderDetail->total_price;
		}
		$data = [
			'orderNumber' => $order->order_number,
			'date' => dateTime($order->created_at),
			'buyerName' => $order->fullname,
			'buyerEmail' => $order->email ?? null,
			'buyerPhone' => $order->phone ?? null,
			'buyerAddress' => $order->detailed_address ?? null,
			'shopName' => optional($order->store)->name ?? null,
			'shopImage' => getFile($order->store?->driver, $order->store?->image),
			'items' => $order->orderDetails,
			'currency' => $currencySymbol->symbol,
			'subtotal' => $subtotal,
			'shipping' => $order->shipping_charge ?? 0,
			'totalAmount' => $subtotal + $order->shipping_charge,
		];

        $dompdf = new Dompdf();
        $dompdf->loadHtml(view('user.store.orderPdf.order', $data)->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, 'order.pdf');

	}
}
