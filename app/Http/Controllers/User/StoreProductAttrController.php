<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductAttrList;
use App\Models\StoreProductAttr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StoreProductAttrController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function attrList()
    {
        $data['storeProductAttrs'] = StoreProductAttr::own()->orderBy('id', 'desc')->get();
        return view('user.store.productAttr.attrList', $data);
    }

    public function attrCreate(Request $request)
    {
        if ($request->method() == 'GET') {
            return view('user.store.productAttr.attrCreate');
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            try {
                $storeProductAttr = new StoreProductAttr();
                $storeProductAttr->user_id = Auth::id();
                $storeProductAttr->name = $request->name;
                $storeProductAttr->status = $purifiedData['status'];
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
                return back()->with('success', 'Product Attribute Created');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Something went wrong');
            }
        }
    }

    public function attrEdit($id, Request $request)
    {
        $data['storeProductAttr'] = StoreProductAttr::own()->with(['attrLists'])->findOrFail($id);
        if ($request->method() == 'GET') {
            return view('user.store.productAttr.attrEdit', $data);
        }
        if ($request->method() == 'POST') {
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
                $data['storeProductAttr']->status = $purifiedData['status'];
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

    public function attrDelete($id)
    {
        $storeProductAttr = StoreProductAttr::own()->with(['attrLists'])->findOrFail($id);
        if (count($storeProductAttr->attrLists) > 0) {
            return back()->with('error', 'Attribute has lot of data');
        }

        $storeProductAttr->delete();
        return back()->with('success', 'Product Attribute Deleted');
    }
}
