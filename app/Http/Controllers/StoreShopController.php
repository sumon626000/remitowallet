<?php

namespace App\Http\Controllers;

use App\Models\ProductAttrMap;
use App\Models\SellerContact;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class StoreShopController extends Controller
{
	use Notify;

	public function shopProduct($link, Request $request)
	{
		$search = $request->all();
		$min = StoreProduct::min('price');
		$max = StoreProduct::max('price');
		$minRange = 100;
		$maxRange = 500;

		if ($request->has('my_range')) {
			$range = explode(';', $request->my_range);
			$minRange = $range[0];
			$maxRange = $range[1];
		}

		$store = Store::where('link', $link)->with(['productsMap', 'user.storeCurrency'])->where('status', 1)->firstOrFail();
		$productId = array();
		$categoryId = array();

		foreach ($store->productsMap as $productMap) {
			$productId[] = $productMap->product_id;
		}
		$data['products'] = StoreProduct::with(['storeProductStocks'])->whereIn('id', $productId)->where('status', 1)
			->when(isset($search['search']), function ($query) use ($search) {
				$query->where('name', 'LIKE', '%' . $search['search'] . '%')
					->orWhere('price', 'LIKE', '%' . $search['search'] . '%');
			})
			->when(isset($search['category']), function ($query) use ($search) {
				$query->whereIn('category_id', $search['category']);
			})
			->when(isset($search['my_range']), function ($query) use ($search, $minRange, $maxRange) {
				$query->whereBetween('price', [$minRange, $maxRange]);
			})
			->when(isset($search['attrList']), function ($query) use ($search) {
				$query->whereHas('storeProductStocks', function ($qq) use ($search) {
					$qq->whereJsonContains('product_attr_lists_id', $search['attrList']);
				});
			})
			->orderBy('id', 'desc')->paginate(basicControl()->paginate);

		foreach ($store->productsMap as $productMap) {
			$categoryId[] = optional($productMap->product)->category_id;
		}
		$categories = StoreCategory::whereIn('id', $categoryId)->get();
		$data['categories'] = $categories;

		$data['attributes'] = ProductAttrMap::with(['attribute', 'attribute.attrLists'])->whereIn('product_id', $productId)->get()->unique('attributes_id');
		$data['link'] = $link;
		Session::put('link', $link);
		return view('user.store.shop.productList', $data, compact('store', 'min', 'max', 'minRange', 'maxRange'));
	}

	public function shopProductDetails($link = null, $title, $id)
	{
		$data['link'] = $link;
		$data['product'] = StoreProduct::with(['category', 'productImages', 'user.StoreCurrency'])->findOrFail($id);
		$data['popularProducts'] = StoreProduct::where('id', '!=', $data['product']->id)->where('category_id', $data['product']->category_id)
			->orderBy('id', 'desc')->take(10)->get();
		$data['attributes'] = ProductAttrMap::with(['attribute', 'attribute.attrLists'])->where('product_id', $data['product']->id)->get()->unique('attributes_id');
		return view('user.store.shop.productDetails', $data);
	}

	public function sellerDetails($link)
	{
		$data['store'] = Store::with(['user'])->where('link', $link)->firstOrFail();
		return view('user.store.shop.sellerProfile', $data, compact('link'));
	}

	public function sellerContact(Request $request, $link)
	{
		$purifiedData = Purify::clean($request->all());
		$validationRules = [
			'name' => 'required',
			'message' => 'required',
		];

		$validate = Validator::make($purifiedData, $validationRules);
		if ($validate->fails()) {
			return back()->withErrors($validate)->withInput();
		}

		$store = Store::with('user')->where('link', $link)->firstOrFail();
		$contactForm = new SellerContact();
		$contactForm->store_id = $store->id;
		$contactForm->user_id = $store->user_id;
		$contactForm->sender_name = $request->name;
		$contactForm->message = $request->message;
		$contactForm->save();

		$params = [
			'storeName' => $store->name,
			'senderName' => $request->name ?? 'N/A',
			'message' => $request->message ?? 'N/A',
		];
		$this->sendMailSms($store->user, 'CONTACT_MESSAGE', $params);

		return back()->with('success', 'Message Send Successfully');
	}

}
