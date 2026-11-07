<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductAttrList;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProductAttr;
use App\Traits\ApiValidation;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    use ApiValidation,Upload;
    public function category()
    {
        $categories = StoreCategory::get();
        return $this->withSuccess($categories);
    }

    public function categoryStore(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()[0]));
        }

        $storeCategory = new StoreCategory();
        $storeCategory->user_id = Auth::id();
        $storeCategory->name = $request->name;
        $storeCategory->status = $request->status;

        $storeCategory->save();

        return $this->withSuccess('Category has been created');
    }

    public function categoryEdit($id)
    {
        $category = StoreCategory::where('id', $id)->first();
        if (!$category) {
            return  $this->withErrors('Category not found');
        }
        return $this->withSuccess($category);
    }

    public function categoryUpdate(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()[0]));
        }

        $storeCategory = StoreCategory::own()->findOrFail($id);
        $storeCategory->name = $request->name;
        $storeCategory->status = $request->status;

        $storeCategory->save();
        return $this->withSuccess('Category has been updated');
    }

    public function categoryDelete($id)
    {
        $storeCategory = StoreCategory::own()->with(['products'])->findOrFail($id);
        if (count($storeCategory->products) > 0) {
            return $this->withErrors('This category has lot of products');
        }
        $storeCategory->delete();
        return $this->withSuccess('Category has been deleted');
    }

    public function storeList()
    {
        $stores = Store::get()->map(function ($store) {
            $route = route('public.view', $store->link);
            return [
                'id' => $store->id,
                'user_id' => $store->user_id,
                'name' => $store->name,
                'image' => getFile($store->driver,$store->image),
                'shipping_charge' => $store->shipping_charge,
                'delivery_note' => $store->delivery_note,
                'status' => $store->status == 1?'Active':'Inactive',
                'link' => $route,
                'short_description' => $store->short_description,
                'created_at' => $store->created_at,
                'updated_at' => $store->updated_at,
                'deleted_at' => $store->deleted_at,
            ];
        });
        return $this->withSuccess($stores);
    }

    public function createStore(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
            'shipping_charge' => 'required',
            'delivery_note' => 'required',
            'status' => 'required',
            'image' => 'required',
            'short_description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()));
        }
        if (auth()->user()->store_currency_id == null) {
            return $this->withErrors('Please Set Currency From Setting Option');
        }
        $store = Store::where('link', $request->link)->exists();
        if ($store) {
            return $this->withErrors('Link Already Exists');
        }

        if (preg_match('/' . preg_quote('/', '/') . '/', $request->link)) {
            return  $this->withErrors('"/" can not be uses');
        }

        $store = new Store();
        $store->user_id = Auth::id();
        $store->name = $purifiedData['name'];
        $store->shipping_charge = $purifiedData['shipping_charge'];
        $store->status = $purifiedData['status'];
        $store->delivery_note = $purifiedData['delivery_note'];
        if ($request->hasFile('image')) {
            $upload = $this->fileUpload($request->image, config('filelocation.store.path'), null, config('filelocation.store.size'), 'webp');
        }
        $store->image = $upload['path'] ?? null;
        $store->driver = $upload['driver'] ?? null;
        $store->short_description = $purifiedData['short_description'];
        $store->save();

        if ($request->link == null) {
            $data = $store->id . '|' . $store->name;
            $store->link = $this->encrypt($data);
        } else {
            $store->link = $purifiedData['link'];
        }

        $store->save();
        return $this->withSuccess('Store has been created');
    }
    public function encrypt($data)
    {
        return implode(unpack("H*", $data));
    }

    public function editStore($id)
    {
        $store = Store::where('id',$id)->first();

        if (!$store){
            return $this->withErrors('Store not found');
        }

        $route = route('public.view', $store->link);

        return $this->withSuccess([
            'id' => $store->id,
            'user_id' => $store->user_id,
            'name' => $store->name,
            'image' => getFile($store->driver,$store->image),
            'shipping_charge' => $store->shipping_charge,
            'delivery_note' => $store->delivery_note,
            'status' => $store->status == 1?'Active':'Inactive',
            'link' => $route,
            'short_description' => $store->short_description,
            'created_at' => $store->created_at,
            'updated_at' => $store->updated_at,
            'deleted_at' => $store->deleted_at,
        ]);
    }

    public function updateStore(Request $request,$id)
    {
        $data['store'] = Store::own()->where('id',$id)->first();

        if (!$data['store']){
            return  $this->withErrors('Store not found');
        }
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
            'shipping_charge' => 'required',
            'delivery_note' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()));
        }
        $store = Store::where('link', $purifiedData['link']??null)->where('id', '!=', $data['store']->id)->exists();
        if ($store) {
            return $this->withErrors('Link Already Exists');
        }

        if (isset($purifiedData['link']) && preg_match('/' . preg_quote('/', '/') . '/', $purifiedData['link'])) {
            return $this->withErrors('"/" can not be uses');
        }

        $data['store']->name = $purifiedData['name'];
        $data['store']->shipping_charge = $purifiedData['shipping_charge'];
        $data['store']->status = $purifiedData['status'];
        $data['store']->delivery_note = $purifiedData['delivery_note'];
        $data['store']->short_description = $purifiedData['short_description']??$data['store']->short_description;
        if ($request->image) {
            $upload = $this->fileUpload($request->image, config('filelocation.store.path'), null, config('filelocation.store.size'), 'webp', null, $data['store']->image, $data['store']->driver);
            $data['store']->image = $upload['path'];
            $data['store']->driver = $upload['driver'];
        }
        $data['store']->link = $purifiedData['link']??$data['store']->link;
        $data['store']->save();
        return $this->withSuccess('Store has been updated');
    }

    public function deleteStore($id)
    {
        $store = Store::own()->with(['productsMap'])->where('id',$id)->first();
        if (!$store){
            return $this->withErrors('Store not found');
        }
        if (count($store->productsMap) > 0) {
            return $this->withErrors('Store has lot of product');
        }
        $store->delete();
        return $this->withSuccess('Deleted Successfully');
    }

    public function productAttribute()
    {
        $attributes = StoreProductAttr::own()->orderBy('id', 'desc')->get();
        return $this->withSuccess($attributes);
    }

    public function storeAttribute(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($this->withErrors(collect($validator->errors())->collapse()));
        }

        DB::beginTransaction();
        try {
            $storeProductAttr = new StoreProductAttr();
            $storeProductAttr->user_id = Auth::id();
            $storeProductAttr->name = $request->name;
            $storeProductAttr->status = $purifiedData['status']??false;
            $storeProductAttr->save();

            if ($request->field_name) {
                for ($i = 0; $i < count($request->field_name); $i++) {
                    $productAttr = new ProductAttrList();
                    $productAttr->store_product_attrs_id = $storeProductAttr->id;
                    $productAttr->name = $request->field_name[$i];
                    $productAttr->save();
                }
            }
            DB::commit();
            return $this->withSuccess('Product Attribute Created');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->withErrors('Something went wrong');
        }
    }

    public function editAttribute($id)
    {
        $attr = StoreProductAttr::where('id',$id)->first();
        if (!$attr){
            return $this->withErrors('Attribute not found');
        }
        return  response()->json($this->withSuccess($attr));
    }

    public function updateAttribute(Request $request,$id)
    {
        $data['storeProductAttr'] = StoreProductAttr::own()->with(['attrLists'])->where('id',$id)->first();
        if (!$data['storeProductAttr']){
            return $this->withErrors('Attribute not found');
        }
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data['storeProductAttr']->name = $request->name;
            $data['storeProductAttr']->status = $purifiedData['status']??false;
            $data['storeProductAttr']->save();

            $previousAttr = ProductAttrList::where('store_product_attrs_id', $data['storeProductAttr']->id)->get();
            foreach ($previousAttr as $item) {
                $item->delete();
            }
            if ($request->field_name) {
                for ($i = 0; $i < count($request->field_name); $i++) {
                    $productAttr = new ProductAttrList();
                    $productAttr->store_product_attrs_id = $data['storeProductAttr']->id;
                    $productAttr->name = $request->field_name[$i];
                    $productAttr->save();
                }
            }
            DB::commit();
            return back()->with('success', 'Product Attribute Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Something went wrong');
        }
    }


}
